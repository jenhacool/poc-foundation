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

	/**
	 * Setup Elementor Pro
	 *
	 * @return bool
	 */
	public function setup()
	{
		if ( ! $this->plugin_manager->is_plugin_installed( 'elementor-pro' ) ) {
			$install = $this->install();

			if ( ! $install ) {
				return false;
			}
		}

		$this->includes();

		if ( $this->plugin_manager->is_plugin_updateable( 'elementor-pro' ) ) {
			$update = $this->plugin_manager->upgrade_plugin( 'elementor-pro' );

			if ( ! $update ) {
				return false;
			}
		}

		if ( ! $this->plugin_manager->is_plugin_active( 'elementor-pro' ) ) {
			$activate = $this->plugin_manager->activate_plugin( 'elementor-pro' );

			if ( ! is_null( $activate ) ) {
				return false;
			}
		}

		if ( ! $this->is_license_valid() ) {
			$activate_license = $this->activate_license();

			if ( ! $activate_license ) {
				return false;
			}
		}

		return true;
	}

	/**
	 * Install plugin
	 *
	 * @return array|bool|\WP_Error
	 */
	public function install()
	{
		return $this->plugin_manager->get_plugin_upgrader()->install(
			$this->get_download_link()
		);
	}

	/**
	 * Get plugin download link
	 *
	 * @return mixed|string
	 */
	public function get_download_link()
	{
		$response = $this->call_api( 'get_download_link' );

		if ( is_null( $response ) ) {
			return '';
		}

		return $response['download_link'];
	}

	/**
	 * Activate plugin license
	 *
	 * @return bool
	 */
	public function activate_license()
	{
		$license_key = $this->get_license_key();

		if ( empty( $license_key ) ) {
			return false;
		}

		$data = \ElementorPro\License\API::activate_license( $license_key );

		if ( is_wp_error( $data ) ) {
			return false;
		}

		\ElementorPro\License\Admin::set_license_key( $license_key );
		\ElementorPro\License\API::set_license_data( $data );

		return true;
	}

	/**
	 * Get license key
	 *
	 * @return mixed|string
	 */
	public function get_license_key()
	{
		$response = $this->call_api( 'get_license_key' );

		if ( is_null( $response ) ) {
			return '';
		}

		return $response['license_key'];
	}

	/**
	 * Check if current Elementor Pro plugin is valid or not
	 *
	 * @return bool
	 */
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

	/**
	 * Includes some Elementor Pro files
	 */
	protected function includes()
	{
		include_once dirname( plugin_dir_path( POC_FOUNDATION_PLUGIN_FILE ) ) . '/elementor/core/common/modules/connect/apps/base-app.php';
		include_once dirname( plugin_dir_path( POC_FOUNDATION_PLUGIN_FILE ) ) . '/elementor/core/common/modules/connect/apps/common-app.php';
		include_once dirname( plugin_dir_path( POC_FOUNDATION_PLUGIN_FILE ) ) . '/elementor-pro/core/connect/apps/activate.php';
		include_once dirname( plugin_dir_path( POC_FOUNDATION_PLUGIN_FILE ) ) . '/elementor-pro/elementor-pro.php';
		include_once dirname( plugin_dir_path( POC_FOUNDATION_PLUGIN_FILE ) ) . '/elementor-pro/license/admin.php';
		include_once dirname( plugin_dir_path( POC_FOUNDATION_PLUGIN_FILE ) ) . '/elementor-pro/license/api.php';
	}

	/**
	 * Get license data
	 *
	 * @return array|bool|mixed|\stdClass|\WP_Error
	 */
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

	/**
	 * Send API request
	 *
	 * @param $path
	 * @param array $data
	 *
	 * @return mixed|null
	 */
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

	/**
	 * Parse API response
	 *
	 * @param $response
	 *
	 * @return mixed|null
	 */
	protected function parse_response( $response )
	{
		if ( is_wp_error( $response ) ) {
			return null;
		}

		$response = json_decode( $response['body'], true );

		if( ! $response ) {
			return null;
		}

		return $response;
	}
}