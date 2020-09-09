(function($) {
    window.poc_foundation_setup_wizard = {
        init: function() {
            var timeout = null;

            $('input#license_key').on('keyup', function() {
                clearTimeout(timeout);
                timeout = setTimeout(function () {
                    poc_foundation_setup_wizard.checkLicenseKey();
                }, 600);
            })
        },
        checkLicenseKey: function() {
            $.ajax({
                url: poc_foundation_setup_data.ajax_url,
                type: 'POST',
                dataType: 'JSON',
                data: {
                    'action': 'poc_foundation_check_license_key',
                    'license_key': $('input#license_key').val(),
                },
                beforeSend: function() {
                    $('input#license_key').prop('disabled', true);
                    $('.setup-actions').hide();
                },
                success: function (response) {
                    if(response.data.is_valid) {
                        $('input#license_key').prop('disabled', false);
                        $('.setup-actions').show();
                    } else {
                        alert('License key is not valid. Please try again.');
                    }
                }
            })
        }
    }

    $(document).ready(function() {
        poc_foundation_setup_wizard.init();
    });
})(jQuery);