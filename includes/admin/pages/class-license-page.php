<?php

namespace POC\Foundation\Admin\Pages;

use POC\Foundation\License\License;

class License_Page implements Admin_Page
{
	public static function render()
	{
		$license_data = ( new License() )->get_license_data();

		include_once dirname( __FILE__ ) . '/views/html-license-page.php';
	}
}