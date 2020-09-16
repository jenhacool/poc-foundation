(function($) {
    window.poc_foundation_bitrix24 = {
        items: {
            queued: [],
            processed: [],
            failed: []
        },
        request: null,
        init: function() {
            $('#poc_foundation_bitrix24').dialog({
                title: 'Send to Bitrix24',
                dialogClass: 'wp-dialog',
                autoOpen: false,
                draggable: false,
                width: 400,
                modal: true,
                resizable: false,
                closeOnEscape: true,
                position: {
                    my: "center",
                    at: "center",
                    of: window
                },
                open: function () {
                    $('.ui-widget-overlay').bind('click', function(){
                        $('#poc_foundation_bitrix24').dialog('close');
                    })
                },
                create: function () {
                    $('.ui-dialog-titlebar-close').addClass('ui-button');
                },
                close: function () {
                    poc_foundation_bitrix24.resetToggle();
                    poc_foundation_bitrix24.clearLog();
                    poc_foundation_bitrix24.clearItems();
                    poc_foundation_bitrix24.resetCount();
                    poc_foundation_bitrix24.stop();
                }
            });

            $('#wpbody').on('click', '#doaction', function(e) {
                var action = $('#bulk-action-selector-top').val();
                if(action != 'send_bitrix24') {
                    return;
                }
                e.preventDefault();
                poc_foundation_bitrix24.setQueuedItems();
                if(poc_foundation_bitrix24.items.queued.length > 0) {
                    poc_foundation_bitrix24.showDialog();
                } else {
                    alert('Please select at least one lead');
                }
            });

            $('#poc_foundation_bitrix24').on('click', 'button.send-button', function(e) {
                poc_foundation_bitrix24.clearLog();
                poc_foundation_bitrix24.clearItems();
                poc_foundation_bitrix24.resetCount();
                poc_foundation_bitrix24.setQueuedItems();
                poc_foundation_bitrix24.toggle();
                $('#poc_foundation_bitrix24 .poc_foundation_bitrix24_log').append('<span>Running...</span><br>');
                poc_foundation_bitrix24.send();
            });

            $('#poc_foundation_bitrix24').on('click', 'button.stop-button', function(e) {
                poc_foundation_bitrix24.stop();
            });

            $('#poc_foundation_bitrix24').on('click', 'button.restart-button', function(e) {
                poc_foundation_bitrix24.clearLog();
                poc_foundation_bitrix24.clearItems();
                poc_foundation_bitrix24.resetCount();
                poc_foundation_bitrix24.setQueuedItems();
                poc_foundation_bitrix24.toggleButton();
                $('#poc_foundation_bitrix24 .poc_foundation_bitrix24_log').append('<span>Running...</span><br>');
                poc_foundation_bitrix24.send();
            });

            $('#poc_foundation_bitrix24').on('click', '.bitrix24_stage_reload', function(e) {
                e.stopPropagation();
                poc_foundation_bitrix24.reloadStage();
            });
        },
        showDialog: function() {
            $('#poc_foundation_bitrix24').find('span.queued').text(poc_foundation_bitrix24.items.queued.length);
            $('#poc_foundation_bitrix24').dialog('open');
        },
        resetCount: function() {
            $('#poc_foundation_bitrix24').find('span.processed').text(0);
            $('#poc_foundation_bitrix24').find('span.failed').text(0);
        },
        toggle: function() {
            $('#poc_foundation_bitrix24').find('.poc_foundation_bitrix24_form').toggle();
            $('#poc_foundation_bitrix24').find('.poc_foundation_bitrix24_status').toggle();
        },
        resetToggle: function() {
            $('#poc_foundation_bitrix24').find('.poc_foundation_bitrix24_form').show();
            $('#poc_foundation_bitrix24').find('.poc_foundation_bitrix24_status').hide();
        },
        toggleButton: function() {
            $('#poc_foundation_bitrix24 .stop-button').toggle();
            $('#poc_foundation_bitrix24 .restart-button').toggle();
        },
        clearLog: function() {
            $('#poc_foundation_bitrix24 .poc_foundation_bitrix24_log').empty();
        },
        clearItems: function() {
            $.each(poc_foundation_bitrix24.items, function(key) {
                poc_foundation_bitrix24.items[key] = [];
            })
        },
        setQueuedItems: function() {
            $('tbody th.check-column input[type="checkbox"]').each(function() {
                if ($(this).prop('checked')) {
                    var id = $(this).val();
                    var name = $(this).closest('tr').find('td.column-name').text();
                    poc_foundation_bitrix24.items.queued.push({
                        id: id,
                        name: name
                    });
                }
            });
        },
        send: function() {
            var nonce = poc_foundation_bitrix24_data.nonce;
            var ajax_url = poc_foundation_bitrix24_data.ajax_url;
            var stage_id = $('#poc_foundation_bitrix24_stage_select option:selected').val();
            var processing_item = poc_foundation_bitrix24.items.queued[0]

            poc_foundation_bitrix24.request = $.ajax({
                url: ajax_url,
                type: 'POST',
                data: {
                    action: 'poc_foundation_create_bitrix24_deal',
                    post_id: processing_item.id,
                    stage_id: stage_id
                },
                cache: false,
                beforeSend: function() {
                },
                success: function() {
                    var processed = poc_foundation_bitrix24.items.queued.shift();
                    poc_foundation_bitrix24.items.processed.push(processed);
                    poc_foundation_bitrix24.updateCount();
                    $('#poc_foundation_bitrix24 .poc_foundation_bitrix24_log').append('<span>Deal created for '+processing_item.name+'</span><br>')
                    if(poc_foundation_bitrix24.items.queued.length === 0) {
                        poc_foundation_bitrix24.finished();
                    } else {
                        poc_foundation_bitrix24.send();
                    }
                },
                // error: function() {
                //     var failed = poc_foundation_bitrix24.items.queued.shift();
                //     poc_foundation_bitrix24.items.failed.push(failed);
                //     poc_foundation_bitrix24.updateCount();
                //     if(poc_foundation_bitrix24.items.queued.length == 0) {
                //         poc_foundation_bitrix24.finished();
                //     } else {
                //         poc_foundation_bitrix24.send();
                //     }
                // }
            });
        },
        stop: function() {
            if(poc_foundation_bitrix24.request != null) {
                $('#poc_foundation_bitrix24 button.stop-button').prop('disabled', true);
                poc_foundation_bitrix24.request.abort();
                $('#poc_foundation_bitrix24 .poc_foundation_bitrix24_log').append('<span>Stopped</span><br>');
                $('#poc_foundation_bitrix24 button.stop-button').prop('disabled', false);
                poc_foundation_bitrix24.request = null;
            }
        },
        updateCount: function() {
            $('#poc_foundation_bitrix24').find('span.processed').text(poc_foundation_bitrix24.items.processed.length);
            $('#poc_foundation_bitrix24').find('span.failed').text(poc_foundation_bitrix24.items.failed.length);
        },
        finished: function() {
            $('#poc_foundation_bitrix24 .poc_foundation_bitrix24_log').append('<span>Done</span>');
            poc_foundation_bitrix24.toggleButton();
        },
        reloadStage: function() {
            $.ajax({
                url: poc_foundation_bitrix24_data.ajax_url,
                type: 'POST',
                data: {
                    action: 'poc_foundation_reload_bitrix24_stages',
                },
                cache: false,
                beforeSend: function () {
                    $('.bitrix24_stage_select select').prop('disabled', true);
                    $('.poc_foundation_bitrix24_form button').prop('disabled', true);
                },
                success: function (response) {
                    $('.bitrix24_stage_select select').html(response.data.html);
                    $('.bitrix24_stage_select select').prop('disabled', false);
                    $('.poc_foundation_bitrix24_form button').prop('disabled', false);
                }
            });
        }
    }

    $(document).ready(function() {
        poc_foundation_bitrix24.init();
    });
})(jQuery);