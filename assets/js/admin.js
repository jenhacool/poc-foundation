(function ($) {
    $(document).ready(function() {
        $('form#poc-foundation-license-form').on('submit', function(e) {
            e.preventDefault();
            var license_key = $(this).find('input#license-key').val();
            if(license_key.length === 0) {
                alert('License key can not be empty');
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
                    $('form#poc-foundation-license-form input, form#poc-foundation-license-form button').prop('disabled', true);
                },
                success: function (response) {
                    if(response.data.is_valid) {
                        location.reload();
                    } else {
                        $('form#poc-foundation-license-form input, form#poc-foundation-license-form button').prop('disabled', false);
                        alert('License key is not correct');
                    }
                }
            })
        });

        $('table#poc-foundation-lgs-table button.add').click(function(e) {
            e.preventDefault();
            var $table = $(this).closest('table');
            var $row = $table.find('tbody tr:last');
            var $clone = $row.clone();
            var count = $table.find('tbody tr').length;

            $clone.find('select, input').each(function() {
                $(this).val('');
            });

            updateTableRow($clone, count);
            $clone.insertAfter($row);
            return false;
        });

        $(document).on('click', 'table#poc-foundation-lgs-table button.remove', function(e) {
            e.preventDefault();
            if($('table#poc-foundation-lgs-table tbody tr').length === 1) {
                return false;
            }
            $(this).closest('tr').remove();
            $('table#poc-foundation-lgs-table tbody tr').each(function(rowIndex) {
                updateTableRow($(this), rowIndex);
            });
            return false;
        });

        function updateTableRow(row, number) {
            row.find('input, select').each(function () {
                var name = $(this).attr('name');
                name = name.replace(/\[(\d+)\]/, '[' + parseInt(number) + ']');
                $(this).attr('name', name).attr('id', name);
            });
        }

        $('table#poc-foundation-lgs-table button.save').click(function(e) {
            e.preventDefault();
            $.ajax({
                url: poc_foundation_params.ajax_url,
                type: 'POST',
                dataType: 'JSON',
                data: {
                    'action': 'poc_foundation_save_campaign',
                    'campaign': $('form#poc-foundation-campaign-form').serialize(),
                },
                beforeSend: function() {
                    $('table#poc-foundation-lgs-table input, table#poc-foundation-lgs-table select, table#poc-foundation-lgs-table button').prop('disabled', true);
                    $('table#poc-foundation-lgs-table span.spinner').toggleClass('is-active');
                },
                success: function (response) {
                    $('table#poc-foundation-lgs-table input, table#poc-foundation-lgs-table select, table#poc-foundation-lgs-table button').prop('disabled', false);
                    $('table#poc-foundation-lgs-table span.spinner').toggleClass('is-active');
                    return false;
                }
            })
        });
    });
})(jQuery);