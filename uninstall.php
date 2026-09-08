<?php
/**
 * Remove Boreal Relay data when an administrator uninstalls the plugin.
 */

defined( 'WP_UNINSTALL_PLUGIN' ) || exit;

// phpcs:disable WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching -- Uninstall must delete plugin-owned tables immediately; caching destructive schema operations is neither applicable nor safe.

global $wpdb;

$boreal_relay_tables = array(
    $wpdb->prefix . 'boreal_relay_conversations',
    $wpdb->prefix . 'boreal_relay_knowledge',
    $wpdb->prefix . 'boreal_relay_escalations',
);

foreach ( $boreal_relay_tables as $boreal_relay_table ) {
    // Plugin-owned table identifiers contain only the trusted WordPress prefix
    // and hard-coded suffixes above; values are never accepted from a request.
    // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared,WordPress.DB.DirectDatabaseQuery.SchemaChange
    $wpdb->query( "DROP TABLE IF EXISTS {$boreal_relay_table}" );
}

$boreal_relay_options = array(
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

foreach ( $boreal_relay_options as $boreal_relay_option ) {
    delete_option( $boreal_relay_option );
}