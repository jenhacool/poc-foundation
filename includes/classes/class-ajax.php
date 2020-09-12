<?php

namespace POC\Foundation\Classes;

use POC\Foundation\Admin\Classes\Plugin_Manager;
use POC\Foundation\License\License;

class AJAX
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

		add_action( 'wp_ajax_poc_foundation_check_elementor_pro_status', array( $this, 'check_elementor_pro_status' ) );
		add_action( 'wp_ajax_nopriv_poc_foundation_check_elementor_pro_status', array( $this, 'check_elementor_pro_status' ) );

		add_action( 'wp_ajax_poc_foundation_check_api_key', array( $this, 'check_api_key' ) );
		add_action( 'wp_ajax_nopriv_poc_foundation_check_api_key', array( $this, 'check_api_key' ) );

		add_action( 'wp_ajax_poc_foundation_save_campaign', array( $this, 'save_campaign' ) );
		add_action( 'wp_ajax_nopriv_poc_foundation_save_campaign', array( $this, 'save_campaign' ) );
	}

	public function check_license_key()
	{
		if ( ! isset( $_POST['license_key'] ) || empty( $_POST['license_key'] ) ) {
			return $this->error_response();
		}

		$is_valid = $this->get_license_manager()->check_license( $_POST['license_key'], true );

		return $this->success_response( array(
			'is_valid' => $is_valid,
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

		foreach ( $data as $key => $config ) {
			update_option( $key, $config );
		}

		return $this->success_response();
	}

	public function save_campaign()
	{
		if ( ! isset( $_POST['campaign'] ) || empty( $_POST['campaign'] ) ) {
			return $this->error_response();
		}

		$data = array();
		parse_str( $_POST['campaign'], $data );

		$update = update_option( 'poc_foundation_campaign', serialize( $data['poc_foundation_campaign'] ) );

		if ( ! $update ) {
			return $this->error_response();
		}

		return $this->success_response();
	}

	public function check_elementor_pro_status()
	{
		$plugin_manager = $this->get_plugin_manager();

		if ( $plugin_manager->is_plugin_installed( 'elementor-pro' ) && $plugin_manager->is_plugin_active( 'elementor-pro' ) && $plugin_manager->get_elementor_pro_handler()->is_license_valid() ) {
			return $this->success_response( array( 'status' => 'done' ) );
		}

		return $this->success_response( array( 'status' => 'processing' ) );
	}

	protected function get_license_manager()
	{
		return new License();
	}

	protected function get_plugin_manager()
	{
		return new Plugin_Manager();
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