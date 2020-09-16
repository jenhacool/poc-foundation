<?php

namespace POC\Foundation\Classes;

class Helper
{
	public static function sanitize_phone_number( $phone_number )
	{
		return substr( preg_replace( '/[^\d+]/', '', $phone_number ), -9 );
	}
}