<?php

namespace POC\Foundation\Modules\Affiliate\Hooks;

use POC\Foundation\Classes\Option;
use POC\Foundation\Contracts\Hook;
use POC\Foundation\Modules\Affiliate\Pages\Pay_The_Reward_Page;
use POC\Foundation\Modules\Affiliate\Hooks\Affiliate_Order_Actions;

class Affiliate_Admin implements Hook
{
	public function hooks()
	{
		add_filter( 'poc_foundation_admin_submenu_pages', array( $this, 'submenu_pages' ) );

		add_filter( 'poc_foundation_admin_settings_tabs', array( $this, 'settings_tab' ) );

        add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts' ) );

        add_action( "wp_ajax_take_data_user", array( $this, 'so_wp_ajax_function' ) );

        add_action( "wp_ajax_nopriv_take_data_user", array( $this, 'so_wp_ajax_function' ) );


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
            wp_enqueue_script( 'setting_poc_foundation_validate', POC_FOUNDATION_PLUGIN_URL . 'includes/admin/assets/js/jquery.validate.min.js', array( 'jquery' ) );

            wp_enqueue_script( 'setting_poc_foundation', POC_FOUNDATION_PLUGIN_URL . 'includes/modules/affiliate/assets/js/setting_poc_foundation.js', array( 'jquery' ) );

            wp_localize_script( 'setting_poc_foundation', 'setting_poc_foundation_data',
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


        $new_status = $this->check_status_transaction_hash( $transaction_hash );


        if ( $status != $new_status ) {
            update_post_meta( $order_id, 'reward_status', $new_status );
        }

        switch ( $new_status ) {
            case 'error':
                delete_post_meta( $order_id, 'transaction_hash', $transaction_hash );
                $data_transaction_hash = new Affiliate_Order_Actions();
                $data_transaction_hash->make_transaction_hash( $order_id );
                // Gui email
                $message = 'fail. email send';
                break;
            case 'success':
                // Gui email
                $message = 'success. email send';
                break;
        }

        wp_send_json_success( array( 'reward_status' => $new_status, 'message' => $message ) );

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