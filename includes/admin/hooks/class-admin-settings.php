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
        include_once ABSPATH . 'wp-admin/includes/template.php';

		$data = [];
		foreach ($_POST['poc_foundation']['ref_rates'] as $item){
            $data[] = $item;
        }
        $_POST['poc_foundation']['ref_rates'] = $data;

        $count_arr_ref_rate = count($_POST['poc_foundation']['ref_rates']);
        if($count_arr_ref_rate > 10){
            add_settings_error( 'poc_foundation_notices', '', __( 'Total floor : not more than 10 floor', 'poc-foundation' ), 'error'  );
            return;
        }

        $total = 0;
        foreach ( $_POST['poc_foundation']['ref_rates'] as $item ) {
            $total = (int)$item + $total;
        }

        if ( $total > 100) {
            // message over 100%
            add_settings_error( 'poc_foundation_notices', '', __( 'Total referral rate : not more than 100', 'poc-foundation' ), 'error'  );
            return;
        }
        // private key 0x...
        $_POST['poc_foundation']['private_key'] = '0x'.$_POST['poc_foundation']['private_key'];

        if ( count( get_settings_errors( 'poc_foundation_settings_errors' ) ) > 0 ) {
            return;
        }

		do_action( 'poc_foundation_validate_posted_data', $_POST['poc_foundation'] );

		$settings = unserialize( get_option( self::SETTING_KEY ) );

		if ( ! is_array( $settings ) ) {
			$settings = array();
		}

		$new_settings = array_merge( $settings, $_POST['poc_foundation'] );

		update_option( self::SETTING_KEY, serialize( $new_settings ) );

		add_settings_error( 'poc_foundation_notices', '', __( 'Settings saved.', 'poc-foundation' ), 'success' );
	}
}