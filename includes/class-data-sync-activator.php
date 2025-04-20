<?php

/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since      1.0.0
 * @package    Data_Sync
 * @subpackage Data_Sync/includes
 * @author     Nazmul Hosen <nazmul.ch11@gmail.com>
 */
class Data_Sync_Activator {

    /**
     * Create necessary database tables and default options on plugin activation.
     *
     * @since    1.0.0
     */
    public static function activate() {
        global $wpdb;

        // Create custom table
        $table_name = $wpdb->prefix . 'data_sync_items';
        $charset_collate = $wpdb->get_charset_collate();

        $sql = "CREATE TABLE $table_name (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            item_id varchar(255) NOT NULL,
            title text NOT NULL,
            description longtext,
            data longtext NOT NULL,
            created_at datetime DEFAULT CURRENT_TIMESTAMP NOT NULL,
            PRIMARY KEY  (id),
            KEY item_id (item_id)
        ) $charset_collate;";

        require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
        dbDelta( $sql );

        add_option( 'data_sync_api_token', '' );
        add_option( 'data_sync_api_url', 'https://jsonplaceholder.typicode.com' );
        add_option( 'data_sync_last_update', '' );
        add_option( 'data_sync_items_per_page', 10 );
    }
}
