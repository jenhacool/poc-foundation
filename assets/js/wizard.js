var POC_Foundation_Setup_Wizard = (function($) {
    var t;
    var current_step = '';
    var step_pointer = '';

    var callbacks = {
        do_next_step: function( btn ) {
            do_next_step( btn );
        },
        install_plugins: function(btn){
            var plugins = new PluginManager();
            plugins.init( btn );
        },
        install_widgets: function( btn ) {
            var widgets = new WidgetManager();
            widgets.init( btn );
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

        $('.wizard-steps li.step' ).css('height', maxHeight);
        $('.wizard-steps li.step:first-child').addClass('active-step');
        $('.wizard-nav li:first-child').addClass( 'active-step' );

        $('#poc-foundation-wizard').addClass('loaded');

        $('.do-it').on('click', function(e) {
            e.preventDefault();
            step_pointer = $(this).data('step');
            current_step = $('.step-' + $(this).data('step'));
            $('#poc-foundation-wizard').addClass( 'spinning' );
            if($(this).data('callback') && typeof callbacks[$(this).data('callback')] != 'undefined'){
                // we have to process a callback before continue with form submission
                callbacks[$(this).data('callback')](this);
                return false;
            } else {
                loading_content();
                return true;
            }
        });
    }

    function loading_content() {

    }

    function do_next_step( btn ) {
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