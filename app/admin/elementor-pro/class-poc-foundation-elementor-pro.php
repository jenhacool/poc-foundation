<?php

namespace POC\Foundation\Admin\ElementorPro;

use POC\Foundation\POC_Foundation_Plugin_Manager;

class POC_Foundation_Elementor_Pro
{
	const API_ENDPOINT = 'http://localhost:3000';

	public $plugin_manager;

	public $app;

	/**
	 * POC_Foundation_Elementor_Pro constructor.
	 *
	 * @param POC_Foundation_Plugin_Manager $plugin_manager
	 */
	public function __construct( $plugin_manager )
	{
		$this->plugin_manager = $plugin_manager;
	}

	public function setup()
	{
		if ( ! $this->plugin_manager->is_plugin_installed( 'elementor-pro' ) ) {
			$waiting = $this->waiting_get_download_link();

			if ( $waiting ) {
				return 'processing';
			}

			return false;
		}

		$this->includes();

		if ( $this->plugin_manager->is_plugin_updateable( 'elementor-pro' ) ) {
			$update = $this->update();

			if ( ! $update ) {
				return false;
			}
		}

		if ( ! $this->is_license_valid() ) {
			$waiting = $this->waiting_get_activate_license_params();

			if ( $waiting ) {
				return 'processing';
			}

			return false;
		}

		return true;
	}

	public function waiting_get_download_link()
	{
		$response = $this->call_api( 'get_download_link', array(
			'callback' => $this->get_install_callback()
		) );

		if ( is_null( $response ) ) {
			return false;
		}

		if ( isset( $response['status'] ) && $response['status'] === 'processing' ) {
			return true;
		}

		return false;
	}

	public function install( $download_link )
	{
		return $this->plugin_manager->get_plugin_upgrader()->install( $download_link );
	}

	public function update()
	{

	}

	public function waiting_get_activate_license_params()
	{
		$remote_authorize_url = $this->get_remote_authorize_url();

		if ( empty( $remote_authorize_url ) ) {
			return false;
		}

		$response = $this->call_api(
			'activate_elementor_pro',
			array(
				'url' => $remote_authorize_url,
				'callback' => $this->get_activate_license_callback()
			)
		);

		if ( is_null( $response ) ) {
			return false;
		}

		if ( ! isset( $response['status'] ) || $response['status'] != 'processing' ) {
			return false;
		}

		return true;
	}

	public function activate_license( $params )
	{
		$activate = $this->get_elementor_pro_app()->activate( $params );

		if ( ! $activate ) {
			return false;
		}

		return true;
	}

	public function is_license_valid()
	{
		$license_key = trim( get_option( 'elementor_pro_license_key' ) );

		if ( empty( $license_key ) ) {
			return false;
		}

		$license_data = $this->get_license_data();

		if ( empty( $license_data['license'] ) ) {
			return false;
		}

		if ( ! $this->is_license_active() ) {
			return false;
		}

		return true;
	}

	protected function get_install_callback()
	{
		return $this->get_callback_url( 'install' );
	}

	protected function get_activate_license_callback()
	{
		return $this->get_callback_url( 'activate' );
	}

	protected function get_callback_url( $action )
	{
		$url = home_url() . '?page=poc-foundation&action=' . $action . '_elementor_pro';

		return add_query_arg( 'state', $this->get_request_state(), $url );
	}

	protected function get_remote_authorize_url()
	{
		return $this->get_elementor_pro_app()->get_remote_authorize_url();
	}

	protected function includes()
	{
		include_once dirname( plugin_dir_path( POC_FOUNDATION_PLUGIN_FILE ) ) . '/elementor/core/common/modules/connect/apps/base-app.php';
		include_once dirname( plugin_dir_path( POC_FOUNDATION_PLUGIN_FILE ) ) . '/elementor/core/common/modules/connect/apps/common-app.php';
		include_once dirname( plugin_dir_path( POC_FOUNDATION_PLUGIN_FILE ) ) . '/elementor-pro/core/connect/apps/activate.php';
		include_once dirname( plugin_dir_path( POC_FOUNDATION_PLUGIN_FILE ) ) . '/elementor-pro/elementor-pro.php';
		include_once dirname( plugin_dir_path( POC_FOUNDATION_PLUGIN_FILE ) ) . '/elementor-pro/license/admin.php';
		include_once dirname( plugin_dir_path( POC_FOUNDATION_PLUGIN_FILE ) ) . '/elementor-pro/license/api.php';
	}

	protected function get_license_data()
	{
		if ( ! class_exists( \ElementorPro\License\API::class ) ) {
			return '';
		}

		return \ElementorPro\License\API::get_license_data();
	}

	protected function is_license_active()
	{
		if ( ! class_exists( \ElementorPro\License\API::class ) ) {
			return false;
		}

		return \ElementorPro\License\API::is_license_active();
	}

	protected function get_elementor_pro_activate_app()
	{
		return new \ElementorPro\Core\Connect\Apps\Activate();
	}

	protected function get_elementor_pro_app()
	{
		return new POC_Foundation_Elementor_Pro_App();
	}

	protected function call_api( $path, $data = [] )
	{
		$url = rtrim( self::API_ENDPOINT, '/' ) . '/' . $path;

		return $this->parse_response( wp_remote_post( $url, array(
			'headers' => array(
				'Content-Type' => 'application/json',
			),
			'data_format' => 'body',
			'body' => json_encode( $data )
		) ) );
	}

	protected function parse_response( $response )
	{
		if( is_wp_error( $response ) ) {
			return null;
		}

		$response = json_decode( $response['body'], true );

		if( ! $response ) {
			return null;
		}

		return $response;
	}

	protected function get_request_state()
	{
		$state = get_transient( 'poc-foundation-state' );

		if ( ! $state ) {
			$state = wp_generate_password( 12, false );
			set_transient( 'poc-foundation-state', $state );
		}

		return $state;
	}
}