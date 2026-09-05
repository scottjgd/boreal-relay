<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class BR_Conversation {

    private $table;

    public function __construct() {
        global $wpdb;
        $this->table = $wpdb->prefix . 'boreal_relay_conversations';
    }

    public function get_history( $session_id, $limit = 10 ) {
        global $wpdb;
        $limit = max( 1, min( 100, absint( $limit ) ) );
        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching,WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- This is a real-time read from this plugin's trusted custom table; the table name is constructed from the WordPress prefix and never request data.
        return $wpdb->get_results( $wpdb->prepare(
            'SELECT role, message FROM ' . $this->table . ' WHERE session_id = %s ORDER BY created_at DESC LIMIT %d',
            $session_id,
            $limit
        ) );
    }

    public function save_message( $session_id, $role, $message, $meta = array() ) {
        global $wpdb;

        // Allowlist role.
        $allowed_roles = array( 'user', 'assistant' );
        if ( ! in_array( $role, $allowed_roles, true ) ) {
            return false;
        }

        $data   = array(
            'session_id' => sanitize_text_field( $session_id ),
            'role'       => $role,
            'message'    => $message,
        );
        $format = array( '%s', '%s', '%s' );

        if ( isset( $meta['confidence'] ) ) {
            $data['confidence'] = floatval( $meta['confidence'] );
            $format[]           = '%f';
        }
        if ( isset( $meta['escalated'] ) ) {
            $data['escalated'] = $meta['escalated'] ? 1 : 0;
            $format[]          = '%d';
        }
        if ( isset( $meta['page_url'] ) ) {
            $data['page_url'] = esc_url_raw( $meta['page_url'] );
            $format[]         = '%s';
        }

        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery -- This writes to this plugin's trusted custom table through $wpdb->insert() with explicit formats.
        $wpdb->insert( $this->table, $data, $format );
        return $wpdb->insert_id;
    }

    /**
     * Record feedback for a specific assistant message.
     *
     * Ownership check: the message must exist, belong to $session_id, and have
     * role = 'assistant'.  This prevents arbitrary feedback injection from
     * sessions that don't own the message.
     *
     * @param string $session_id
     * @param int    $message_id
     * @param bool   $helpful
     * @param string $question
     * @param string $answer
     */
    public function save_feedback( $session_id, $message_id, $helpful, $question = '', $answer = '' ) {
        global $wpdb;

        $message_id = intval( $message_id );

        // Ownership + role validation.
        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching,WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- This real-time ownership check uses this plugin's trusted custom table; all values are prepared.
        $row = $wpdb->get_row( $wpdb->prepare(
            'SELECT id, role, helpful FROM ' . $this->table . ' WHERE id = %d AND session_id = %s',
            $message_id,
            $session_id
        ) );

        if ( ! $row ) {
            // Message doesn't exist or belongs to a different session — silently abort.
            return;
        }
        if ( $row->role !== 'assistant' ) {
            // Only assistant messages can receive feedback.
            return;
        }

        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery -- This updates this plugin's trusted custom table using explicit formats.
        $wpdb->update(
            $this->table,
            array( 'helpful' => $helpful ? 1 : 0 ),
            array( 'id' => $message_id ),
            array( '%d' ),
            array( '%d' )
        );

        /**
         * Allow a separately installed extension to process valid feedback.
         *
         * The free plugin records feedback but does not create or edit
         * knowledge entries. Boreal Relay Pro uses this hook to create a
         * reviewable draft from an unhelpful answer.
         *
         * @param string $session_id Conversation session ID.
         * @param int    $message_id Assistant message database ID.
         * @param bool   $helpful Whether the visitor marked the answer helpful.
         * @param string $question Visitor question supplied by the widget.
         * @param string $answer Assistant answer supplied by the widget.
         */
        if ( null === $row->helpful ) {
            do_action(
                'boreal_relay_feedback_saved',
                sanitize_text_field( $session_id ),
                $message_id,
                (bool) $helpful,
                sanitize_text_field( $question ),
                sanitize_textarea_field( $answer )
            );
        }
    }

    public function get_sessions( $limit = 50, $offset = 0, $escalated_only = false ) {
        global $wpdb;
        $limit  = max( 1, min( 100, absint( $limit ) ) );
        $offset = absint( $offset );
        if ( $escalated_only ) {
            // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching,WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- This is a real-time aggregate from this plugin's trusted custom table; pagination values are prepared.
            return $wpdb->get_results( $wpdb->prepare(
                "SELECT session_id,
                    MIN(created_at)  AS started_at,
                    MAX(created_at)  AS last_activity,
                    COUNT(*)         AS message_count,
                    MAX(escalated)   AS has_escalation,
                    AVG(confidence)  AS avg_confidence
             FROM " . $this->table . "
             WHERE escalated = 1
                    GROUP BY session_id
                    ORDER BY last_activity DESC
                    LIMIT %d OFFSET %d",
                $limit,
                $offset
            ) );
        }
        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching,WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- This is a real-time aggregate from this plugin's trusted custom table; pagination values are prepared.
        return $wpdb->get_results( $wpdb->prepare(
            "SELECT session_id,
                    MIN(created_at)  AS started_at,
                    MAX(created_at)  AS last_activity,
                    COUNT(*)         AS message_count,
                    MAX(escalated)   AS has_escalation,
                    AVG(confidence)  AS avg_confidence
             FROM " . $this->table . "
             GROUP BY session_id
                    ORDER BY last_activity DESC
                    LIMIT %d OFFSET %d",
            $limit,
            $offset
        ) );
    }

    public function get_session_messages( $session_id ) {
        global $wpdb;
        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching,WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- This is a real-time read from this plugin's trusted custom table; the session ID is prepared.
        return $wpdb->get_results( $wpdb->prepare(
            'SELECT * FROM ' . $this->table . ' WHERE session_id = %s ORDER BY created_at ASC',
            $session_id
        ) );
    }

    public function get_stats() {
        global $wpdb;
        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching,WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Dashboard statistics must reflect the plugin's trusted custom table at request time.
        $total_sessions  = $wpdb->get_var( 'SELECT COUNT(DISTINCT session_id) FROM ' . $this->table );
        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching,WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Dashboard statistics must reflect the plugin's trusted custom table at request time.
        $total_messages  = $wpdb->get_var( 'SELECT COUNT(*) FROM ' . $this->table . " WHERE role = 'user'" );
        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching,WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Dashboard statistics must reflect the plugin's trusted custom table at request time.
        $escalated_count = $wpdb->get_var( 'SELECT COUNT(DISTINCT session_id) FROM ' . $this->table . ' WHERE escalated = 1' );
        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching,WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Dashboard statistics must reflect the plugin's trusted custom table at request time.
        $helpful_count   = $wpdb->get_var( 'SELECT COUNT(*) FROM ' . $this->table . ' WHERE helpful = 1' );
        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching,WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Dashboard statistics must reflect the plugin's trusted custom table at request time.
        $unhelpful_count = $wpdb->get_var( 'SELECT COUNT(*) FROM ' . $this->table . ' WHERE helpful = 0' );
        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching,WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Dashboard statistics must reflect the plugin's trusted custom table at request time.
        $avg_confidence  = $wpdb->get_var( 'SELECT AVG(confidence) FROM ' . $this->table . " WHERE role = 'assistant' AND confidence IS NOT NULL" );

        return array(
            'total_sessions'  => intval( $total_sessions ),
            'total_messages'  => intval( $total_messages ),
            'escalated_count' => intval( $escalated_count ),
            'helpful_count'   => intval( $helpful_count ),
            'unhelpful_count' => intval( $unhelpful_count ),
            'avg_confidence'  => round( floatval( $avg_confidence ) * 100 ),
        );
    }
}
