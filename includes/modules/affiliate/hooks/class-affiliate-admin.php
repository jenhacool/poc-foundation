<?php

namespace POC\Foundation\Modules\Affiliate\Hooks;

use POC\Foundation\Classes\Option;
use POC\Foundation\Contracts\Hook;

class Affiliate_Admin implements Hook
{
	public function hooks()
	{
		add_filter( 'poc_foundation_admin_submenu_pages', array( $this, 'submenu_pages' ) );

		add_filter( 'poc_foundation_admin_settings_tabs', array( $this, 'settings_tab' ) );
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

		return $submenu_pages;
	}
}