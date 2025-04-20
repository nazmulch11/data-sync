<?php
/**
 *
 * @link              https://nazmul.xyz/
 * @since             1.0.0
 *
 * @wordpress-plugin
 * Plugin Name:       Data Sync
 * Plugin URI:        https://nazmul.xyz/
 * Description:       Fetches data from an external API, saves it, and displays the results in the WordPress admin panel.
 * Version:           1.0.0
 * Author:            Nazmul Hosen
 * Author URI:        https://nazmul.xyz/
 * License:           GPL-3.0+
 * License URI:       https://www.gnu.org/licenses/gpl-3.0.html
 * Text Domain:       data-sync
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
    die;
}

/**
 * Currently plugin version.
 */
define( 'DATA_SYNC_VERSION', '1.0.0' );
define( 'DATA_SYNC_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'DATA_SYNC_PLUGIN_URL', plugin_dir_url( __FILE__ ) );

/**
 * The code that runs during plugin activation.
 */
function activate_data_sync() {
    require_once DATA_SYNC_PLUGIN_DIR . 'includes/class-data-sync-activator.php';
    Data_Sync_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 */
function deactivate_data_sync() {
    require_once DATA_SYNC_PLUGIN_DIR . 'includes/class-data-sync-deactivator.php';
    Data_Sync_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_data_sync' );
register_deactivation_hook( __FILE__, 'deactivate_data_sync' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require DATA_SYNC_PLUGIN_DIR . 'includes/class-data-sync.php';

/**
 * Begins execution of the plugin.
 *
 * @since    1.0.0
 */
function run_data_sync() {
    $plugin = new Data_Sync();
    $plugin->run();
}

run_data_sync();
