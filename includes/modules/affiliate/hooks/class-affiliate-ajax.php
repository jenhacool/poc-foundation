<?php

namespace POC\Foundation\Modules\Affiliate\Hooks;

use kornrunner\Ethereum\Address;
use POC\Foundation\Classes\Option;
use POC\Foundation\Contracts\Hook;
use POC\Foundation\Modules\Affiliate\Classes\Transaction;
use POC\Foundation\Modules\Affiliate\Classes\Affiliate_Email;

class Affiliate_AJAX implements Hook
{
    public function hooks()
    {
        add_action( 'wp_ajax_check_transaction_hash', array( $this, 'check_transaction_hash' ) );
        add_action( 'wp_ajax_nopriv_check_transaction_hash', array( $this, 'check_transaction_hash' ) );

        add_action( 'wp_ajax_take_private_key', array( $this, 'take_private_key' ) );
        add_action( 'wp_ajax_nopriv_take_private_key', array( $this, 'take_private_key' ) );
    }

    public function check_transaction_hash()
    {
        if ( ! isset( $_POST['order_id'] ) ) {
            wp_send_json_error();
        }

        $order_id = $_POST['order_id'];

        $transaction_hash = get_post_meta( $order_id, 'transaction_hash', true );

        $status = get_post_meta( $order_id, 'reward_status', true );

        if ( ! $transaction_hash || empty( $transaction_hash ) ) {
            $new_status = 'error';
        } else {
            $new_status = Transaction::check_status( $transaction_hash );
        }

        if ( $status != $new_status ) {
            update_post_meta( $order_id, 'reward_status', $new_status );
        }

        if ( $new_status === 'error' ) {
	        Affiliate_Email::email_notification_error( $order_id );

	        wp_send_json_success( array(
	        	'reward_status' => $new_status,
		        'message' => __( 'Pay the reward failed. Please check amount or network.', 'poc-foundation' )
	        ) );
        }

	    wp_send_json_success( array(
		    'reward_status' => $new_status,
		    'message' => __( 'Pay the reward success', 'poc-foundation' )
	    ) );
    }

    public function take_private_key(){
        $generate_key = new Address();

        wp_send_json_success( array(
        	'private_key' => $generate_key->getPrivateKey()
        ) );
    }
}