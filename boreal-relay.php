<?php
/**
 * Plugin Name: Boreal Relay
 * Plugin URI: https://borealform.com/boreal-relay
 * Description: A BYOK AI support assistant with approved answers, conversation history, feedback, and safe human handoff.
 * Version: 2.1.0
 * Author: Borealform Studio
 * Author URI: https://borealform.com
 * License: GPL-2.0+
 * Text Domain: boreal-relay
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

define( 'BR_VERSION',     '2.1.0' );
define( 'BR_PLUGIN_DIR',  plugin_dir_path( __FILE__ ) );
define( 'BR_PLUGIN_URL',  plugin_dir_url( __FILE__ ) );
define( 'BR_PLUGIN_FILE', __FILE__ );

// Public-AJAX input limits.
define( 'BR_MAX_MESSAGE_LENGTH', 2000 );
define( 'BR_MAX_SESSION_LENGTH', 128 );

require_once BR_PLUGIN_DIR . 'includes/class-database.php';
require_once BR_PLUGIN_DIR . 'includes/class-knowledge-base.php';
require_once BR_PLUGIN_DIR . 'includes/class-ai-engine.php';
require_once BR_PLUGIN_DIR . 'includes/class-conversation.php';
require_once BR_PLUGIN_DIR . 'includes/class-escalation.php';
require_once BR_PLUGIN_DIR . 'public/class-widget.php';
require_once BR_PLUGIN_DIR . 'admin/class-admin.php';

register_activation_hook( __FILE__, array( 'BR_Database', 'install' ) );
add_action( 'plugins_loaded', array( 'BR_Database', 'maybe_upgrade' ) );
add_action( 'wp_ajax_nopriv_boreal_relay_save_contact', array( 'BR_Escalation', 'handle_save_contact_ajax' ) );
add_action( 'wp_ajax_boreal_relay_save_contact',        array( 'BR_Escalation', 'handle_save_contact_ajax' ) );
register_deactivation_hook( __FILE__, array( 'BR_Database', 'deactivate' ) );

function boreal_relay_init() {
    $widget = new BR_Widget();
    $widget->init();

    if ( is_admin() ) {
        $admin   = new BR_Admin();
        $admin->init();

    }
}
add_action( 'plugins_loaded', 'boreal_relay_init' );

// ---------------------------------------------------------------------------
// Helpers
// ---------------------------------------------------------------------------

/**
 * Return the optional Pro product page with a non-identifying campaign label.
 *
 * This only builds a URL. The free plugin never contacts Borealform in the
 * background and never performs licence checks.
 *
 * @param string $placement Admin surface where the link is displayed.
 * @return string
 */
function boreal_relay_get_pro_url( $placement = 'admin' ) {
    return add_query_arg(
        array(
            'utm_source'   => 'wordpress-plugin',
            'utm_medium'   => 'admin',
            'utm_campaign' => 'boreal-relay-pro',
            'utm_content'  => sanitize_key( $placement ),
        ),
        'https://borealform.com/boreal-relay'
    );
}

/**
 * Determine whether the separately installed Pro add-on is active.
 *
 * This is a local integration check. Licence verification belongs exclusively
 * to Boreal Relay Pro.
 *
 * @return bool
 */
function boreal_relay_has_active_pro() {
    return defined( 'BRP_VERSION' )
        && function_exists( 'boreal_relay_pro_is_active' )
        && boreal_relay_pro_is_active();
}

/**
 * Validate and normalise a public-facing session ID.
 * Returns empty string on failure.
 *
 * @param  string $raw
 * @return string
 */
function boreal_relay_sanitize_session_id( $raw ) {
    $clean = sanitize_text_field( $raw );
    // Limit length and allow only safe characters.
    if ( strlen( $clean ) > BR_MAX_SESSION_LENGTH ) {
        return '';
    }
    if ( ! preg_match( '/^[a-zA-Z0-9_\-]{1,' . BR_MAX_SESSION_LENGTH . '}$/', $clean ) ) {
        return '';
    }
    return $clean;
}

/**
 * Rate-limit a given action per IP using a transient.
 *
 * @param  string $action     Short identifier, e.g. 'chat'.
 * @param  int    $max        Calls allowed in the window.
 * @param  int    $window_sec Window length in seconds.
 * @return bool  true = allowed, false = blocked.
 */
function boreal_relay_rate_limit( $action, $max = 30, $window_sec = 60 ) {
    // Derive a stable, non-PII key from the client IP.
    $remote_address_input = filter_input( INPUT_SERVER, 'REMOTE_ADDR', FILTER_UNSAFE_RAW );
    $remote_address       = is_string( $remote_address_input ) ? sanitize_text_field( $remote_address_input ) : '';
    $client_ip             = filter_var( $remote_address, FILTER_VALIDATE_IP ) ? $remote_address : 'unknown';
    $ip_hash               = md5( $client_ip );
    $key     = 'boreal_relay_rl_' . sanitize_key( $action ) . '_' . $ip_hash;

    $count = (int) get_transient( $key );
    if ( $count >= $max ) {
        return false;
    }

    // Increment counter; set expiry only on first call.
    if ( $count === 0 ) {
        set_transient( $key, 1, $window_sec );
    } else {
        set_transient( $key, $count + 1, $window_sec );
    }
    return true;
}

// ---------------------------------------------------------------------------
// Chat AJAX handler — Free feature, no licence gate.
// ---------------------------------------------------------------------------

function boreal_relay_ajax_handler() {
    check_ajax_referer( 'boreal_relay_nonce', 'nonce' );

    // Rate limit: 30 messages per minute per IP.
    if ( ! boreal_relay_rate_limit( 'chat', 30, 60 ) ) {
        wp_send_json_error( array( 'message' => 'Too many requests. Please wait a moment.' ) );
    }

    if ( ! isset( $_POST['session_id'] ) || ! is_string( $_POST['session_id'] ) ) {
        wp_send_json_error( array( 'message' => 'Invalid session.' ) );
    }
    $session = boreal_relay_sanitize_session_id( wp_unslash( $_POST['session_id'] ) );
    if ( empty( $session ) ) {
        wp_send_json_error( array( 'message' => 'Invalid session.' ) );
    }

    if ( ! isset( $_POST['message'] ) || ! is_string( $_POST['message'] ) ) {
        wp_send_json_error( array( 'message' => 'Empty message.' ) );
    }
    $message = sanitize_text_field( wp_unslash( $_POST['message'] ) );
    if ( empty( $message ) ) {
        wp_send_json_error( array( 'message' => 'Empty message.' ) );
    }
    if ( strlen( $message ) > BR_MAX_MESSAGE_LENGTH ) {
        wp_send_json_error( array( 'message' => 'Message too long.' ) );
    }

    $page_url = isset( $_POST['page_url'] ) && is_string( $_POST['page_url'] ) ? esc_url_raw( wp_unslash( $_POST['page_url'] ) ) : '';

    $conversation = new BR_Conversation();
    $ai_engine    = new BR_AI_Engine();
    $escalation   = new BR_Escalation();

    $history = $conversation->get_history( $session );

    $result = $ai_engine->respond( $message, $history, $page_url );

    $conversation->save_message( $session, 'user', $message );
    $msg_db_id = $conversation->save_message( $session, 'assistant', $result['reply'], array(
        'confidence' => $result['confidence'],
        'escalated'  => $result['escalate'],
        'page_url'   => $page_url,
    ) );

    if ( $result['escalate'] ) {
        $escalation->trigger( $session, $message, $result['reply'] );
    }

    wp_send_json_success( array(
        'reply'         => $result['reply'],
        'escalate'      => $result['escalate'],
        'api_error'     => ! empty( $result['api_error'] ),
        'msg_db_id'     => intval( $msg_db_id ),
        'support_email' => sanitize_email( get_option( 'boreal_relay_support_email', get_option( 'admin_email' ) ) ),
    ) );
}
add_action( 'wp_ajax_boreal_relay_chat',        'boreal_relay_ajax_handler' );
add_action( 'wp_ajax_nopriv_boreal_relay_chat', 'boreal_relay_ajax_handler' );

// ---------------------------------------------------------------------------
// Feedback AJAX handler — Free feature, no licence gate.
// ---------------------------------------------------------------------------

function boreal_relay_feedback_handler() {
    check_ajax_referer( 'boreal_relay_nonce', 'nonce' );

    // Rate limit: 20 feedback per minute per IP.
    if ( ! boreal_relay_rate_limit( 'feedback', 20, 60 ) ) {
        wp_send_json_error( array( 'message' => 'Too many requests. Please wait a moment.' ) );
    }

    if ( ! isset( $_POST['session_id'] ) || ! is_string( $_POST['session_id'] ) ) {
        wp_send_json_error( array( 'message' => 'Invalid session.' ) );
    }
    $session = boreal_relay_sanitize_session_id( wp_unslash( $_POST['session_id'] ) );
    if ( empty( $session ) ) {
        wp_send_json_error( array( 'message' => 'Invalid session.' ) );
    }

    $message_id = isset( $_POST['message_id'] ) && is_scalar( $_POST['message_id'] ) ? absint( $_POST['message_id'] ) : 0;
    if ( $message_id <= 0 ) {
        wp_send_json_error( array( 'message' => 'Invalid message ID.' ) );
    }

    // Validate feedback value.
    $helpful_raw = isset( $_POST['helpful'] ) && is_scalar( $_POST['helpful'] ) ? sanitize_text_field( wp_unslash( $_POST['helpful'] ) ) : '';
    if ( ! in_array( $helpful_raw, array( '0', '1', 'true', 'false', true, false, 0, 1 ), true ) ) {
        wp_send_json_error( array( 'message' => 'Invalid feedback value.' ) );
    }
    $helpful = filter_var( $helpful_raw, FILTER_VALIDATE_BOOLEAN );

    $question = isset( $_POST['question'] ) && is_string( $_POST['question'] ) ? sanitize_text_field( wp_unslash( $_POST['question'] ) ) : '';
    $answer   = isset( $_POST['answer'] ) && is_string( $_POST['answer'] ) ? sanitize_textarea_field( wp_unslash( $_POST['answer'] ) ) : '';

    $conversation = new BR_Conversation();
    $conversation->save_feedback( $session, $message_id, $helpful, $question, $answer );

    wp_send_json_success();
}
add_action( 'wp_ajax_boreal_relay_feedback',        'boreal_relay_feedback_handler' );
add_action( 'wp_ajax_nopriv_boreal_relay_feedback', 'boreal_relay_feedback_handler' );
