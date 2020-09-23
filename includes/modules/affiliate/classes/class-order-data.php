<?php

namespace POC\Foundation\Modules\Affiliate\Classes;

use POC\Foundation\Classes\Option;

class Order_Data
{
	const DEFAULT_REVENUE_SHARE = 60;

	protected $order_id;

	public function get_revenue_share_total()
	{
		$revenue_share_total = 0;

		foreach ( $this->get_order()->get_items() as $item ) {
			$revenue_share_percent = (int) $item->get_product()->get_meta( 'poc_foundation_revenue_share' );

			if ( empty( $revenue_share_percent ) ) {
				$revenue_share_percent = (int) $this->get_default_revenue_share();
			}

			$revenue_share = ( $item->get_total() ) * ( $revenue_share_percent / 100 );

			$revenue_share_total += $revenue_share;
		}

		return round( $revenue_share_total / $this->get_poc_price(), 6 );
	}

	public function get_ref_by_string()
	{
		$ref_by = get_post_meta( $this->get_order_id(), 'ref_by', true );

		$ref_by_subid = get_post_meta( $this->get_order_id(), 'ref_by_subid', true );

		if ( ! $ref_by ) {
			$ref_by = 'null';
		}

		if ( $ref_by_subid ) {
			$ref_by = $ref_by . '::' . urlencode( $ref_by_subid );
		}

		return $ref_by;
	}

	public function get_order()
	{
		return wc_get_order( $this->order_id );
	}

	public function get_order_id()
	{
		return $this->get_order()->get_id();
	}

	public function set_order_id( $order_id )
	{
		$this->order_id = $order_id;
	}

	protected function get_poc_price()
	{
		$poc_api = new POC_API();

		$currency = strtolower( get_woocommerce_currency() );

		$price = $poc_api->send_request( "getprice/poc/$currency" );
		if ( $price && is_numeric( $price['data']['price'] ) && $price['data']['price'] > 0 ) {
			return $price['data']['price'];
		} else {
			sleep(1);
			$price = $poc_api->send_request( "getprice/poc/$currency" );
			if ( $price && is_numeric( $price['data']['price'] ) && $price['data']['price'] > 0 ) {
				return $price['data']['price'];
			} else {
				// Try again after 1s
				sleep(1);
				$price = $poc_api->send_request( "getprice/poc/$currency" );
				if ( $price && is_numeric( $price['data']['price'] ) && $price['data']['price'] > 0)  {
					return $price['data']['price'];
				} else {
					// Try again after 1s
					sleep(1);
					return false;
				}
			}
		}
	}

	protected function get_default_revenue_share()
	{
		$default_revenue_share = Option::get( 'default_revenue_share' );

		if ( empty( $default_revenue_share ) ) {
			return self::DEFAULT_REVENUE_SHARE;
		}

		return $default_revenue_share;
	}
}