<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class BR_Escalation {

    private $table;

    // Allowed status values — used both for writes and for WHERE filters.
    const ALLOWED_STATUSES = array( 'open', 'in_progress', 'resolved' );

    public function __construct() {
        global $wpdb;
        $this->table = $wpdb->prefix . 'boreal_relay_escalations';
    }

    // -----------------------------------------------------------------------
    // Trigger — Free feature
    // -----------------------------------------------------------------------

    public function trigger( $session_id, $trigger_message, $ai_reply ) {
        global $wpdb;

        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching,WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- This real-time duplicate check uses this plugin's trusted custom table; the session ID is prepared.
        $existing = $wpdb->get_var( $wpdb->prepare(
            'SELECT id FROM ' . $this->table . " WHERE session_id = %s AND status = 'open'",
            $session_id
        ) );

        if ( $existing ) {
            return;
        }

        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery -- This writes to this plugin's trusted custom table with explicit formats.
        $wpdb->insert(
            $this->table,
            array(
                'session_id'      => sanitize_text_field( $session_id ),
                'trigger_message' => sanitize_textarea_field( $trigger_message ),
                'ai_reply'        => sanitize_textarea_field( $ai_reply ),
                'status'          => 'open',
            ),
            array( '%s', '%s', '%s', '%s' )
        );

        $this->send_notification( $session_id, $trigger_message, $ai_reply );
    }

    // -----------------------------------------------------------------------
    // Contact info — Free feature
    // -----------------------------------------------------------------------

    public function update_contact_info( $session_id, $name, $email, $phone ) {
        global $wpdb;

        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching,WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- This real-time lookup uses this plugin's trusted custom table; the session ID is prepared.
        $existing_id = $wpdb->get_var( $wpdb->prepare(
            'SELECT id FROM ' . $this->table . ' WHERE session_id = %s',
            sanitize_text_field( $session_id )
        ) );

        if ( $existing_id ) {
            // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery -- This updates this plugin's trusted custom table with explicit formats.
            $wpdb->update(
                $this->table,
                array(
                    'customer_name'  => sanitize_text_field( $name ),
                    'customer_email' => sanitize_email( $email ),
                    'customer_phone' => sanitize_text_field( $phone ),
                ),
                array( 'id' => intval( $existing_id ) ),
                array( '%s', '%s', '%s' ),
                array( '%d' )
            );
        } else {
            // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery -- This writes to this plugin's trusted custom table with explicit formats.
            $wpdb->insert(
                $this->table,
                array(
                    'session_id'      => sanitize_text_field( $session_id ),
                    'trigger_message' => '',
                    'ai_reply'        => '',
                    'customer_name'   => sanitize_text_field( $name ),
                    'customer_email'  => sanitize_email( $email ),
                    'customer_phone'  => sanitize_text_field( $phone ),
                    'status'          => 'open',
                ),
                array( '%s', '%s', '%s', '%s', '%s', '%s', '%s' )
            );
        }

        $this->send_contact_notification( $session_id, $name, $email, $phone );
    }

    // -----------------------------------------------------------------------
    // Contact AJAX — Free feature, public-facing
    // -----------------------------------------------------------------------

    public static function handle_save_contact_ajax() {
        check_ajax_referer( 'boreal_relay_nonce', 'nonce' );

        $session_id = isset( $_POST['session_id'] ) && is_string( $_POST['session_id'] ) ? boreal_relay_sanitize_session_id( wp_unslash( $_POST['session_id'] ) ) : '';
        $name       = isset( $_POST['name'] ) && is_string( $_POST['name'] ) ? sanitize_text_field( wp_unslash( $_POST['name'] ) ) : '';
        $email      = isset( $_POST['email'] ) && is_string( $_POST['email'] ) ? sanitize_email( wp_unslash( $_POST['email'] ) ) : '';
        $phone      = isset( $_POST['phone'] ) && is_string( $_POST['phone'] ) ? sanitize_text_field( wp_unslash( $_POST['phone'] ) ) : '';

        if ( empty( $session_id ) || ( empty( $email ) && empty( $phone ) ) ) {
            wp_send_json_error( 'Please provide at least an email or phone number.' );
        }

        $esc = new self();
        $esc->update_contact_info( $session_id, $name, $email, $phone );
        wp_send_json_success();
    }

    // -----------------------------------------------------------------------
    // Status update — allowlisted status, explicit formats
    // -----------------------------------------------------------------------

    public function update_status( $id, $status, $notes = '', $resolved_by = '' ) {
        global $wpdb;

        // Allowlist status value.
        if ( ! in_array( $status, self::ALLOWED_STATUSES, true ) ) {
            $status = 'open';
        }

        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery -- This updates this plugin's trusted custom table with explicit formats.
        return $wpdb->update(
            $this->table,
            array(
                'status'      => $status,
                'notes'       => sanitize_textarea_field( $notes ),
                'resolved_by' => sanitize_text_field( $resolved_by ),
            ),
            array( 'id' => intval( $id ) ),
            array( '%s', '%s', '%s' ),
            array( '%d' )
        );
    }

    // -----------------------------------------------------------------------
    // Queries
    // -----------------------------------------------------------------------

    public function get_all( $status = null, $limit = 50, $offset = 0 ) {
        global $wpdb;
        $limit  = max( 1, min( 100, absint( $limit ) ) );
        $offset = absint( $offset );

        // Allowlist status filter to prevent arbitrary WHERE injection.
        if ( $status !== null && ! in_array( $status, self::ALLOWED_STATUSES, true ) ) {
            $status = null;
        }

        if ( $status !== null ) {
            // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching,WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- This real-time list reads this plugin's trusted custom table; filter and pagination values are prepared.
            return $wpdb->get_results( $wpdb->prepare(
                'SELECT * FROM ' . $this->table . ' WHERE status = %s ORDER BY created_at DESC LIMIT %d OFFSET %d',
                $status,
                $limit,
                $offset
            ) );
        }

        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching,WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- This real-time list reads this plugin's trusted custom table; pagination values are prepared.
        return $wpdb->get_results( $wpdb->prepare(
            'SELECT * FROM ' . $this->table . ' ORDER BY created_at DESC LIMIT %d OFFSET %d',
            $limit,
            $offset
        ) );
    }

    public function get_open_count() {
        global $wpdb;
        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching,WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- The admin badge requires the current count from this plugin's trusted custom table.
        return intval( $wpdb->get_var( 'SELECT COUNT(*) FROM ' . $this->table . " WHERE status = 'open'" ) );
    }

    // -----------------------------------------------------------------------
    // Email notifications
    // -----------------------------------------------------------------------

    private function send_notification( $session_id, $trigger_message, $ai_reply ) {
        $to      = sanitize_email( get_option( 'boreal_relay_support_email', get_option( 'admin_email' ) ) );
        $cc      = sanitize_email( get_option( 'boreal_relay_escalation_cc', '' ) );
        $subject = '[Boreal Relay] Customer Needs Help — Chat Escalation';

        $admin_url = admin_url( 'admin.php?page=boreal-relay-escalations' );

        $body  = "Hello,\n\n";
        $body .= "A customer has a question your AI assistant escalated for human follow-up.\n\n";
        $body .= "--- CUSTOMER MESSAGE ---\n";
        $body .= $trigger_message . "\n\n";
        $body .= "--- AI RESPONSE GIVEN ---\n";
        $body .= $ai_reply . "\n\n";
        $body .= "NOTE: The customer may provide their contact info in the chat. Check the escalation in your admin panel:\n";
        $body .= $admin_url . "\n\n";
        $body .= "— Boreal Relay";

        $headers = array( 'Content-Type: text/plain; charset=UTF-8' );
        if ( ! empty( $cc ) ) {
            $headers[] = 'Cc: ' . $cc;
        }

        wp_mail( $to, $subject, $body, $headers );
    }

    private function send_contact_notification( $session_id, $name, $email, $phone ) {
        $to      = sanitize_email( get_option( 'boreal_relay_support_email', get_option( 'admin_email' ) ) );
        $subject = '[Boreal Relay] Customer Left Contact Info — Please Follow Up';

        $admin_url = admin_url( 'admin.php?page=boreal-relay-escalations' );

        $body  = "Hello,\n\n";
        $body .= "A customer who needed help has left their contact information. Please follow up!\n\n";
        if ( $name )  $body .= 'Name:  ' . $name . "\n";
        if ( $email ) $body .= 'Email: ' . $email . "\n";
        if ( $phone ) $body .= 'Phone: ' . $phone . "\n";
        $body .= "\nView the escalation:\n" . $admin_url . "\n\n";
        $body .= "— Boreal Relay";

        wp_mail( $to, $subject, $body );
    }
}
