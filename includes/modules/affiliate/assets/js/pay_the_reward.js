(function($, window, document) {
    // Listen for the jQuery ready event on the document
    $(function() {
        $('#submit_pay_reward').on('click', function() {
            //take all referral id
            var data_array = [];
            $("#table_id_referral tr").each(function () {
                data_array.push(this.id)
            });
            console.log(data_array, 11111);
            data_array.every(async function(element, index) {
                var data = await getDatafromServer(element);
                console.log(element, data.data.reward_status);
                updateDom(data.data.reward_status, element);
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

    function updateDom(data, element) {
        let id = '#'+ 'id_referral_' + element;
        $(id).html(data)
    }

}(window.jQuery, window, document));