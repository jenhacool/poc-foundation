<?php

namespace POC\Foundation\Modules\Bitrix24\Hooks;

use POC\Foundation\Classes\Option;
use POC\Foundation\Contracts\Hook;
use POC\Foundation\Modules\Bitrix24\Classes\Bitrix24_Data;
use POC\Foundation\Modules\Bitrix24\Pages\Admin_Page;
use POC\Foundation\Modules\Bitrix24\Pages\Bitrix24_Admin_Page;

class Bitrix24_Admin implements Hook
{
	public function hooks()
	{
		add_filter( 'poc_foundation_admin_settings_tabs', array( $this, 'settings_tab' ) );

		add_action( 'admin_footer', array( $this, 'bitrix24_dialog' ) );

		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
	}

	public function bitrix24_dialog()
	{
	    global $pagenow;

		$post_type = isset( $_GET['post_type'] ) ? $_GET['post_type'] : '';

		if ( ! is_admin() || $pagenow != 'edit.php' || $post_type != 'poc_foundation_lead' ) {
			return;
		}

		$stages = ( new Bitrix24_Data() )->get_stages();

		include_once dirname( dirname( __FILE__ ) ) . '/views/html-bitrix24-dialog.php';
	}

	public function enqueue_scripts()
	{
		wp_register_script( 'poc-foundation-bitrix24', POC_FOUNDATION_PLUGIN_URL . 'includes/modules/bitrix24/assets/js/bitrix24.js', array( 'jquery', 'jquery-ui-core', 'jquery-ui-dialog' ) );

		wp_localize_script(
			'poc-foundation-bitrix24',
			'poc_foundation_bitrix24_data',
			array(
				'ajax_url' => admin_url( 'admin-ajax.php' ),
				'bitrix24_stages' => ( new Bitrix24_Data() )->get_stages(),
			)
		);

		wp_enqueue_script( 'poc-foundation-bitrix24' );

		wp_enqueue_style ( 'wp-jquery-ui-dialog' );
	}

	public function settings_tab( $tabs )
    {
	    $tabs[] = array(
		    'id' => 'bitrix24',
		    'label' => __( 'Bitrix24', 'poc-foundation' ),
		    'callback' => array( $this, 'settings_tab_callback' )
	    );

	    return $tabs;
    }

    public function settings_tab_callback()
    {
        $option = new Option();

	    include_once dirname( dirname( __FILE__ ) ) . '/views/html-bitrix24-settings-tab.php';
    }
}