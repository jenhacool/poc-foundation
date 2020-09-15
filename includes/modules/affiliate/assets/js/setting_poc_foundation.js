(function($, window, document) {
    // Listen for the jQuery ready event on the document
    $(function() {

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

        $('#form_affiliate').validate({
            rules: {
                'poc_foundation[api_key]': 'required',
                'poc_foundation[uid_prefix]': 'required',
                'poc_foundation[default_discount]': 'required',
                'poc_foundation[default_revenue_share]': 'required',

            },

            submitHandler: function(form) {
                var total = 0;
                var isValid = 1;
                var floor = 0;
                $('#edd_tax_rates tr td input').each(function() {
                    total = total + parseInt($(this).val());
                    floor++;
                });
                if(floor > 10) {
                    $( "#error_total_floor" ).remove();
                    $( "#error_total_rate" ).remove();
                    isValid = 0;
                    $( "#title_add_rate" ).append( "<p id='error_total_floor' style='color: red'>Total floor : not more than 10</p>" );
                }

                if (total > 100){
                    $( "#error_total_rate" ).remove();
                    $( "#error_total_floor" ).remove();
                    isValid = 0;
                    $( "#title_add_rate" ).append( "<p id='error_total_rate' style='color: red'>Total : not more than 100</p>" );
                }

                if(isValid == 1) {
                    form.submit();

                }

            }

        });

        var no_states = $('select.edd-no-states');
        if( no_states.length ) {
            no_states.closest('tr').addClass('hidden');
        }

        //remove tax row
        $( document.body ).on('click', '#edd_tax_rates .edd_remove_tax_rate', function() {
                var tax_rates = $('#edd_tax_rates tr:visible');
                var count     = tax_rates.length;

                if( count === 1 ) {
                    $('#edd_tax_rates select').val('');
                    $('#edd_tax_rates input[type="text"]').val('');
                    $('#edd_tax_rates input[type="number"]').val('');
                } else {
                    $(this).closest('tr').remove();
                }
            return false;
        });

        // Insert new tax rate row
        $('#edd_add_tax_rate').on('click', function() {
            var row = $('#edd_tax_rates tr:last');
            var clone = row.clone();
            var count = row.parent().find( 'tr' ).length;
            clone.find( 'td input' ).not(':input[type=checkbox]').val( '' );
            clone.find( 'input' ).each(function() {
                var name = $( this ).attr( 'name' );
                name = name.replace( /\[(\d+)\]/, '[' + parseInt( count ) + ']');
                $( this ).attr( 'name', name ).attr( 'id', name );
            });
            clone.find( 'i' ).html('Floor '+(count+1) +':')
            clone.insertAfter( row );
            return false;
        });

    });



}(window.jQuery, window, document));