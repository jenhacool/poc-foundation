(function($, window, document) {
    // Listen for the jQuery ready event on the document
    $(function() {

        $.ajax({
            url: send_token_ajax_data.ajax_url,
            type: 'POST',
            dataType: 'json',
            data:{
                action: 'take_data_user',
                order_id: 168
            },
            context: this,
            success: function( response ){
                //Do something with the result from server
                if(response.success){
                    var id = '#' + 168;
                    if(response.data.reward_status == 'error'){
                        // update to table with column status
                        $(id).html('error')
                    } else if ( response.data.reward_status == 'success' ) {
                        $(id).html('success');
                    }
                    console.log(response.data);
                    $.ajax({
                        url: send_token_ajax_data.ajax_url,
                        type: 'POST',
                        dataType: 'json',
                        data:{
                            action: 'take_data_user',
                            order_id: 169
                        },
                        context: this,
                        success: function( response ){

                            if(response.success){
                                var id = '#' + 169;
                                if(response.data.reward_status == 'error'){
                                    // update to table with column status
                                    $(id).html('error')
                                } else if ( response.data.reward_status == 'success' ) {
                                    $(id).html('success');
                                }
                            }
                        }
                    });
                }
            }
        });
    });


    // The rest of the code goes here!

}(window.jQuery, window, document));