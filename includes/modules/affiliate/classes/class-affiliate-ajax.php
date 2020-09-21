<?php

namespace POC\Foundation\Modules\Affiliate\Classes;

use kornrunner\Ethereum\Address;
use POC\Foundation\Classes\Option;
use POC\Foundation\Contracts\Hook;
use POC\Foundation\Modules\Affiliate\Hooks\Affiliate_Order_Actions;
use POC\Foundation\Modules\Affiliate\Classes\Explorer_API;
use POC\Foundation\Modules\Affiliate\Classes\Affiliate_Email;

class Affiliate_AJAX implements Hook
{

    protected $message_error = 'Pay the reward failed. please check amount or network.';
    protected $message_success = 'Pay the reward success';

    public function hooks()
    {
        add_action( 'wp_ajax_check_transaction_hash', array( $this, 'check_transaction_hash' ) );
        add_action( 'wp_ajax_nopriv_check_transaction_hash', array( $this, 'check_transaction_hash' ) );

        add_action( 'wp_ajax_take_private_key', array( $this, 'take_private_key' ) );
        add_action( 'wp_ajax_nopriv_take_private_key', array( $this, 'take_private_key' ) );
    }

    public function check_transaction_hash()
    {
        if( !isset( $_POST['order_id'] ) ){
            return;
        }
        $order_id = $_POST['order_id'];

        $transaction_hash = get_post_meta( $order_id, 'transaction_hash', true );

        $status = get_post_meta( $order_id, 'reward_status', true );

        if( empty( $transaction_hash ) ){
            $new_status = 'error';
        }else{
            $new_status = $this->check_status_transaction_hash( $transaction_hash );
        }

        if ( $status != $new_status ) {
            update_post_meta( $order_id, 'reward_status', $new_status );
        }

        switch ( $new_status ) {
            case 'error':
                $data_transaction_hash = new Affiliate_Order_Actions();
                $data_transaction_hash->make_transaction_hash( $order_id );
                // send email
                    $send_mail = new Affiliate_Email();
                    $send_mail->email_notification_error( $order_id );
                $message = $this->message_error;
                break;
            case 'success':
                $message = $this->message_success;
                break;
        }

        wp_send_json_success( array( 'reward_status' => $new_status, 'message' => $message ) );

    }

    protected function check_status_transaction_hash( $transaction_hash )
    {

        $result = ( new Explorer_API() )->check_status_transaction( $transaction_hash );

        if( $result === "1" ) {
            return 'error';
        }

        return 'success';
    }

    public function take_private_key(){
        $generate_key = new Address();
        $generate_key->get();
        $private_key = $generate_key->getPrivateKey();
        wp_send_json_success( array( 'private_key' => $private_key ) );
    }
}