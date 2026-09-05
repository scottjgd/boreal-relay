<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class BR_Admin {

    // Allowed escalation status values.
    const ALLOWED_STATUSES = array( 'open', 'in_progress', 'resolved' );

    // Allowed user roles (for KB / escalation role checks — extend as needed).
    const ALLOWED_ROLES = array( 'manage_options' );

    public function init() {
        add_action( 'admin_menu',   array( $this, 'register_menus' ) );
        add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_assets' ) );
        add_action( 'admin_init',   array( $this, 'register_settings' ) );
        add_action( 'admin_post_boreal_relay_update_escalation', array( $this, 'handle_escalation_update' ) );
        add_action( 'admin_notices', array( $this, 'admin_notices' ) );
    }

    // -----------------------------------------------------------------------
    // Menus
    // -----------------------------------------------------------------------

    public function register_menus() {
        $escalation = new BR_Escalation();
        $open_count = $escalation->get_open_count();
        $badge = $open_count > 0
            ? ' <span class="update-plugins"><span class="plugin-count">' . intval( $open_count ) . '</span></span>'
            : '';

        add_menu_page(
            'Boreal Relay',
            'Boreal Relay' . $badge,
            'manage_options',
            'boreal-relay-dashboard',
            array( $this, 'page_dashboard' ),
            'dashicons-format-chat',
            58
        );

        add_submenu_page( 'boreal-relay-dashboard', 'Dashboard',            'Dashboard',            'manage_options', 'boreal-relay-dashboard',     array( $this, 'page_dashboard' ) );
        add_submenu_page( 'boreal-relay-dashboard', 'Conversations',        'Conversations',        'manage_options', 'boreal-relay-conversations', array( $this, 'page_conversations' ) );
        add_submenu_page( 'boreal-relay-dashboard', 'Knowledge Base',       'Knowledge Base',       'manage_options', 'boreal-relay-knowledge',     array( $this, 'page_knowledge' ) );
        add_submenu_page( 'boreal-relay-dashboard', 'Escalations' . $badge, 'Escalations' . $badge, 'manage_options', 'boreal-relay-escalations',   array( $this, 'page_escalations' ) );
        add_submenu_page( 'boreal-relay-dashboard', 'Settings',             'Settings',             'manage_options', 'boreal-relay-settings',      array( $this, 'page_settings' ) );
        // The optional Pro add-on registers its own licence submenu.
    }

    // -----------------------------------------------------------------------
    // Assets
    // -----------------------------------------------------------------------

    public function enqueue_assets( $hook ) {
        if ( strpos( $hook, 'boreal-relay' ) === false ) return;
        wp_enqueue_style(  'boreal-relay-admin', BR_PLUGIN_URL . 'admin/css/admin.css', array(), BR_VERSION );
        wp_enqueue_script( 'boreal-relay-admin', BR_PLUGIN_URL . 'admin/js/admin.js',   array( 'jquery' ), BR_VERSION, true );
    }

    // -----------------------------------------------------------------------
    // Settings registration — per-field sanitizers, secret preservation
    // -----------------------------------------------------------------------

    public function register_settings() {
        // Each field registered with an explicit sanitize callback.
        $text_fields = array(
            'boreal_relay_model', 'boreal_relay_bot_name', 'boreal_relay_tone', 'boreal_relay_business_name',
        );
        foreach ( $text_fields as $field ) {
            register_setting( 'boreal_relay_settings', $field, array(
                'sanitize_callback' => 'sanitize_text_field',
            ) );
        }

        register_setting( 'boreal_relay_settings', 'boreal_relay_enabled', array(
            'sanitize_callback' => array( $this, 'sanitize_checkbox' ),
        ) );

        register_setting( 'boreal_relay_settings', 'boreal_relay_greeting', array(
            'sanitize_callback' => 'sanitize_textarea_field',
        ) );

        register_setting( 'boreal_relay_settings', 'boreal_relay_theme_color', array(
            'sanitize_callback' => array( $this, 'sanitize_hex_color' ),
        ) );

        register_setting( 'boreal_relay_settings', 'boreal_relay_support_email', array(
            'sanitize_callback' => 'sanitize_email',
        ) );

        register_setting( 'boreal_relay_settings', 'boreal_relay_escalation_cc', array(
            'sanitize_callback' => 'sanitize_email',
        ) );

        // Secret fields: preserve stored value when submitted blank.
        register_setting( 'boreal_relay_settings', 'boreal_relay_openai_api_key', array(
            'sanitize_callback' => array( $this, 'sanitize_preserve_secret' ),
        ) );
    }

    // -- Sanitize helpers ---------------------------------------------------

    public function sanitize_checkbox( $value ) {
        return ( $value === '1' || $value === 1 ) ? '1' : '0';
    }

    public function sanitize_hex_color( $value ) {
        $clean = sanitize_hex_color( $value );
        return $clean ?: '#2563eb';
    }

    /**
     * For boreal_relay_openai_api_key: if the submitted value is blank OR looks like
     * our masked placeholder, keep the previously saved value.
     */
    public function sanitize_preserve_secret( $new_value ) {
        $clean = sanitize_text_field( wp_unslash( (string) $new_value ) );
        if ( $clean === '' || strpos( $clean, '***' ) !== false ) {
            return get_option( 'boreal_relay_openai_api_key', '' );
        }
        return $clean;
    }

    // -----------------------------------------------------------------------
    // Admin notices
    // -----------------------------------------------------------------------

    public function admin_notices() {
        $screen = get_current_screen();
        if ( ! $screen || strpos( $screen->id, 'boreal-relay' ) === false ) return;

        if ( ! get_option( 'boreal_relay_openai_api_key' ) ) {
            echo '<div class="notice notice-warning is-dismissible"><p>';
            echo '<strong>' . esc_html( 'Boreal Relay:' ) . '</strong> ';
            /* translators: %s: settings page URL. */
            $message = __( 'Please <a href="%s">add your OpenAI API key</a> to activate the AI assistant.', 'boreal-relay' );
            printf(
                wp_kses(
                    $message,
                    array( 'a' => array( 'href' => array() ) )
                ),
                esc_url( admin_url( 'admin.php?page=boreal-relay-settings' ) )
            );
            echo '</p></div>';
        }

    }

    // -----------------------------------------------------------------------
    // Page callbacks
    // -----------------------------------------------------------------------

    public function page_dashboard() {
        $conversation = new BR_Conversation();
        $stats        = $conversation->get_stats();
        $escalation   = new BR_Escalation();
        include BR_PLUGIN_DIR . 'admin/views/dashboard.php';
    }

    public function page_conversations() {
        $session_id = '';
        if ( isset( $_GET['session'], $_GET['_wpnonce'] ) && is_string( $_GET['session'] ) && is_string( $_GET['_wpnonce'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_GET['_wpnonce'] ) ), 'boreal_relay_view_conversation' ) ) {
            $session_id = boreal_relay_sanitize_session_id( wp_unslash( $_GET['session'] ) );
        }
        $conversation = new BR_Conversation();
        if ( $session_id ) {
            $messages = $conversation->get_session_messages( $session_id );
            include BR_PLUGIN_DIR . 'admin/views/conversation-detail.php';
        } else {
            $sessions = $conversation->get_sessions( 100 );
            include BR_PLUGIN_DIR . 'admin/views/conversations.php';
        }
    }

    public function page_knowledge() {
        $kb      = new BR_Knowledge_Base();
        $entries = $kb->get_all( false );
        $edit_id = isset( $_GET['edit'], $_GET['_wpnonce'] ) && is_scalar( $_GET['edit'] ) && is_string( $_GET['_wpnonce'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_GET['_wpnonce'] ) ), 'boreal_relay_knowledge_view' ) ? absint( $_GET['edit'] ) : 0;
        include BR_PLUGIN_DIR . 'admin/views/knowledge.php';
    }

    public function page_escalations() {
        $escalation    = new BR_Escalation();
        $status_filter = isset( $_GET['status'], $_GET['_wpnonce'] ) && is_string( $_GET['status'] ) && is_string( $_GET['_wpnonce'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_GET['_wpnonce'] ) ), 'boreal_relay_escalation_filter' ) ? sanitize_key( wp_unslash( $_GET['status'] ) ) : null;
        // Allowlist status filter.
        if ( $status_filter !== null && ! in_array( $status_filter, self::ALLOWED_STATUSES, true ) ) {
            $status_filter = null;
        }
        $escalations = $escalation->get_all( $status_filter );
        include BR_PLUGIN_DIR . 'admin/views/escalations.php';
    }

    public function page_settings() {
        include BR_PLUGIN_DIR . 'admin/views/settings.php';
    }

    // -----------------------------------------------------------------------
    // Escalation update handler — Free feature (status management)
    // -----------------------------------------------------------------------

    public function handle_escalation_update() {
        check_admin_referer( 'boreal_relay_escalation_update' );
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_die( esc_html__( 'Unauthorized', 'boreal-relay' ) );
        }

        $escalation = new BR_Escalation();
        $id         = isset( $_POST['escalation_id'] ) && is_scalar( $_POST['escalation_id'] ) ? absint( $_POST['escalation_id'] ) : 0;
        $status_raw = isset( $_POST['status'] ) && is_string( $_POST['status'] ) ? sanitize_key( wp_unslash( $_POST['status'] ) ) : 'open';
        $notes      = isset( $_POST['notes'] ) && is_string( $_POST['notes'] ) ? sanitize_textarea_field( wp_unslash( $_POST['notes'] ) ) : '';

        // Allowlist status.
        $status = in_array( $status_raw, self::ALLOWED_STATUSES, true ) ? $status_raw : 'open';

        $by = sanitize_text_field( wp_get_current_user()->display_name );

        if ( $id ) {
            $escalation->update_status( $id, $status, $notes, $by );
        }

        wp_safe_redirect( wp_nonce_url( admin_url( 'admin.php?page=boreal-relay-escalations&updated=1' ), 'boreal_relay_escalation_updated' ) );
        exit;
    }
}
