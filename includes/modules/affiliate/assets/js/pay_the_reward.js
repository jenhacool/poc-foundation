(function($, window, document) {
    // Listen for the jQuery ready event on the document
    $(function() {
        $('#submit_pay_reward').on('click', function() {
            //take all referral id
            var data_array = [];
            $("#table_id_referral tr").each(function () {
                data_array.push(this.id)
            });
            data_array.every(async function(element, index) {
                var data = await getDatafromServer(element);
                updateDom(data.data.reward_status, element, data.data.message );
            });
        });
    });

    function getDatafromServer(element) {
        return $.ajax({
            url: send_token_ajax_data.ajax_url,
            type: 'POST',
            dataType: 'json',
            data: {
                action: 'take_data_user',
                order_id: element
            },
            context: this,
        });
    }

    function updateDom(data, element, message) {
        let id = '#'+ 'id_referral_' + element;
        $(id).html(data)
        if(data === 'success'){
            $(id).append("<p style='color: #28a745'> <b>"+ message + "</b></p>");
            return;
        }
        $(id).append("<p style='color: red'> <b>"+ message + "</b></p>");
    }

}(window.jQuery, window, document));