<?php

namespace POC\Foundation;

use POC\Foundation\Admin\ElementorPro\POC_Foundation_Elementor_Pro;

class POC_Foundation_Callback
{
	public function __construct()
	{
		$this->add_hooks();
	}

	public function add_hooks()
	{
		add_action( 'init', array( $this, 'handle' ) );
	}

	public function handle()
	{
		$params = array(
			'page' => '',
			'action' => '',
			'state' => '',
		);

		$params = array_merge( $params, $_GET );

		$state = get_transient( 'poc-foundation-state' );

		if ( $params['page'] != 'poc-foundation' || empty( $params['action'] ) || ! $state || $state != $params['state'] ) {
			return;
		}

		$action = $params['action'];

		return $this->{"action_$action"}();
	}

	public function action_install_elementor_pro()
	{
		if ( ! isset( $_GET['download_link'] ) || empty( $_GET['download_link'] ) ) {
			return;
		}

		$this->get_elementor_pro_handler()->install( $_GET['download_link'] );

		return;
	}

	public function action_activate_elementor_pro()
	{
		$params = array_merge( array(
			'nonce' => '',
			'state' => '',
			'code' => '',
		), $_GET );

		if ( empty( $params['nonce'] ) || empty( $params['state'] ) || empty( $params['code'] ) ) {
			return;
		}

		$this->get_elementor_pro_handler()->activate_license( $params );

		return;
	}

	protected function get_elementor_pro_handler()
	{
		return new POC_Foundation_Elementor_Pro( $this->get_plugin_manager() );
	}

	protected function get_plugin_manager()
	{
		return new POC_Foundation_Plugin_Manager();
	}
}