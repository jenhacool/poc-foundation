<?php

namespace POC\Foundation\Admin\Hooks;

use POC\Foundation\Contracts\Hook;

class Admin_Settings implements Hook
{
	const SETTING_PREFIX = 'poc_foundation';

	const SETTING_KEY = 'poc_foundation';

	const SETTING_GROUP_NAME = 'poc_foundation_settings';

	public function hooks()
	{
		add_action( 'admin_init', array( $this, 'save_settings' ) );
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

	public function register_settings()
	{
		foreach ( $this->get_settings() as $setting ) {
			register_setting(
				self::SETTING_GROUP_NAME,
				self::SETTING_PREFIX . '_' . $setting
			);
		}
	}

	protected function get_settings()
	{
		$default_settings = array();

		return apply_filters( 'poc_foundation_admin_settings', $default_settings );
		return array(
			'api_key',
			'uid_prefix',
			'redirect_page',
			'fanpage_id',
			'fanpage_url',
			'chatbot_backlink',
			'allowed_iframe_domain',
			'default_discount',
			'default_revenue_share',
		);
	}
}