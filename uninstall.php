<?php
/**
 * Fired when the plugin is uninstalled.
 *
 * @link       https://nazmul.xyz/
 * @since      1.0.0
 *
 * @package    Data_Sync
 * @subpackage Data_Sync/uninstall
 */

// If uninstall not called from WordPress, then exit.
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
    exit;
}

// Delete options
delete_option( 'data_sync_api_token' );
delete_option( 'data_sync_api_url' );
delete_option( 'data_sync_last_update' );
delete_option( 'data_sync_items_per_page' );

// Drop custom table
global $wpdb;
$table_name = $wpdb->prefix . 'data_sync_items';
$wpdb->query( $wpdb->prepare( "DROP TABLE IF EXISTS %s", $table_name ) );
