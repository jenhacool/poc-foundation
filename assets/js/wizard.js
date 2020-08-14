var POC_Foundation_Setup_Wizard = (function($) {
    var t;
    var current_step = '';
    var step_pointer = '';

    var callbacks = {
        do_next_step: function(btn) {
            do_next_step( btn );
        },
        check_license: function(btn) {
            var license = new LicenseManager();
            license.init(btn);
        },
        install_plugins: function(btn) {
            var plugins = new PluginManager();
            plugins.init(btn);
        },
        save_campaign: function(btn) {
            save_campaign(btn);
        },
        save_config: function(btn) {
            var config = new ConfigManager();
            config.init(btn);
        },
    };
    
    function window_loaded() {
        var maxHeight = 0;

        // $('#poc-foundation-wizard .wizard-steps li.step').each( function(index) {
        //     $(this).attr( 'data-height', $(this).innerHeight());
        //     if($(this).innerHeight() > maxHeight) {
        //         maxHeight = $(this).innerHeight();
        //     }
        // });

        $('.wizard-steps li .detail').each( function(index) {
            // $(this).attr('data-height', $(this).innerHeight());
            $(this).addClass('scale-down');
        });

        // $('.wizard-steps li.step').css('height', maxHeight);
        // $('.wizard-steps li.step').addClass('active-step');
        $('.wizard-steps li.step:first-child').addClass('active-step');
        $('.wizard-nav li:first-child').addClass( 'active-step' );

        $('#poc-foundation-wizard').addClass('loaded');

        $('.do-it').on('click', function(e) {
            e.preventDefault();
            step_pointer = $(this).data('step');
            current_step = $('.step-' + $(this).data('step'));
            $('#poc-foundation-wizard').addClass( 'spinning' );
            if($(this).data('callback') && typeof callbacks[$(this).data('callback')] != 'undefined'){
                callbacks[$(this).data('callback')]($(this));
                return false;
            } else {
                loading_content();
                return true;
            }
        });
    }

    function loading_content() {

    }

    function do_next_step(btn) {
        current_step.removeClass('active-step');
        $('.nav-step-' + step_pointer).removeClass('active-step');
        current_step.addClass( 'done-step' );
        $('.nav-step-' + step_pointer).addClass('done-step');
        current_step.fadeOut( 500, function() {
            current_step = current_step.next();
            step_pointer = current_step.data('step');
            current_step.fadeIn();
            current_step.addClass('active-step');
            $('.nav-step-' + step_pointer).addClass('active-step');
            $('#poc-foundation-wizard').removeClass('spinning');
        });
    }

    function LicenseManager() {
        function check_license(btn) {
            var license_key = $('form#check-license-form input#license-key').val();
            if(license_key.length === 0) {
                $('#poc-foundation-wizard').removeClass('spinning');
                alert('License key can\'t be empty');
                return false;
            }
            $.ajax({
                url: poc_foundation_params.ajax_url,
                type: 'POST',
                dataType: 'JSON',
                data: {
                    'action': 'poc_foundation_check_license_key',
                    'license_key': license_key,
                },
                beforeSend: function() {
                    $('form#check-license-form').find('p.license-invalid').remove();
                    $('#poc-foundation-wizard').addClass('spinning');
                },
                success: function (response) {
                    $('#poc-foundation-wizard').removeClass('spinning');
                    if(response.data.is_valid) {
                        do_next_step(btn);
                    } else {
                        alert('License key is not valid. Please try again.');
                    }
                }
            })
        }

        return {
            init: function(btn) {
                check_license(btn);
            }
        }
    }

    function PluginManager(){
        var complete;
        var items_completed = 0;
        var current_item = '';
        var current_node;

        function run_loop() {
            var do_next = false;
            if(current_node){
                if(!current_node.data('done_item')){
                    items_completed++;
                    current_node.data('done_item',1);
                }
            }
            var items = $('#poc-foundation-wizard .plugin-list li')
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
                // finished all plugins!
                complete();
            }
        }

        function setup() {
            current_node.find('span.plugin-label').html('<span class="spinner is-active"></span>');
            $.ajax({
                url: poc_foundation_params.ajax_url,
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
            current_node.find('span.plugin-label').html('<span class="dashicons dashicons-yes"></span>');
            run_loop();
        }

        return {
            init: function(btn){
                complete = function() {
                    $.post(poc_foundation_params.ajax_url, {
                        'action': 'poc_foundation_clear_update_cache',
                    });
                    do_next_step();
                };
                $('#poc-foundation-wizard').addClass('spinning');
                run_loop(btn);
            }
        }
    }

    function ConfigManager() {
        function save_config(btn) {
            $.ajax({
                url: poc_foundation_params.ajax_url,
                type: 'POST',
                dataType: 'JSON',
                data: {
                    'action': 'poc_foundation_save_config',
                    'poc_foundation_config': $('form#plugin-config-form').serialize()
                },
                beforeSend: function() {
                    $('form#check-license-form').find('p.license-invalid').remove();
                },
                success: function () {
                    do_next_step(btn);
                }
            })
        }

        return {
            init: function(btn) {
                complete = function() {
                    do_next_step(btn);
                };

                save_config(btn);
            }
        }
    }

    function save_campaign(btn) {
        $.ajax({
            url: poc_foundation_params.ajax_url,
            type: 'POST',
            dataType: 'JSON',
            data: {
                'action': 'poc_foundation_save_campaign',
                'campaign': $('form#poc-foundation-campaign-form').serialize(),
            },
            beforeSend: function() {
                $('#poc-foundation-wizard').addClass('spinning');
            },
            success: function (response) {
                do_next_step(btn);
            }
        })
    }

    return {
        init: function() {
            t = this;
            $(window_loaded)
        },
        callback: function(func){
            console.log(func);
            console.log(this);
        }
    }
})(jQuery);

POC_Foundation_Setup_Wizard.init();