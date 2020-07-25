<?php

namespace POC\Foundation;

class POC_Foundation_AJAX
{
	public function __construct()
	{
		add_action( 'wp_ajax_poc_foundation_check_license_key', array( $this, 'check_license_key' ) );
		add_action( 'wp_ajax_nopriv_poc_foundation_check_license_key', array( $this, 'check_license_key' ) );

		add_action( 'wp_ajax_poc_foundation_setup_plugin', array( $this, 'setup_plugin' ) );
		add_action( 'wp_ajax_nopriv_poc_foundation_setup_plugin', array( $this, 'setup_plugin' ) );

		add_action( 'wp_ajax_poc_foundation_clear_update_cache', array( $this, 'clear_update_cache' ) );
		add_action( 'wp_ajax_nopriv_poc_foundation_clear_update_cache', array( $this, 'clear_update_cache' ) );

		add_action( 'wp_ajax_poc_foundation_save_config', array( $this, 'save_config' ) );
		add_action( 'wp_ajax_nopriv_poc_foundation_save_config', array( $this, 'save_config' ) );
	}

	public function check_license_key()
	{
		if ( ! isset( $_POST['license_key'] ) || empty( $_POST['license_key'] ) ) {
			return $this->error_response();
		}

		$license_data = $this->get_license_server()->check( $_POST['license_key'] );

		if ( $license_data['status'] === 'Active' ) {
			return $this->success_response( array(
				'is_valid' => true
			) );
		}

		return $this->success_response( array(
			'is_valid' => false
		) );
	}

	public function setup_plugin()
	{
		if ( ! isset( $_POST['slug'] ) || empty( $_POST['slug'] ) ) {
			return $this->error_response();
		}

		$setup = $this->get_plugin_manager()->setup_plugin( $_POST['slug'] );

		if ( ! $setup ) {
			return $this->error_response();
		}

		if ( $setup === 'processing' ) {
			return $this->success_response( array( 'status' => 'processing' ) );
		}

		return $this->success_response( array( 'status' => 'done' ) );
	}

	public function clear_update_cache()
	{
		wp_clean_plugins_cache( true );

		return $this->success_response();
	}

	public function save_config()
	{
		if ( ! isset( $_POST['poc_foundation_config'] ) || empty( $_POST['poc_foundation_config'] ) ) {
			return $this->error_response();
		}

		$data = array();
		parse_str( $_POST['poc_foundation_config'], $data );

		var_dump($data);

		foreach ( $data as $key => $config ) {
			update_option( $key, $config );
		}

		return $this->success_response();
	}

	protected function get_license_server()
	{
		return new POC_Foundation_License_Server();
	}

	protected function get_plugin_manager()
	{
		return new POC_Foundation_Plugin_Manager();
	}

	protected function response( $data = array() )
	{
		return wp_send_json( $data );
	}

	protected function success_response( $data = array() )
	{
		return wp_send_json_success( $data );
	}

	protected function error_response( $data = array() )
	{
		return wp_send_json_error( $data );
	}
}