<?php
/**
 * Provide a admin area view for the data table
 *
 * @link       https://nazmul.xyz
 * @since      1.0.0
 *
 * @package    Data_Sync
 * @subpackage Data_Sync/admin/partials
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
    die;
}
?>

<?php if ( empty( $data['items'] ) ) : ?>
    <div class="data-sync-no-data">
        <p><?php esc_html_e( 'No data available. Click "Sync Now" to fetch data from the API.', 'data-sync' ); ?></p>
    </div>
<?php else : ?>
    <table class="wp-list-table widefat fixed striped data-sync-table">
        <thead>
            <tr>
                <th scope="col" class="column-id"><?php esc_html_e( 'ID', 'data-sync' ); ?></th>
                <th scope="col" class="column-title"><?php esc_html_e( 'Title', 'data-sync' ); ?></th>
                <th scope="col" class="column-description"><?php esc_html_e( 'Description', 'data-sync' ); ?></th>
                <th scope="col" class="column-details"><?php esc_html_e( 'Details', 'data-sync' ); ?></th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ( $data['items'] as $item ) : ?>
                <tr>
                    <td class="column-id"><?php echo esc_html( $item['item_id'] ); ?></td>
                    <td class="column-title"><?php echo esc_html( $item['title'] ); ?></td>
                    <td class="column-description">
                        <?php echo esc_html( wp_trim_words( $item['description'], 20, '...' ) ); ?>
                    </td>
                    <td class="column-details">
                        <button type="button" class="button view-details"
                                data-id="<?php echo esc_attr( $item['id'] ); ?>">
                            <?php esc_html_e( 'View Details', 'data-sync' ); ?>
                        </button>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <?php if ( $data['total_pages'] > 1 ) : ?>
        <div class="tablenav bottom">
            <div class="tablenav-pages">
                <span class="displaying-num">
                    <?php
                    printf(
                        /* translators: %s: number of items */
                        esc_html( _n( '%s item', '%s items', $data['total_items'], 'data-sync' ) ),
                        esc_html( number_format_i18n( $data['total_items'] ) )
                    );
                    ?>
                </span>
                <span class="pagination-links">
                    <?php
                    echo wp_kses_post( paginate_links( array(
                        'base'      => add_query_arg( 'paged', '%#%' ),
                        'format'    => '',
                        'prev_text' => '&laquo;',
                        'next_text' => '&raquo;',
                        'total'     => $data['total_pages'],
                        'current'   => $data['current_page'],
                    ) ) );
                    ?>
                </span>
            </div>
        </div>
    <?php endif; ?>

    <!-- Modal for item details -->
    <div id="data-sync-modal" class="data-sync-modal" style="display: none;">
        <div class="data-sync-modal-content">
            <span class="data-sync-modal-close">&times;</span>
            <h2><?php esc_html_e( 'Item Details', 'data-sync' ); ?></h2>
            <div id="data-sync-modal-body">
                <p><?php esc_html_e( 'Loading...', 'data-sync' ); ?></p>
            </div>
        </div>
    </div>
<?php endif; ?>
