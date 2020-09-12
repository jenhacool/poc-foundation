<?php

namespace POC\Foundation\Admin\Hooks;

use POC\Foundation\Contracts\Hook;

class Admin_Asset implements Hook
{
	public function hooks()
	{
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
	}

	public function enqueue_scripts( $hook_suffix )
	{
		if ( strpos( $hook_suffix, 'poc-foundation_page' ) === false && $hook_suffix != 'toplevel_page_poc-foundation' ) {
			return;
		}

		if ( $hook_suffix === 'poc-foundation_page_poc-foundation-getting-started' ) {
			wp_enqueue_style( 'poc-foundation-setup-wizard', POC_FOUNDATION_PLUGIN_URL . '/assets/css/wizard.css', array(), time() );
			wp_register_script( 'poc-foundation-setup-wizard', POC_FOUNDATION_PLUGIN_URL . '/assets/js/wizard.js', array( 'jquery' ), time() );
			wp_localize_script(
				'poc-foundation-setup-wizard',
				'poc_foundation_params',
				array(
					'ajax_url' => admin_url( 'admin-ajax.php' ),
					'wp_nonce' => wp_create_nonce( 'poc_foundation_admin_nonce' ),
				)
			);
			wp_enqueue_script( 'poc-foundation-setup-wizard' );
		}

		wp_register_script( 'poc-foundation-admin', POC_FOUNDATION_PLUGIN_URL . '/admin/assets/js/admin.js', array( 'jquery' ), time() );
		wp_localize_script(
			'poc-foundation-admin',
			'poc_foundation_params',
			array(
				'ajax_url' => admin_url( 'admin-ajax.php' ),
				'wp_nonce' => wp_create_nonce( 'poc_foundation_admin_nonce' ),
			)
		);
		wp_enqueue_script( 'poc-foundation-admin' );
	}
}