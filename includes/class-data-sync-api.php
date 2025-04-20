<?php

/**
 * Handles API interactions.
 *
 * This class defines all code necessary to interact with the external API.
 *
 * @since      1.0.0
 * @package    Data_Sync
 * @subpackage Data_Sync/includes
 * @author     Nazmul Hosen <nazmul.ch11@gmail.com>
 */
class Data_Sync_API {

    /**
     * The API endpoint URL.
     *
     * @since    1.0.0
     * @access   private
     * @var      string    $api_url    The API endpoint URL.
     */
    private $api_url;

    /**
     * The API token.
     *
     * @since    1.0.0
     * @access   private
     * @var      string    $api_token    The API token.
     */
    private $api_token;

    /**
     * The database table name.
     *
     * @since    1.0.0
     * @access   private
     * @var      string    $table_name    The database table name.
     */
    private $table_name;

    /**
     * The wpdb object.
     *
     * @since    1.0.0
     * @access   private
     * @var      wpdb    $wpdb    The wpdb object.
     */
    private $wpdb;

    /**
     * Initialize the class and set its properties.
     *
     * @since    1.0.0
     */
    public function __construct() {
        global $wpdb;

        $this->api_token = get_option( 'data_sync_api_token', '' );
        $this->table_name = $wpdb->prefix . 'data_sync_items';
        $this->wpdb = $wpdb;
    }

    /**
     * Fetch data from the API.
     *
     * @since    1.0.0
     * @param    string    $endpoint    The API endpoint to fetch data from.
     * @param    array     $args        Additional arguments for the request.
     * @return   array|WP_Error         The API response or WP_Error on failure.
     */
    public function fetch_data( $endpoint = '/posts', $args = array() ) {
        try {
            $request_url = get_option( 'data_sync_api_url' ) . $endpoint;
            error_log('Data Sync: Fetching data from ' . $request_url);

            $request_args = array(
                'timeout'     => 300,
                'redirection' => 5,
                'httpversion' => '1.1',
                'user-agent'  => 'WordPress/' . get_bloginfo( 'version' ) . '; ' . home_url(),
                'headers'     => array(
                    'Accept' => 'application/json',
                ),
                'stream'      => false,
            );

            if ( ! empty( $this->api_token ) ) {
                $request_args['headers']['Authorization'] = 'Bearer ' . $this->api_token;
            }

            $request_args = wp_parse_args( $args, $request_args );

            $response = wp_remote_get( $request_url, $request_args );

            if ( is_wp_error( $response ) ) {
                error_log('Data Sync Error: ' . $response->get_error_message());
                return $response;
            }

            $response_code = wp_remote_retrieve_response_code( $response );
            if ( $response_code !== 200 ) {
                $error_message = sprintf( __( 'API request failed with status code: %d', 'data-sync' ), $response_code );
                error_log('Data Sync Error: ' . $error_message);
                return new WP_Error('api_error', $error_message);
            }

            $body = wp_remote_retrieve_body( $response );
            if ( empty( $body ) ) {
                error_log('Data Sync Error: Empty response body');
                return new WP_Error('empty_response', __( 'Empty response from API', 'data-sync' ));
            }


            $data = json_decode( $body, true );

            if ( $data === null && json_last_error() !== JSON_ERROR_NONE ) {
                $json_error = json_last_error_msg();
                error_log('Data Sync Error: JSON parsing failed - ' . $json_error);
                return new WP_Error('json_error', __( 'Failed to parse API response: ', 'data-sync' ) . $json_error);
            }

            error_log('Data Sync: Successfully fetched and parsed data');

            return $data;
        } catch ( Exception $e ) {
            error_log('Data Sync Exception: ' . $e->getMessage());
            return new WP_Error('exception', $e->getMessage());
        }
    }


    /**
     * Parse a JSON file in a memory-efficient way.
     *
     * @since    1.0.0
     * @param    string    $file_path    Path to the JSON file.
     * @return   array|null              Parsed JSON data or null on failure.
     */
    private function parse_json_file( $file_path ) {

        $content = file_get_contents( $file_path );

        if ( $content === false ) {
            return null;
        }

        $data = json_decode( $content, true );

        unset( $content );

        if ( $data === null && json_last_error() !== JSON_ERROR_NONE ) {
            return null;
        }

        return $data;
    }

    /**
     * Save fetched data to the database.
     *
     * @since    1.0.0
     * @param    array    $items    The items to save.
     * @return   int|WP_Error       The number of items saved or WP_Error on failure.
     */
    public function save_data( $items ) {
        global $wpdb;

        try {
            if ( empty( $items ) || ! is_array( $items ) ) {
                error_log('Data Sync Error: No valid data to save');
                return new WP_Error(
                    'invalid_data',
                    __( 'No valid data to save', 'data-sync' )
                );
            }

            error_log('Data Sync: Starting to save ' . count($items) . ' items');
            $count = 0;

            $truncate_result = $wpdb->query( "TRUNCATE TABLE {$this->table_name}" );
            if ($truncate_result === false) {
                error_log('Data Sync Error: Failed to truncate table - ' . $wpdb->last_error);
                return new WP_Error('db_error', __('Failed to clear existing data: ', 'data-sync') . $wpdb->last_error);
            }

            $chunk_size = 20; 
            $chunks = array_chunk($items, $chunk_size);

            foreach ($chunks as $chunk_index => $chunk) {
                error_log('Data Sync: Processing chunk ' . ($chunk_index + 1) . ' of ' . count($chunks));

                foreach ($chunk as $item) {
  
                    if (!isset($item['id'])) {
                        continue;
                    }

                    $title = isset($item['title']) ? sanitize_text_field($item['title']) : '';
                    $description = isset($item['body']) ? sanitize_textarea_field($item['body']) : '';

                    $data_to_store = array();
                    foreach ($item as $key => $value) {
                        if (in_array($key, array('id', 'title', 'body', 'userId'))) {
                            $data_to_store[$key] = $value;
                        }
                    }

                    $json_data = wp_json_encode($data_to_store);
                    if ($json_data === false) {
                        error_log('Data Sync Warning: Failed to JSON encode item data for ID ' . $item['id']);
                        $json_data = '{}';
                    }

                    $result = $wpdb->insert(
                        $this->table_name,
                        array(
                            'item_id'     => sanitize_text_field($item['id']),
                            'title'       => $title,
                            'description' => $description,
                            'data'        => $json_data,
                        ),
                        array(
                            '%s',
                            '%s',
                            '%s',
                            '%s',
                        )
                    );

                    if ($result) {
                        $count++;
                    } else {
                        error_log('Data Sync Warning: Failed to insert item ID ' . $item['id'] . ' - ' . $wpdb->last_error);
                    }
                }

                unset($chunk);
                if (function_exists('wp_cache_flush')) {
                    wp_cache_flush();
                }
            }

            update_option('data_sync_last_update', current_time('mysql'));

            error_log('Data Sync: Successfully saved ' . $count . ' items');
            return $count;
        } catch (Exception $e) {
            error_log('Data Sync Exception in save_data: ' . $e->getMessage());
            return new WP_Error('exception', $e->getMessage());
        }
    }

    /**
     * Get saved data from the database.
     *
     * @since    1.0.0
     * @param    int       $per_page    Number of items per page.
     * @param    int       $page        Current page number.
     * @return   array                  Array containing items and pagination info.
     */
    public function get_saved_data( $per_page = 10, $page = 1 ) {
        global $wpdb;

        $offset = ( $page - 1 ) * $per_page;

        $total_items = $wpdb->get_var( "SELECT COUNT(*) FROM {$this->table_name}" );

        $items = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT id, item_id, title, description FROM {$this->table_name} ORDER BY id DESC LIMIT %d OFFSET %d",
                $per_page,
                $offset
            ),
            ARRAY_A
        );

        return array(
            'items'       => $items,
            'total_items' => (int) $total_items,
            'total_pages' => ceil( $total_items / $per_page ),
            'per_page'    => (int) $per_page,
            'current_page'=> (int) $page,
        );
    }

    /**
     * Get single item data from the database.
     *
     * @since    1.0.0
     * @param    int       $id    The item ID.
     * @return   array|false     The item data or false if not found.
     */
    public function get_item_data( $id ) {
        global $wpdb;

        $item = $wpdb->get_row(
            $wpdb->prepare(
                "SELECT * FROM {$this->table_name} WHERE id = %d",
                $id
            ),
            ARRAY_A
        );

        if ( $item ) {
            $item['data'] = json_decode( $item['data'], true );
        }

        return $item;
    }

    /**
     * Set the API token.
     *
     * @since    1.0.0
     * @param    string    $token    The API token.
     */
    public function set_api_token( $token ) {
        $this->api_token = sanitize_text_field( $token );
    }

    /**
     * Get the API token.
     *
     * @since    1.0.0
     * @return   string    The API token.
     */
    public function get_api_token() {
        return $this->api_token;
    }
}
