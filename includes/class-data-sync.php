<?php
/**
 * The core plugin class.
 *
 * @since      1.0.0
 * @package    Data_Sync
 * @subpackage Data_Sync/includes
 * @author     Nazmul Hosen <nazmul.ch11@gmail.com>
 */
class Data_Sync {

    /**
     * The loader that's responsible for maintaining and registering all hooks that power
     * the plugin.
     *
     * @since    1.0.0
     * @access   protected
     * @var      Data_Sync_Loader    $loader    Maintains and registers all hooks for the plugin.
     */
    protected $loader;

    /**
     * The unique identifier of this plugin.
     *
     * @since    1.0.0
     * @access   protected
     * @var      string    $plugin_name    The string used to uniquely identify this plugin.
     */
    protected $plugin_name;

    /**
     * The current version of the plugin.
     *
     * @since    1.0.0
     * @access   protected
     * @var      string    $version    The current version of the plugin.
     */
    protected $version;

    /**
     * Define the core functionality of the plugin.
     *
     * @since    1.0.0
     */
    public function __construct() {
        if ( defined( 'DATA_SYNC_VERSION' ) ) {
            $this->version = DATA_SYNC_VERSION;
        } else {
            $this->version = '1.0.0';
        }
        $this->plugin_name = 'data-sync';

        $this->load_dependencies();
        $this->set_locale();
        $this->define_admin_hooks();
    }

    /**
     * Load the required dependencies for this plugin.
     *
     * Include the following files that make up the plugin:
     *
     * - Data_Sync_Loader. Orchestrates the hooks of the plugin.
     * - Data_Sync_i18n. Defines internationalization functionality.
     * - Data_Sync_Admin. Defines all hooks for the admin area.
     *
     * @since    1.0.0
     * @access   private
     */
    private function load_dependencies() {

        /**
         * The class responsible for orchestrating the actions and filters of the
         * core plugin.
         */
        require_once DATA_SYNC_PLUGIN_DIR . 'includes/class-data-sync-loader.php';

        /**
         * The class responsible for defining internationalization functionality
         * of the plugin.
         */
        require_once DATA_SYNC_PLUGIN_DIR . 'includes/class-data-sync-i18n.php';

        /**
         * The class responsible for handling API requests and responses.
         */
        require_once DATA_SYNC_PLUGIN_DIR . 'includes/class-data-sync-api.php';

        /**
         * The class responsible for defining all actions that occur in the admin area.
         */
        require_once DATA_SYNC_PLUGIN_DIR . 'admin/class-data-sync-admin.php';

        $this->loader = new Data_Sync_Loader();
    }

    /**
     * Define the locale for this plugin for internationalization.
     *
     * Uses the Data_Sync_i18n class in order to set the domain and to register the hook
     * with WordPress.
     *
     * @since    1.0.0
     * @access   private
     */
    private function set_locale() {
        $plugin_i18n = new Data_Sync_i18n();
        $this->loader->add_action( 'plugins_loaded', $plugin_i18n, 'load_plugin_textdomain' );
    }

    /**
     * Register all of the hooks related to the admin area functionality
     * of the plugin.
     *
     * @since    1.0.0
     * @access   private
     */
    private function define_admin_hooks() {
        $plugin_admin = new Data_Sync_Admin( $this->get_plugin_name(), $this->get_version() );

        // Add menu item
        $this->loader->add_action( 'admin_menu', $plugin_admin, 'add_plugin_admin_menu' );

        $plugin_basename = plugin_basename( plugin_dir_path( __DIR__ ) . 'data-sync.php' );
        $this->loader->add_filter( 'plugin_action_links_' . $plugin_basename, $plugin_admin, 'add_action_links' );

        // Save/Update our plugin options
        $this->loader->add_action( 'admin_init', $plugin_admin, 'register_setting' );

        // Enqueue styles and scripts
        $this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_styles' );
        $this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts' );

        // AJAX handlers
        $this->loader->add_action( 'wp_ajax_data_sync_fetch_data', $plugin_admin, 'ajax_fetch_data' );
        $this->loader->add_action( 'wp_ajax_nopriv_data_sync_fetch_data', $plugin_admin, 'ajax_fetch_data' );
        $this->loader->add_action( 'wp_ajax_data_sync_get_item_details', $plugin_admin, 'ajax_get_item_details' );
        $this->loader->add_action( 'wp_ajax_nopriv_data_sync_get_item_details', $plugin_admin, 'ajax_get_item_details' );
    }

    /**
     * Run the loader to execute all of the hooks with WordPress.
     *
     * @since    1.0.0
     */
    public function run() {
        $this->loader->run();
    }

    /**
     * The name of the plugin used to uniquely identify it within the context of
     * WordPress and to define internationalization functionality.
     *
     * @since     1.0.0
     * @return    string    The name of the plugin.
     */
    public function get_plugin_name() {
        return $this->plugin_name;
    }

    /**
     * The reference to the class that orchestrates the hooks with the plugin.
     *
     * @since     1.0.0
     * @return    Data_Sync_Loader    Orchestrates the hooks of the plugin.
     */
    public function get_loader() {
        return $this->loader;
    }

    /**
     * Retrieve the version number of the plugin.
     *
     * @since     1.0.0
     * @return    string    The version number of the plugin.
     */
    public function get_version() {
        return $this->version;
    }
}
