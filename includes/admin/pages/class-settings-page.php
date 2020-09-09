<?php

namespace POC\Foundation\Admin\Pages;

class Settings_Page implements Admin_Page
{
	public static function render()
	{
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		if ( isset( $_GET['settings-updated'] ) ) {
			add_settings_error( 'poc_foundation_messages', 'poc_foundation_message', __( 'Settings Saved', 'poc_foundation' ), 'updated' );
		}

		settings_errors( 'poc_foundation_messages' );

		$tabs = self::get_tabs();

		include_once dirname( __FILE__ ) . '/views/html-settings-page.php';
	}

	public static function get_tabs()
	{
		$tabs = array();

		return apply_filters( 'poc_foundation_admin_settings_tabs', $tabs );
	}
}