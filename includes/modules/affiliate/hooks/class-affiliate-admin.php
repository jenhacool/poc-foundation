<?php

namespace POC\Foundation\Modules\Affiliate\Hooks;

use POC\Foundation\Classes\Option;
use POC\Foundation\Contracts\Hook;
use POC\Foundation\Modules\Affiliate\Pages\Reward_Page;

class Affiliate_Admin implements Hook
{
	public function hooks()
	{
		add_filter( 'poc_foundation_admin_submenu_pages', array( $this, 'submenu_pages' ) );

		add_filter( 'poc_foundation_admin_settings_tabs', array( $this, 'settings_tab' ) );

        add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts' ) );

	}

	public function settings_tab( $tabs )
	{
		$tabs[] = array(
			'id' => 'affiliate',
			'label' => __( 'Affiliate', 'poc-foundation' ),
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
            'callback' => array( Reward_Page::class, 'render' )
        );

		return $submenu_pages;
	}

    public function enqueue_scripts()
    {
        if( !isset( $_GET['page'] ) ){
            return ;
        }

        $page = $_GET['page'];
        if ( $page == 'poc-foundation-reward' ) {
            wp_enqueue_script( 'send_token_ajax', POC_FOUNDATION_PLUGIN_URL . 'includes/modules/affiliate/assets/js/pay_the_reward.js', array( 'jquery' ) );

            wp_localize_script( 'send_token_ajax', 'check_transaction_hash',
                array(
                    'ajax_url' => admin_url( 'admin-ajax.php' )
                )
            );
        }

        if ( $page == 'poc-foundation' ) {
            wp_enqueue_script( 'setting_poc_foundation_chart_jquery', POC_FOUNDATION_PLUGIN_URL . 'includes/modules/affiliate/assets/js/jquery-1.11.1.min.js' );

            wp_enqueue_script( 'setting_poc_foundation_chart_canvas', POC_FOUNDATION_PLUGIN_URL . 'includes/modules/affiliate/assets/js/canvasjs.min.js',array( 'setting_poc_foundation_chart_jquery' ) );

            wp_enqueue_script( 'setting_poc_foundation_chart_referral_rate', POC_FOUNDATION_PLUGIN_URL . 'includes/modules/affiliate/assets/js/chart_referral_sate.js',array( 'setting_poc_foundation_chart_canvas' ) );

            wp_enqueue_script( 'setting_poc_foundation_validate', POC_FOUNDATION_PLUGIN_URL . 'includes/admin/assets/js/jquery.validate.min.js', array( 'jquery' ) );

            wp_enqueue_script( 'setting_poc_foundation', POC_FOUNDATION_PLUGIN_URL . 'includes/modules/affiliate/assets/js/setting_poc_foundation.js', array( 'jquery' ) );

            wp_localize_script( 'setting_poc_foundation', 'create_private_key',
                array(
                    'ajax_url' => admin_url( 'admin-ajax.php' )
                )
            );
        }
    }

}