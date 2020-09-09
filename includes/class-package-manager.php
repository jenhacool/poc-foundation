<?php

namespace POC\Foundation;

class Package_Manager
{
	protected static $packages = array(
		'ajax-queue'   => '\\AJAXQueue\\Core',
	);

	public static function load_packages()
	{
		foreach ( self::$packages as $package_name => $package_class ) {
			if ( ! self::package_exists( $package_name ) ) {
				self::missing_package( $package_name );
				continue;
			}
			require_once POC_FOUNDATION_PLUGIN_DIR . '/packages/' . $package_name . '/' . $package_name . '.php';
			call_user_func( array( $package_class, 'init' ) );
		}
	}

	public static function package_exists( $package ) {
		return file_exists( POC_FOUNDATION_PLUGIN_DIR . '/packages/' . $package );
	}

	protected static function missing_package( $package )
	{
		wp_die('abbab');
	}
}