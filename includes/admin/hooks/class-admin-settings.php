<?php

namespace POC\Foundation\Admin\Hooks;

use POC\Foundation\Contracts\Hook;

class Admin_Settings implements Hook
{
	const SETTING_KEY = 'poc_foundation';

	public function hooks()
	{
		add_action( 'wp_loaded', array( $this, 'save_settings' ) );
	}

	public function save_settings()
	{
		if ( ! isset( $_POST['poc_foundation'] ) || empty( $_POST['poc_foundation'] ) ) {
			return;
		}

		$settings = unserialize( get_option( self::SETTING_KEY ) );

		if ( ! is_array( $settings ) ) {
			$settings = array();
		}

		$new_settings = array_merge( $settings, $_POST['poc_foundation'] );

		update_option( self::SETTING_KEY, serialize( $new_settings ) );
	}
}