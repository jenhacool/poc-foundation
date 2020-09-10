<?php

namespace POC\Foundation\Admin\Hooks;

use POC\Foundation\Admin\Pages\Getting_Started_Page;
use POC\Foundation\Admin\Pages\License_Page;
use POC\Foundation\Admin\Pages\Settings_Page;
use POC\Foundation\Contracts\Hook;

class Admin_Menu implements Hook
{
	const MENU_PAGE_SLUG = 'poc-foundation';

	const DEFAULT_CAPABILITY = 'manage_options';

	public function hooks()
	{
		add_action( 'admin_menu', array( $this, 'init_admin_menu' ) );
	}

	public function init_admin_menu()
	{
		$this->add_admin_menu();

		$this->change_admin_menu_name();
	}

	protected function add_admin_menu()
	{
		add_menu_page(
			__( 'POC Foundation', 'poc-foundation' ),
			__( 'POC Foundation', 'poc-foundation' ),
			self::DEFAULT_CAPABILITY,
			self::MENU_PAGE_SLUG,
			array( Settings_Page::class, 'render' )
		);

		foreach ( $this->get_submenu_pages() as $submenu_page ) {
			add_submenu_page(
				self::MENU_PAGE_SLUG,
				$submenu_page['page_title'],
				$submenu_page['menu_title'],
				isset( $submenu_page['capability'] ) ? $submenu_page['capability'] : self::DEFAULT_CAPABILITY,
				$submenu_page['menu_slug'],
				isset( $submenu_page['callback'] ) ? $submenu_page['callback'] : ''
			);
		}
	}

	protected function get_submenu_pages()
	{
		$submenu_pages = array(
			array(
				'page_title' => __( 'License', 'poc-foundation' ),
				'menu_title' => __( 'License', 'poc-foundation' ),
				'menu_slug' => 'poc-foundation-license',
				'callback' => array( License_Page::class, 'render' )
			)
		);

		return apply_filters( 'poc_foundation_admin_submenu_pages', $submenu_pages );
	}

	protected function change_admin_menu_name()
	{
		global $submenu;

		if ( isset( $submenu['poc-foundation'] ) ) {
			$submenu['poc-foundation'][0][0] = __( 'Settings', 'poc-foundation' );
		}
	}
}