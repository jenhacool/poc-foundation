<?php

namespace POC\Foundation\Modules\Affiliate\Utilities;

use POC\Foundation\Classes\POC_API;

trait Check_Coupon
{
	public function is_coupon_valid( $coupon_code )
	{
		$coupon_code = strtolower( $coupon_code );

		$is_valid = get_transient( 'poc_foundation_coupon_' . $coupon_code . '_is_valid' );

		if ( $is_valid ) {
			return true;
		}

		$api = new POC_API();

		$data = $api->send_request( "user/$coupon_code" );

		if( is_null( $data ) || $data['message'] != 'success' ) {
			return false;
		}

		set_transient( 'poc_foundation_coupon_' . $coupon_code . '_is_valid', true, HOUR_IN_SECONDS );

		return true;
	}
}