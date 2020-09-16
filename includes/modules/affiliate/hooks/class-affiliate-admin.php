<?php

namespace POC\Foundation\Modules\Affiliate\Hooks;

use POC\Foundation\Classes\Option;
use POC\Foundation\Contracts\Hook;
use POC\Foundation\Modules\Affiliate\Pages\Pay_The_Reward_Page;
use POC\Foundation\Modules\Affiliate\Hooks\Affiliate_Order_Actions;
use kornrunner\Ethereum\Address;

class Affiliate_Admin implements Hook
{
	public function hooks()
	{
		add_filter( 'poc_foundation_admin_submenu_pages', array( $this, 'submenu_pages' ) );

		add_filter( 'poc_foundation_admin_settings_tabs', array( $this, 'settings_tab' ) );

        add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts' ) );

        add_action( "wp_ajax_take_data_user", array( $this, 'so_wp_ajax_function' ) );

        add_action( "wp_ajax_nopriv_take_data_user", array( $this, 'so_wp_ajax_function' ) );

        add_action( "wp_ajax_take_private_key", array( $this, 'create_ajax_function' ) );

        add_action( "wp_ajax_nopriv_take_private_key", array( $this, 'create_ajax_function' ) );

	}

	public function settings_tab( $tabs )
	{
		$tabs[] = array(
			'id' => 'affiliate',
			'label' => 'Affiliate',
			'callback' => array( $this, 'settings_tab_callback' )
		);

		return $tabs;
	}

	public function settings_tab_callback()
	{
		$option = new Option();

		include_once dirname( dirname( __FILE__ ) ) . '/views/html-affiliate-settings-tab.php';
	}

	public function submenu_pages( $submenu_pages )
	{
		$submenu_pages[] = array(
			'page_title' => __( 'Leads', 'poc-foundation' ),
			'menu_title' => __( 'Leads', 'poc-foundation' ),
			'menu_slug' => 'edit.php?post_type=poc_foundation_lead',
		);

		$submenu_pages[] = array(
            'page_title' => __( 'Pay The Reward', 'poc-foundation' ),
            'menu_title' => __( 'Pay The Reward', 'poc-foundation' ),
            'menu_slug' => 'poc-foundation-reward',
            'callback' => array( Pay_The_Reward_Page::class, 'render' )
        );

		return $submenu_pages;
	}

    public function enqueue_scripts()
    {
        $page = $_GET['page'];
        if ( $page == 'poc-foundation-reward' ) {
            wp_enqueue_script( 'send_token_ajax', POC_FOUNDATION_PLUGIN_URL . 'includes/modules/affiliate/assets/js/pay_the_reward.js', array( 'jquery' ) );

            wp_localize_script( 'send_token_ajax', 'send_token_ajax_data',
                array(
                    'ajax_url' => admin_url( 'admin-ajax.php' )
                )
            );
        }

        if ( $page == 'poc-foundation' ) {
            wp_enqueue_script('setting_poc_foundation_chart_jquery', POC_FOUNDATION_PLUGIN_URL . 'includes/modules/affiliate/assets/js/jquery-1.11.1.min.js');

            wp_enqueue_script('setting_poc_foundation_chart_canvas', POC_FOUNDATION_PLUGIN_URL . 'includes/modules/affiliate/assets/js/canvasjs.min.js',array('setting_poc_foundation_chart_jquery'));

            wp_enqueue_script('setting_poc_foundation_chart_referral_rate', POC_FOUNDATION_PLUGIN_URL . 'includes/modules/affiliate/assets/js/chart_referral_sate.js',array('setting_poc_foundation_chart_canvas'));

            wp_enqueue_script( 'setting_poc_foundation_validate', POC_FOUNDATION_PLUGIN_URL . 'includes/admin/assets/js/jquery.validate.min.js', array( 'jquery' ) );

            wp_enqueue_script( 'setting_poc_foundation', POC_FOUNDATION_PLUGIN_URL . 'includes/modules/affiliate/assets/js/setting_poc_foundation.js', array( 'jquery' ) );

            wp_localize_script( 'setting_poc_foundation', 'create_private_key',
                array(
                    'ajax_url' => admin_url( 'admin-ajax.php' )
                )
            );
        }
    }

    function so_wp_ajax_function()
    {
        $order_id = $_POST['order_id'];

        $transaction_hash = get_post_meta( $order_id, 'transaction_hash', true );

        $status = get_post_meta( $order_id, 'reward_status', true );

        $ref_by = get_post_meta( $order_id, 'ref_by', true );

        if( empty( $transaction_hash ) ){
            $new_status = 'error';
        }else{
            $new_status = $this->check_status_transaction_hash( $transaction_hash );
        }

        if ( $status != $new_status ) {
            update_post_meta( $order_id, 'reward_status', $new_status );
        }

        $option = new Option();
        $email = $option->get( 'email_notification' );
        $message_email = 'Pay the reward failed.
Please check review amount or network error !';
        $subject_email_error = "Pay the reward failed for order - " .$order_id;
        switch ( $new_status ) {
            case 'error':
                $data_transaction_hash = new Affiliate_Order_Actions();
                $data_transaction_hash->make_transaction_hash( $order_id );
                // send email
                wp_mail( $email, $subject_email_error, $message_email );
                $message = 'Pay the reward failed. please check amount or network.';
                break;
            case 'success':
                $message = 'Pay the reward success';
                break;
        }

        wp_send_json_success( array( 'reward_status' => $new_status, 'message' => $message ) );

    }

    function create_ajax_function(){
        $generate_key = new Address();
        $generate_key->get();
        $private_key = $generate_key->getPrivateKey();
        wp_send_json_success( array( 'private_key' => $private_key ) );
    }

    protected function check_status_transaction_hash( $transaction_hash )
    {
        // call api check status transaction hash
        $url = 'https://explorer.nexty.io/api?module=transaction&action=getstatus&txhash='.$transaction_hash;

        $response = wp_remote_get($url);

        $result = (json_decode($response['body'])->result->isError);

        if( $result === "1" ) {
            return 'error';
        }

        return 'success';
    }
}