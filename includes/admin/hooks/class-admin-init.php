<?php

namespace POC\Foundation\Admin\Hooks;

use POC\Foundation\Contracts\Hook;

class Admin_Init implements Hook
{
	public function hooks()
	{
		add_action( 'admin_init', array( $this, 'on_admin_init' ) );
	}

	public function on_admin_init()
	{
		$this->maybe_remove_all_admin_notices();

		$this->admin_redirect();
	}

	public function admin_redirect()
	{
		if ( ! $this->is_new_install() || ! get_transient( 'poc_foundation_activation_redirect' ) ) {
			return;
		}

		return $this->redirect_to_wizard_page();
	}

	public function is_new_install()
	{
		return ! get_option( 'poc_foundation_api_key' ) || ! get_option( 'poc_foundation_uid_prefix' );
	}

	protected function redirect_to_wizard_page()
	{
		delete_transient( 'poc_foundation_activation_redirect' );
		wp_safe_redirect( admin_url( 'admin.php?page=poc-foundation-getting-started' ) );
		exit;
	}

	protected function maybe_remove_all_admin_notices()
	{
		$elementor_pages = [
			'poc-foundation-license',
			'poc-foundation-getting-started'
		];

		if ( empty( $_GET['page'] ) || ! in_array( $_GET['page'], $elementor_pages, true ) ) {
			return;
		}

		remove_all_actions( 'admin_notices' );
	}
}