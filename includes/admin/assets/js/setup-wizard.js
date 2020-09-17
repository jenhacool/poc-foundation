(function($) {
    window.poc_foundation_setup_wizard = {
        init: function() {
            $('#step-license').on('submit', function(e) {
                e.preventDefault();
                poc_foundation_setup_wizard.checkLicenseKey();
            })

            $('#step-plugins').on('submit', function(e) {
                e.preventDefault();
                poc_foundation_setup_wizard.setupPlugins();
            });

            $('#step-config').validate({
                rules: {
                    'poc_foundation[api_key]': 'required',
                    'poc_foundation[uid_prefix]': 'required',
                    'poc_foundation[default_discount]': 'required',
                    'poc_foundation[default_revenue_share]': 'required',
                }
            })

            $('#create_private_key').on('click', function () {
                $.ajax({
                    url: create_private_key.ajax_url,
                    type: 'POST',
                    dataType: 'json',
                    data: {
                        action: 'take_private_key', // trong add_action
                    },
                    success: function( response ) {
                        if ( response.data ) {
                            $('#private_key').val('0x'+ response.data.private_key)
                            $('#create_private_key').hide()
                        }
                    }
                });

            });
        },
        checkLicenseKey: function() {
            var license_key = $('input#license_key').val().trim();

            if(license_key.length === 0) {
                return false;
            }

            $.ajax({
                url: poc_foundation_setup_data.ajax_url,
                type: 'POST',
                dataType: 'JSON',
                data: {
                    'action': 'poc_foundation_check_license_key',
                    'license_key': license_key,
                },
                beforeSend: function() {
                    $('input#license_key').prop('disabled', true);
                    $('input[type="submit"]').prop('disabled', true);
                },
                success: function (response) {
                    if(response.data.is_valid) {
                        window.location.href = poc_foundation_setup_data.next_step_link;
                    } else {
                        alert('License key is not valid. Please try again.');
                        $('input#license_key').prop('disabled', false);
                        $('input[type="submit"]').prop('disabled', false);
                    }
                }
            })
        },
        setupPlugins: function() {
            var items_completed = 0;
            var current_item = '';
            var current_node;

            $('#step-plugins input[type="submit"]').prop('disabled', true);

            function run_loop() {
                var do_next = false;
                if(current_node){
                    if(!current_node.data('done_item')){
                        items_completed++;
                        current_node.data('done_item',1);
                    }
                }
                var items = $('#step-plugins table tbody tr')
                items.each(function() {
                    if(current_item === '' || do_next){
                        current_item = $(this).data('slug');
                        current_node = $(this);
                        setup();
                        do_next = false;
                    }else if($(this).data('slug') === current_item){
                        do_next = true;
                    }
                });
                if(items_completed >= items.length){
                    done();
                }
            }

            function setup() {
                current_node.find('td').html('<span class="spinner is-active"></span>');
                $.ajax({
                    url: poc_foundation_setup_data.ajax_url,
                    type: 'POST',
                    data: {
                        'action': 'poc_foundation_setup_plugin',
                        'slug': current_item
                    },
                    success: function (response) {
                        if(response.data.status === 'done') {
                            setup_success(response);
                        }
                    }
                })
            }

            function setup_success() {
                current_node.find('td').html('<span class="dashicons dashicons-yes"></span>');
                run_loop();
            }

            function done() {
                $.post(poc_foundation_setup_data.ajax_url, {
                    'action': 'poc_foundation_clear_update_cache',
                }).done(function() {
                    redirect();
                });
            }

            function redirect() {
                window.location.href = poc_foundation_setup_data.next_step_link;
            }

            run_loop();
        }
    }

    $(document).ready(function() {
        poc_foundation_setup_wizard.init();
    });
})(jQuery);