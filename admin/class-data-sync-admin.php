<?php
/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://nazmul.xyz/
 * @since      1.0.0
 *
 * @subpackage Data_Sync/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two hooks for
 * enqueuing the admin-specific stylesheet and JavaScript.
 *
 * @subpackage Data_Sync/admin
 */
class Data_Sync_Admin {

    /**
     * The ID of this plugin.
     *
     * @since    1.0.0
     * @access   private
     * @var      string    $plugin_name    The ID of this plugin.
     */
    private $plugin_name;

    /**
     * The version of this plugin.
     *
     * @since    1.0.0
     * @access   private
     * @var      string    $version    The current version of this plugin.
     */
    private $version;

    /**
     * The API handler instance.
     *
     * @since    1.0.0
     * @access   private
     * @var      Data_Sync_API    $api    The API handler instance.
     */
    private $api;

    /**
     * Initialize the class and set its properties.
     *
     * @since    1.0.0
     * @param    string    $plugin_name       The name of this plugin.
     * @param    string    $version           The version of this plugin.
     */
    public function __construct( $plugin_name, $version ) {
        $this->plugin_name = $plugin_name;
        $this->version = $version;

        $this->api = new Data_Sync_API();
    }

    /**
     * Register the stylesheets for the admin area.
     *
     * @since    1.0.0
     */
    public function enqueue_styles() {
        wp_enqueue_style(
            $this->plugin_name,
            DATA_SYNC_PLUGIN_URL . 'admin/css/data-sync-admin.css',
            array(),
            $this->version,
            'all'
        );
    }

    /**
     * Register the JavaScript for the admin area.
     *
     * @since    1.0.0
     */
    public function enqueue_scripts() {
        wp_enqueue_script(
            $this->plugin_name,
            DATA_SYNC_PLUGIN_URL . 'admin/js/data-sync-admin.js',
            array( 'jquery' ),
            $this->version,
            false
        );

        wp_localize_script(
            $this->plugin_name,
            'data_sync_params',
            array(
                'ajax_url'   => admin_url( 'admin-ajax.php' ),
                'nonce'      => wp_create_nonce( 'data_sync_nonce' ),
                'loading'    => __( 'Loading...', 'data-sync' ),
                'error'      => __( 'An error occurred. Please try again.', 'data-sync' ),
                'success'    => __( 'Data synced successfully!', 'data-sync' ),
                'sync_now'   => __( 'Sync Now', 'data-sync' ),
                'last_updated' => __( 'Last updated:', 'data-sync' ),
                'item_details' => __( 'Item Details', 'data-sync' ),
                'close'      => __( 'Close', 'data-sync' ),
            )
        );
    }

    /**
     * Add an options page under the Settings submenu.
     *
     * @since    1.0.0
     */
    public function add_plugin_admin_menu() {
        add_submenu_page(
            'options-general.php',
            __( 'Data Sync Settings', 'data-sync' ),
            __( 'Data Sync', 'data-sync' ),
            'manage_options',
            $this->plugin_name,
            array( $this, 'display_plugin_admin_page' )
        );
    }

    /**
     * Add settings action link to the plugins page.
     *
     * @since    1.0.0
     * @param    array    $links    Plugin action links.
     * @return   array              Plugin action links.
     */
    public function add_action_links( $links ) {
        $settings_link = array(
            '<a href="' . admin_url( 'options-general.php?page=' . $this->plugin_name ) . '">' . __( 'Settings', 'data-sync' ) . '</a>',
        );
        return array_merge( $settings_link, $links );
    }

    /**
     * Register the settings for the admin page.
     *
     * @since    1.0.0
     */
    public function register_setting() {
        // Register the settings group
        register_setting(
            'data_sync_options',
            'data_sync_api_token',
            array(
                'type'              => 'string',
                'sanitize_callback' => 'sanitize_text_field',
                'default'           => '',
                'show_in_rest'      => false,
            )
        );

        register_setting(
            'data_sync_options',
            'data_sync_api_url',
            array(
                'type'              => 'string',
                'sanitize_callback' => 'sanitize_text_field',
                'default'           => 'https://jsonplaceholder.typicode.com',
                'show_in_rest'      => false,
            )
        );

        register_setting(
            'data_sync_options',
            'data_sync_items_per_page',
            array(
                'type'              => 'integer',
                'sanitize_callback' => 'absint',
                'default'           => 10,
                'show_in_rest'      => false,
            )
        );

        // Add settings section
        add_settings_section(
            'data_sync_general_section',
            __( 'General Settings', 'data-sync' ),
            array( $this, 'general_section_callback' ),
            $this->plugin_name
        );

        // Add settings fields
        add_settings_field(
            'data_sync_api_url',
            __( 'API URL', 'data-sync' ),
            array( $this, 'api_url_field_callback' ),
            $this->plugin_name,
            'data_sync_general_section'
        );

        add_settings_field(
            'data_sync_api_token',
            __( 'API Token', 'data-sync' ),
            array( $this, 'api_token_field_callback' ),
            $this->plugin_name,
            'data_sync_general_section'
        );

        add_settings_field(
            'data_sync_items_per_page',
            __( 'Items Per Page', 'data-sync' ),
            array( $this, 'items_per_page_field_callback' ),
            $this->plugin_name,
            'data_sync_general_section'
        );
    }

    /**
     * General section callback.
     *
     * @since    1.0.0
     */
    public function general_section_callback() {
        echo '<p>' . esc_html__( 'Configure your API settings below.', 'data-sync' ) . '</p>';
    }

    /**
     * API URL field callback.
     *
     * @since    1.0.0
     */
    public function api_url_field_callback() {
        $url = get_option( 'data_sync_api_url', 'https://jsonplaceholder.typicode.com' );
        ?>
        <input type="text" name="data_sync_api_url" id="data_sync_api_url"
               value="<?php echo esc_attr( $url ); ?>" class="regular-text" />
        <p class="description">
            <?php esc_html_e( 'Enter the base URL of your API.', 'data-sync' ); ?>
        </p>
        <?php
    }

    /**
     * API token field callback.
     *
     * @since    1.0.0
     */
    public function api_token_field_callback() {
        $token = get_option( 'data_sync_api_token', '' );
        ?>
        <input type="text" name="data_sync_api_token" id="data_sync_api_token"
               value="<?php echo esc_attr( $token ); ?>" class="regular-text" />
        <p class="description">
            <?php esc_html_e( 'Enter your API token for authentication.', 'data-sync' ); ?>
        </p>
        <?php
    }

    /**
     * Items per page field callback.
     *
     * @since    1.0.0
     */
    public function items_per_page_field_callback() {
        $items_per_page = get_option( 'data_sync_items_per_page', 10 );
        ?>
        <input type="number" name="data_sync_items_per_page" id="data_sync_items_per_page"
               value="<?php echo esc_attr( $items_per_page ); ?>" min="1" max="100" step="1" />
        <p class="description">
            <?php esc_html_e( 'Number of items to display per page.', 'data-sync' ); ?>
        </p>
        <?php
    }

    /**
     * Render the admin page.
     *
     * @since    1.0.0
     */
    public function display_plugin_admin_page() {
        if ( ! current_user_can( 'manage_options' ) ) {
            return;
        }
        $page = isset( $_GET['paged'] ) ? max( 1, absint( sanitize_text_field( wp_unslash( $_GET['paged'] ) ) ) ) : 1;

        $per_page = get_option( 'data_sync_items_per_page', 10 );

        $data = $this->api->get_saved_data( $per_page, $page );

        $last_update = get_option( 'data_sync_last_update', '' );

        include_once DATA_SYNC_PLUGIN_DIR . 'admin/partials/data-sync-admin-display.php';
    }

    /**
     * AJAX handler for fetching data from the API.
     *
     * @since    1.0.0
     */
    public function ajax_fetch_data() {
        try {
            if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ) ), 'data_sync_nonce' ) ) {
                wp_send_json_error( array( 'message' => __( 'Security check failed.', 'data-sync' ) ) );
                return;
            }

            // Check user capabilities
            if ( ! current_user_can( 'manage_options' ) ) {
                wp_send_json_error( array( 'message' => __( 'You do not have permission to perform this action.', 'data-sync' ) ) );
                return;
            }

            error_log('Data Sync: AJAX fetch data request received');

            // Get the API token
            $token = get_option('data_sync_api_token');
            $this->api->set_api_token($token);

            $items = $this->api->fetch_data();

            if ( is_wp_error( $items ) ) {
                $error_message = $items->get_error_message();
                error_log('Data Sync Error: API fetch failed - ' . $error_message);
                wp_send_json_error( array( 'message' => $error_message ) );
                return;
            }

            // Save data to database
            error_log('Data Sync: Saving data to database');
            $result = $this->api->save_data( $items );

            if ( is_wp_error( $result ) ) {
                $error_message = $result->get_error_message();
                error_log('Data Sync Error: Data save failed - ' . $error_message);
                wp_send_json_error( array( 'message' => $error_message ) );
                return;
            }

            // Get current page
            $page = isset( $_POST['page'] ) ? max( 1, intval( $_POST['page'] ) ) : 1;

            // Get items per page
            $per_page = get_option( 'data_sync_items_per_page', 10 );

            // Get saved data
            error_log('Data Sync: Retrieving saved data for display');
            $data = $this->api->get_saved_data( $per_page, $page );

            // Get last update time
            $last_update = get_option( 'data_sync_last_update', '' );

            // Prepare HTML for the table
            ob_start();
            include DATA_SYNC_PLUGIN_DIR . 'admin/partials/data-sync-table.php';
            $table_html = ob_get_clean();

            error_log('Data Sync: AJAX request completed successfully');
            wp_send_json_success( array(
                'message'     => sprintf(
                    /* translators: %d: Number of items successfully synced */
                    __( 'Successfully synced %d items.', 'data-sync' ),
                    $result
                ),
                'table_html'  => $table_html,
                'last_update' => $last_update,
            ) );
        } catch (Exception $e) {
            error_log('Data Sync Exception in AJAX handler: ' . $e->getMessage());
            wp_send_json_error( array( 'message' => $e->getMessage() ) );
        }
    }

    /**
     * AJAX handler for getting item details.
     *
     * @since    1.0.0
     */
    public function ajax_get_item_details() {
        if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ) ), 'data_sync_nonce' ) ) {
            wp_send_json_error( array( 'message' => __( 'Security check failed.', 'data-sync' ) ) );
        }

        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error( array( 'message' => __( 'You do not have permission to perform this action.', 'data-sync' ) ) );
        }

        $item_id = isset( $_POST['id'] ) ? intval( $_POST['id'] ) : 0;

        if ( $item_id <= 0 ) {
            wp_send_json_error( array( 'message' => __( 'Invalid item ID.', 'data-sync' ) ) );
        }

        $item = $this->api->get_item_data( $item_id );

        if ( ! $item ) {
            wp_send_json_error( array( 'message' => __( 'Item not found.', 'data-sync' ) ) );
        }

        ob_start();
        ?>
        <table class="widefat">
            <tbody>
                <?php foreach ( $item['data'] as $key => $value ) : ?>
                    <tr>
                        <th><?php echo esc_html( ucfirst( $key ) ); ?></th>
                        <td><?php echo is_scalar( $value ) ? esc_html( $value ) : esc_html( json_encode( $value ) ); ?></td>
                    </tr>
                <?php endforeach; ?>
                <tr>
                    <th><?php esc_html_e( 'Created At', 'data-sync' ); ?></th>
                    <td><?php echo esc_html( $item['created_at'] ); ?></td>
                </tr>
            </tbody>
        </table>
        <?php
        $html = ob_get_clean();

        wp_send_json_success( array(
            'html' => $html,
        ) );
    }
}
