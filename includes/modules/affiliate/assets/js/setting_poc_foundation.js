(function($, window, document) {
    console.log(22222222);
    // Listen for the jQuery ready event on the document
    $(function() {

        $('#submit').on('click', function() {
            var APIkey = $('#api_key').val();
            var uid_prefix = $('#uid_prefix').val();
            var default_discount = $('#default_discount').val();
            var default_revenue_share = $('#default_revenue_share').val();
            var ref_rates_1 = $('#ref_rates_1').val();
            var ref_rates_2 = $('#ref_rates_2').val();
            var ref_rates_3 = $('#ref_rates_3').val();
            var ref_rates_4 = $('#ref_rates_4').val();
            var ref_rates_5 = $('#ref_rates_5').val();
            var ref_rates_6 = $('#ref_rates_6').val();
            var ref_rates_7 = $('#ref_rates_7').val();
            var ref_rates_8 = $('#ref_rates_8').val();
            var ref_rates_9 = $('#ref_rates_9').val();
            var ref_rates_10 = $('#ref_rates_10').val();

            if(
                (ref_rates_1 + ref_rates_2 + ref_rates_3 + ref_rates_4 + ref_rates_5 + ref_rates_6 + ref_rates_7 + ref_rates_8 + ref_rates_9 + ref_rates_10) >10000
            ) {

                return;
            }

            var ref_rates = [
                ref_rates_1, ref_rates_2, ref_rates_3, ref_rates_4, ref_rates_5, ref_rates_6, ref_rates_7, ref_rates_8, ref_rates_9, ref_rates_10
            ];
            var poc_foundation = [];
            poc_foundation['api_key'] = APIkey;
            poc_foundation['uid_prefix'] = uid_prefix;
            poc_foundation['default_discount'] = default_discount;
            poc_foundation['default_revenue_share'] = default_revenue_share;
            poc_foundation['ref_rates'] = ref_rates;
            console.log(poc_foundation);
            return;
        });

        var no_states = $('select.edd-no-states');
        if( no_states.length ) {
            no_states.closest('tr').addClass('hidden');
        }

        // Update base state field based on selected base country
        $('select[name="edd_settings[base_country]"]').change(function() {
            var $this = $(this), $tr = $this.closest('tr');
            var data = {
                action: 'edd_get_shop_states',
                country: $this.val(),
                nonce: $this.data('nonce'),
                field_name: 'edd_settings[base_state]'
            };
            $.post(ajaxurl, data, function (response) {
                if( 'nostates' == response ) {
                    $tr.next().addClass('hidden');
                } else {
                    $tr.next().removeClass('hidden');
                    $tr.next().find('select').replaceWith( response );
                }
            });

            return false;
        });

        // Update tax rate state field based on selected rate country
        $( document.body ).on('change', '#edd_tax_rates select.edd-tax-country', function() {
            var $this = $(this);
            var data = {
                action: 'edd_get_shop_states',
                country: $this.val(),
                nonce: $this.data('nonce'),
                field_name: $this.attr('name').replace('country', 'state')
            };
            $.post(ajaxurl, data, function (response) {
                if( 'nostates' == response ) {
                    var text_field = '<input type="text" name="' + data.field_name + '" value=""/>';
                    $this.parent().next().find('select').replaceWith( text_field );
                } else {
                    $this.parent().next().find('input,select').show();
                    $this.parent().next().find('input,select').replaceWith( response );
                }
            });

            return false;
        });



        //remove tax row
        $( document.body ).on('click', '#edd_tax_rates .edd_remove_tax_rate', function() {
            console.log(222);
            // if( confirm( edd_vars.delete_tax_rate ) ) {
                var tax_rates = $('#edd_tax_rates tr:visible');
                var count     = tax_rates.length;

                if( count === 1 ) {
                    $('#edd_tax_rates select').val('');
                    $('#edd_tax_rates input[type="text"]').val('');
                    $('#edd_tax_rates input[type="number"]').val('');
                    $('#edd_tax_rates input[type="checkbox"]').attr('checked', false);
                } else {
                    $(this).closest('tr').remove();
                }

                /* re-index after deleting */
                $('#edd_tax_rates tr').each( function( rowIndex ) {
                    $(this).children().find( 'input, select' ).each(function() {
                        var name = $( this ).attr( 'name' );
                        name = name.replace( /\[(\d+)\]/, '[' + ( rowIndex - 1 ) + ']');
                        $( this ).attr( 'name', name ).attr( 'id', name );
                    });
                });
            // }
            return false;
        });

        // Insert new tax rate row
        $('#edd_add_tax_rate').on('click', function() {
            console.log(1111);
            var row = $('#edd_tax_rates tr:last');
            var clone = row.clone();
            var count = row.parent().find( 'tr' ).length;
            clone.find( 'td input' ).not(':input[type=checkbox]').val( '' );
            clone.find( 'input' ).each(function() {
                var name = $( this ).attr( 'name' );
                name = name.replace( /\[(\d+)\]/, '[' + parseInt( count ) + ']');
                $( this ).attr( 'name', name ).attr( 'id', name );
            });
            clone.find( 'i' ).each
            clone.insertAfter( row );
            return false;
        });

    });



}(window.jQuery, window, document));