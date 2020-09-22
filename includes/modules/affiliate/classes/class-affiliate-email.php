<?php
namespace POC\Foundation\Modules\Affiliate\Classes;

use POC\Foundation\Classes\Option;

class Affiliate_Email
{
    public $message_error = 'Pay the reward failed.
Please check review amount or network error !';

    public function email_notification_error( $order_id )
    {
        $email = Option::get( 'email_notification' );
        $subject_email_error = "Pay the reward failed for order - " .$order_id;
        wp_mail( $email, $subject_email_error, $this->message_error );
    }

}