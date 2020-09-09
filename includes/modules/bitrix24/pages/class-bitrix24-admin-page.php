<?php

namespace POC\Foundation\Modules\Bitrix24\Pages;

use POC\Foundation\Modules\Bitrix24\Classes\Bitrix24_Data;

class Bitrix24_Admin_Page
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

		include_once dirname( __FILE__ ) . '/views/html-settings-page.php';
	}
}