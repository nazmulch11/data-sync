(function ($) {
    'use strict';

    $(document).ready(function () {
        $('.data-sync-tabs .nav-tab').on('click', function (e) {
            if ($(this).attr('href').startsWith('#')) {
                e.preventDefault();

                var tabId = $(this).attr('href');

                $('.data-sync-tabs .nav-tab').removeClass('nav-tab-active');
                $('.data-sync-tabs .tab-content').removeClass('active');

                $(this).addClass('nav-tab-active');
                $(tabId).addClass('active');
            }
        });

        $('#data-sync-button').on('click', function () {
            var $button = $(this);
            var $container = $('#data-sync-table-container');
            var $message = $('#data-sync-message');

            $button.prop('disabled', true).text(data_sync_params.loading);
            $container.addClass('data-sync-loading');
            $message.hide();

            console.log('Data Sync: Starting AJAX request');

            $.ajax({
                url: data_sync_params.ajax_url,
                type: 'POST',
                data: {
                    action: 'data_sync_fetch_data',
                    nonce: data_sync_params.nonce,
                    page: 1
                },
                success: function (response) {
                    console.log('Data Sync: AJAX response received', response);

                    if (response && response.success) {
                        $container.html(response.data.table_html);

                        $message
                            .removeClass('notice-error')
                            .addClass('notice-success')
                            .html('<p>' + response.data.message + '</p>')
                            .show();

                        $('.data-sync-last-update').text(
                            data_sync_params.last_updated + ' ' + response.data.last_update
                        );
                    } else {
                        var errorMsg = (response && response.data && response.data.message) ?
                            response.data.message : data_sync_params.error;

                        $message
                            .removeClass('notice-success')
                            .addClass('notice-error')
                            .html('<p>' + errorMsg + '</p>')
                            .show();
                    }
                },
                error: function (xhr, status, error) {
                    console.error('Data Sync: AJAX error', status, error);

                    var errorMsg = data_sync_params.error;
                    if (xhr.responseJSON && xhr.responseJSON.data && xhr.responseJSON.data.message) {
                        errorMsg = xhr.responseJSON.data.message;
                    }

                    $message
                        .removeClass('notice-success')
                        .addClass('notice-error')
                        .html('<p>' + errorMsg + '</p>')
                        .show();
                },
                complete: function () {
                    console.log('Data Sync: AJAX request completed');

                    $button.prop('disabled', false).text(data_sync_params.sync_now);
                    $container.removeClass('data-sync-loading');

                    initModal();
                }
            });
        });

        function initModal() {
            var $modal = $('#data-sync-modal');
            var $modalBody = $('#data-sync-modal-body');
            var $modalClose = $('.data-sync-modal-close');

            $('.view-details').on('click', function () {
                var itemId = $(this).data('id');

                $modalBody.html('<p>' + data_sync_params.loading + '</p>');
                $modal.show();

                $.ajax({
                    url: data_sync_params.ajax_url,
                    type: 'POST',
                    data: {
                        action: 'data_sync_get_item_details',
                        nonce: data_sync_params.nonce,
                        id: itemId
                    },
                    success: function (response) {
                        if (response.success) {
                            $modalBody.html(response.data.html);
                        } else {
                            $modalBody.html('<p class="error">' + response.data.message + '</p>');
                        }
                    },
                    error: function () {
                        $modalBody.html('<p class="error">' + data_sync_params.error + '</p>');
                    }
                });
            });

            $modalClose.on('click', function () {
                $modal.hide();
            });

            $(window).on('click', function (event) {
                if ($(event.target).is($modal)) {
                    $modal.hide();
                }
            });
        }

        initModal();
    });

})(jQuery);