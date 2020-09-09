<?php

namespace POC\Foundation\License;

class License
{
	public function check_license( $license_key = '', $refresh = false )
	{
		$license_data = $this->get_license_data( $license_key, '', $refresh );

		if ( is_null( $license_data ) || ! is_array( $license_data ) ) {
			return false;
		}

		if ( $license_data['status'] === 'Active' ) {
			return true;
		}

		return false;
	}

	public function get_license_data( $license_key = '', $local_key = '', $refresh = false )
	{
		if ( $refresh ) {
			delete_option( 'poc_foundation_license_key' );
			delete_option( 'poc_foundation_license_local_key' );
			delete_transient( 'poc_foundation_license_data' );
		}

		$license_data = null;

		if ( empty( $license_key ) ) {
			$license_data = get_transient( 'poc_foundation_license_data' );
		}

		if ( $license_data ) {
			return $license_data;
		}

		if ( empty( $license_key ) ) {
			$license_key = get_option( 'poc_foundation_license_key', '' );
		}

		if ( empty( $local_key ) ) {
			$local_key = get_option( 'poc_foundation_license_local_key', '' );
		}

		if ( empty( $license_key ) ) {
			return null;
		}

		$license_data = $this->get_license_server()->check( $license_key, $local_key );

		if ( $refresh || ( isset( $license_data['status'] ) && $license_data['status'] != 'Active' ) ) {
			set_transient( 'poc_foundation_license_data', $license_data, 12 * HOUR_IN_SECONDS );
			return $license_data;
		}

		update_option( 'poc_foundation_license_key', $license_key );

		if ( isset( $license_data['local_key'] ) ) {
			update_option( 'poc_foundation_license_local_key', $license_data['local_key'] );
		}

		set_transient( 'poc_foundation_license_data', $license_data, 12 * HOUR_IN_SECONDS );

		return $license_data;
	}

	protected function get_license_server()
	{
		return new POC_Foundation_License_Server();
	}
}