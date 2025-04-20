<?php
/**
 * Provide a admin area view for the plugin
 *
 * This file is used to markup the admin-facing aspects of the plugin.
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

<div class="wrap data-sync-admin">
    <h1><?php echo esc_html( get_admin_page_title() ); ?></h1>

    <div class="data-sync-tabs">
        <nav class="nav-tab-wrapper">
            <a href="#settings" class="nav-tab "><?php esc_html_e( 'Settings', 'data-sync' ); ?></a>
            <a href="#data" class="nav-tab nav-tab-active"><?php esc_html_e( 'Data', 'data-sync' ); ?></a>
        </nav>

        <div id="settings" class="tab-content">
            <form method="post" action="options.php">
                <?php

                settings_fields( 'data_sync_options' );
                do_settings_sections( $this->plugin_name );
                submit_button();
                ?>
            </form>
        </div>

        <div id="data" class="tab-content active">
            <div class="data-sync-header">
                <h2><?php esc_html_e( 'Synced Data', 'data-sync' ); ?></h2>

                <div class="data-sync-actions">
                    <button id="data-sync-button" class="button button-primary">
                        <?php esc_html_e( 'Sync Now', 'data-sync' ); ?>
                    </button>

                    <?php if ( ! empty( $last_update ) ) : ?>
                        <span class="data-sync-last-update">
                            <?php
                            printf(
                                /* translators: %s: formatted date and time of last update */
                                esc_html__( 'Last updated: %s', 'data-sync' ),
                                esc_html( date_i18n( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), strtotime( $last_update ) ) )
                            );
                            ?>
                        </span>
                    <?php endif; ?>
                </div>
            </div>

            <div id="data-sync-message" class="notice" style="display: none;"></div>

            <div id="data-sync-table-container">
                <?php include DATA_SYNC_PLUGIN_DIR . 'admin/partials/data-sync-table.php'; ?>
            </div>
        </div>
    </div>
</div>
