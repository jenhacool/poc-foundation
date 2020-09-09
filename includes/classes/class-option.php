<?php

namespace POC\Foundation\Classes;

class Option
{
	const OPITON_KEY = 'poc_foundation';

	public static function get( $key )
	{
		$settings = unserialize( get_option( self::OPITON_KEY ) );

		if ( empty( $settings ) || ! isset( $settings[$key] ) ) {
			return '';
		}

		return $settings[$key];
	}
}
