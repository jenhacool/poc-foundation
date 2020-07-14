<?php

namespace POC\Foundation;

class POC_Foundation_License
{
	public function check_license()
	{
		$license_data = $this->get_license_data();

		if ( $license_data['status'] === 'Active' ) {
			return true;
		}

		return false;
	}

	protected function get_license_data()
	{
		$license_data = get_transient( 'poc_foundation_license_data' );

		return $license_data;
	}

	protected function get_license_server()
	{

	}
}