<?php

namespace POC\Foundation\Admin\ElementorPro;

include_once dirname( plugin_dir_path( POC_FOUNDATION_PLUGIN_FILE ) ) . '/elementor-pro/core/connect/apps/activate.php';
include_once dirname( plugin_dir_path( POC_FOUNDATION_PLUGIN_FILE ) ) . '/elementor-pro/license/api.php';
include_once dirname( plugin_dir_path( POC_FOUNDATION_PLUGIN_FILE ) ) . '/elementor-pro/license/admin.php';

class POC_Foundation_Elementor_Pro_App extends \ElementorPro\Core\Connect\Apps\Activate
{
	public function get_remote_authorize_url()
	{
		$this->set_client_id();
		$this->set_request_state();

		return parent::get_remote_authorize_url();
	}

	public function activate( $params )
	{
		$token = $this->get_token( $params );

		if ( is_null( $token ) ) {
			return false;
		}

		$this->delete( 'state' );

		$this->set( (array) $token );

		$license = $this->request( 'get_connected_license' );

		if ( is_wp_error( $license ) || empty( $license ) ) {
			return false;
		}

		$license_key = trim( $license->key );

		if ( empty( $license_key ) ) {
			return false;
		}

		$data = \ElementorPro\License\API::activate_license( $license_key );

		if ( is_wp_error( $data ) || \ElementorPro\License\API::STATUS_VALID !== $data['license'] ) {
			return false;
		}

		\ElementorPro\License\Admin::set_license_key( $license_key );

		\ElementorPro\License\API::set_license_data( $data );

		$this->request( 'set_site_owner' );

		return true;
	}

	protected function get_token( $params )
	{
		$response = $this->request( 'get_token', [
			'grant_type' => 'authorization_code',
			'code' => $params['code'],
			'redirect_uri' => rawurlencode( $this->get_admin_url( 'get_token' ) ),
			'client_id' => $this->get( 'client_id' ),
		] );

		if ( is_wp_error( $response ) ) {
			return null;
		}

		return $response;
	}
}