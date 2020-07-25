var POC_Foundation_Setup_Wizard = (function($) {
    var t;
    var current_step = '';
    var step_pointer = '';

    var callbacks = {
        do_next_step: function(btn) {
            do_next_step( btn );
        },
        install_plugins: function(btn) {
            var plugins = new PluginManager();
            plugins.init(btn);
        },
        save_config: function(btn) {
            var config = new ConfigManager();
            config.init(btn);
        },
        install_content: function(btn){
            var content = new ContentManager();
            content.init( btn );
        }
    };
    
    function window_loaded() {
        var maxHeight = 0;

        $('#poc-foundation-wizard .wizard-steps li.step').each( function(index) {
            $(this).attr( 'data-height', $(this).innerHeight());
            if($(this).innerHeight() > maxHeight) {
                maxHeight = $(this).innerHeight();
            }
        });

        $('.wizard-steps li .detail').each( function(index) {
            $(this).attr('data-height', $(this).innerHeight());
            $(this).addClass('scale-down');
        });

        $('.wizard-steps li.step').css('height', maxHeight);
        $('.wizard-steps li.step').addClass('active-step');
        // $('.wizard-steps li.step:first-child').addClass('active-step');
        // $('.wizard-nav li:first-child').addClass( 'active-step' );

        $('#poc-foundation-wizard').addClass('loaded');

        $('.do-it').on('click', function(e) {
            // e.preventDefault();
            // step_pointer = $(this).data('step');
            // current_step = $('.step-' + $(this).data('step'));
            // $('#poc-foundation-wizard').addClass( 'spinning' );
            if($(this).data('callback') && typeof callbacks[$(this).data('callback')] != 'undefined'){
                // we have to process a callback before continue with form submission
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

    function PluginManager(){
        var complete;
        var items_completed = 0;
        var current_item = '';
        var current_node;
        var current_item_hash = '';

        function ajax_callback(response){
            if(typeof response == 'object' && typeof response.message != 'undefined'){
                $current_node.find('span').text(response.message);
                if(typeof response.url != 'undefined'){
                    // we have an ajax url action to perform.

                    if(response.hash == current_item_hash){
                        $current_node.find('span').text("failed");
                        find_next();
                    }else {
                        current_item_hash = response.hash;
                        jQuery.post(response.url, response, function(response2) {
                            process_current();
                            $current_node.find('span').text(response.message + whizzie_params.verify_text);
                        }).fail(ajax_callback);
                    }

                }else if(typeof response.done != 'undefined'){
                    // finished processing this plugin, move onto next
                    find_next();
                }else{
                    // error processing this plugin
                    find_next();
                }
            }else{
                // error - try again with next plugin
                $current_node.find('span').text("ajax error");
                find_next();
            }
        }
        function process_current() {
            if(current_item){
                // query our ajax handler to get the ajax to send to TGM
                // if we don't get a reply we can assume everything worked and continue onto the next one.
                jQuery.post(whizzie_params.ajaxurl, {
                    action: 'setup_plugins',
                    wpnonce: whizzie_params.wpnonce,
                    slug: current_item
                }, ajax_callback).fail(ajax_callback);
            }
        }
        function find_next(){
            var do_next = false;
            if($current_node){
                if(!$current_node.data('done_item')){
                    items_completed++;
                    $current_node.data('done_item',1);
                }
                $current_node.find('.spinner').css('visibility','hidden');
            }
            var $li = $('#poc-foundation-wizard .plugin-list li');
            $li.each(function(){
                if(current_item == '' || do_next){
                    current_item = $(this).data('slug');
                    $current_node = $(this);
                    process_current();
                    do_next = false;
                }else if($(this).data('slug') == current_item){
                    do_next = true;
                }
            });
            if(items_completed >= $li.length){
                // finished all plugins!
                complete();
            }
        }

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
                    setup_success(response);
                }
            })
        }

        function setup_success(response) {
            if(response.data.status === 'done') {
                current_node.find('span.plugin-label').html('<span class="dashicons dashicons-yes"></span>');
                run_loop();
            }
        }

        return {
            init: function(btn){
                complete = function() {
                    $.post(poc_foundation_params.ajax_url, {
                        'action': 'poc_foundation_clear_update_cache',
                    });
                    do_next_step();
                };
                run_loop(btn);
            }
        }
    }

    function ConfigManager() {
        function save_config() {
            $.ajax({
                url: poc_foundation_params.ajax_url,
                type: 'POST',
                dataType: 'JSON',
                data: {
                    'action': 'poc_foundation_save_config',
                    'poc_foundation_config': $('form#plugin-config-form').serialize()
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