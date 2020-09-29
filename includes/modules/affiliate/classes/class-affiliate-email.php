<?php
namespace POC\Foundation\Modules\Affiliate\Classes;

use POC\Foundation\Classes\Option;

class Affiliate_Email
{
    public static function email_notification_error( $order_id )
    {
        wp_mail(
	        Option::get( 'email_notification' ),
	        __( "Pay the reward failed for order - $order_id", 'poc-foundation' ),
	        __( 'Pay the reward failed. Please check review amount or network error !', 'poc-foundation' )
        );
    }
}