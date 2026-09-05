<?php
/**
 * Remove Boreal Relay data when an administrator uninstalls the plugin.
 */

defined( 'WP_UNINSTALL_PLUGIN' ) || exit;

global $wpdb;

$br_tables = array(
    $wpdb->prefix . 'boreal_relay_conversations',
    $wpdb->prefix . 'boreal_relay_knowledge',
    $wpdb->prefix . 'boreal_relay_escalations',
);

foreach ( $br_tables as $br_table ) {
    // Plugin-owned table identifiers contain only the trusted WordPress prefix
    // and hard-coded suffixes above; values are never accepted from a request.
    // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared,WordPress.DB.DirectDatabaseQuery.SchemaChange
    $wpdb->query( "DROP TABLE IF EXISTS {$br_table}" );
}

$br_options = array(
    'boreal_relay_db_version',
    'boreal_relay_enabled',
    'boreal_relay_openai_api_key',
    'boreal_relay_model',
    'boreal_relay_bot_name',
    'boreal_relay_greeting',
    'boreal_relay_theme_color',
    'boreal_relay_tone',
    'boreal_relay_business_name',
    'boreal_relay_support_email',
    'boreal_relay_escalation_cc',
);

foreach ( $br_options as $br_option ) {
    delete_option( $br_option );
}