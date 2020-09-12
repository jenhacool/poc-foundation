<?php

namespace POC\Foundation\Modules\Affiliate\Hooks;

use POC\Foundation\Classes\POC_API;
use POC\Foundation\Contracts\Hook;
use POC\Foundation\Modules\Affiliate\Utilities\Check_Coupon;

class Affiliate_Order_Actions implements Hook
{
	use Check_Coupon;

	public function hooks()
	{
		add_action( 'woocommerce_checkout_order_processed', array( $this, 'add_ref_to_order' ), 10, 3 );

		add_action( 'woocommerce_order_status_completed', array( $this, 'after_order_completed' ) );

		add_action( 'woocommerce_order_status_refunded', array( $this, 'after_order_refunded' ) );

		add_filter( 'woocommerce_coupon_get_discount_amount', array( $this, 'get_discount_amount' ), 10, 6 );
	}

	public function add_ref_to_order( $order_id, $posted_data, $order )
	{
		if ( ! empty( get_post_meta( $order_id, 'ref_by' ) ) ) {
			return;
		}

		$coupon_codes = $order->get_coupon_codes();

		if ( ! empty( $coupon_codes ) && $this->is_coupon_valid( $coupon_codes[0] ) ) {
			$ref_by = $coupon_codes[0];
		} else {
			$ref_by = ! empty( $_COOKIE['ref_by'] ) ? $_COOKIE['ref_by'] : get_user_meta( get_current_user_id(), 'ref_by', true );
		}

		if ( empty( $ref_by ) ) {
			return;
		}

		$this->add_order_meta_data( $order_id, 'ref_by', $ref_by );

		if( ! empty( get_post_meta( $order_id, 'ref_by_subid' ) ) ) {
			return;
		}

		$ref_by_subid = ! empty( $_COOKIE['ref_by_subid'] ) ? $_COOKIE['ref_by_subid'] : get_user_meta( get_current_user_id(), 'ref_by_subid', true );

		if( empty( $ref_by_subid ) ) {
			return;
		}

		$this->add_order_meta_data( $order_id, 'ref_by_subid', $ref_by_subid );

		return;
	}

	public function after_order_completed( $order_id )
	{
		$ref_by = $this->get_order_meta_data( $order_id, 'ref_by' );

		$ref_by_subid = $this->get_order_meta_data( $order_id, 'ref_by_subid' );

		if ( ! $ref_by ) {
			$ref_by = 'null';
		}

		if ( $ref_by_subid ) {
			$ref_by = $ref_by . '::' . urlencode( $ref_by_subid );
		}

		$order = $this->get_order_by_id( $order_id );

		$username = $this->get_uid_prefix();

		$amount = $this->get_revenue_share_total( $order );

		$release = $this->get_release_value();

		$result = $this->get_api_wrapper()->send_request(
			"transaction/addtransaction/username/$username/ref_by/$ref_by/uid/$username-$order_id/amount/$amount/merchant/$username/release/$release"
		);

		if ( isset( $result['message'] ) && $result['message'] != 'Done' ) {
			$this->write_log( "Error while adding an affiliate TX:: username: $username / uid: $username - $order_id / amount: $amount / release: $release");
		}

		delete_transient( 'poc_foundation_coupon_' . $ref_by . '_is_valid' );

		return;
	}

	public function after_order_refunded( $order_id )
	{
		$uid = $this->get_uid_prefix();

		$this->write_log( "Revoked an affiliate TX:: $uid.$order_id" );

		$result = $this->get_api_wrapper()->send_request(
			"revoketransaction/uid/$uid.$order_id"
		);

		if ( $result != 'Done') {
			$this->write_log( "Error while revoke a Tx:: uid: $uid.$order_id" );
		}

		return;
	}

	/**
	 * Custom discount amount base on product
	 *
	 * @param $round
	 * @param $discounting_amount
	 * @param $cart_item
	 * @param $single
	 * @param $coupon
	 *
	 * @return false|float
	 */
	public function get_discount_amount( $round, $discounting_amount, $cart_item, $single, $coupon )
	{
		$product = wc_get_product( $cart_item['product_id'] );

		if( ! $product ) {
			return $round;
		}

		$custom_discount = $product->get_meta( 'poc_foundation_discount' );

		if( empty( $custom_discount ) ) {
			return $round;
		}

		$discount = (float) $cart_item['line_subtotal'] * ( (int) $custom_discount / 100 );

		$round = round( $discount, wc_get_rounding_precision() );

		return $round;
	}

	/**
	 * Add order meta data
	 *
	 * @param integer $order_id
	 * @param string $key
	 * @param string $value
	 *
	 * @return false|int
	 */
	protected function add_order_meta_data( $order_id, $key, $value )
	{
		return add_post_meta( $order_id, $key, $value );
	}

	/**
	 * Get order meta data
	 *
	 * @param $order_id
	 * @param $key
	 *
	 * @return mixed
	 */
	protected function get_order_meta_data( $order_id, $key )
	{
		return get_post_meta( $order_id, $key, true );
	}

	/**
	 * Get order by id
	 *
	 * @param $order_id
	 *
	 * @return bool|\WC_Order|\WC_Order_Refund
	 */
	protected function get_order_by_id( $order_id )
	{
		return wc_get_order( $order_id );
	}

	/**
	 * Calculate revenue share total
	 *
	 * @param $order
	 *
	 * @return float|int
	 */
	protected function get_revenue_share_total( $order )
	{
		$revenue_share_total = 0;

		foreach ( $order->get_items() as $item ) {
			$revenue_share_percent = (int) $item->get_product()->get_meta( 'poc_foundation_revenue_share' );

			if( empty( $revenue_share_percent ) ) {
				$revenue_share_percent = (int) $this->get_default_revenue_share();
			}

			$revenue_share = (  $item->get_total() ) * ( $revenue_share_percent / 100 );

			$revenue_share_total += $revenue_share;
		}

		return round( $revenue_share_total / $this->get_poc_price(), 6 );
	}

	/**
	 * Get POC Price
	 *
	 * @return bool|string
	 */
	protected function get_poc_price()
	{
		$currency = strtolower( get_woocommerce_currency() );
		$price = $this->get_api_wrapper()->send_request( "getprice/poc/$currency" );
		if ($price && is_numeric($price['data']['price']) && $price['data']['price'] > 0) {
			return $price['data']['price'];
		} else {
			// Try again after 1s
			sleep(1);
			$price = $this->get_api_wrapper()->send_request( "getprice/poc/$currency" );
			if ($price && is_numeric($price['data']['price']) && $price['data']['price'] > 0) {
				return $price['data']['price'];
			} else {
				// Try again after 1s
				sleep(1);
				$price = $this->get_api_wrapper()->send_request( "getprice/poc/$currency" );
				if ($price && is_numeric($price['data']['price']) && $price['data']['price'] > 0) {
					return $price['data']['price'];
				} else {
					// Try again after 1s
					sleep(1);
					return false;
				}
			}
		}
	}

	/**
	 * Get UID Prefix
	 *
	 * @return bool|mixed|void
	 */
	protected function get_uid_prefix()
	{
		return get_option( 'poc_foundation_uid_prefix', true );
	}

	/**
	 * Get refund term
	 *
	 * @return int
	 */
	protected function get_refund_term()
	{
		return 1;
	}

	/**
	 * Get release value
	 *
	 * @return float|int
	 */
	protected function get_release_value()
	{
		return time() + $this->get_refund_term() * 60;
	}

	/**
	 * Get default revenue share value
	 *
	 * @return bool|mixed|void
	 */
	protected function get_default_revenue_share()
	{
		return get_option( 'poc_foundation_default_revenue_share', 60 );
	}

	protected function get_api_wrapper()
	{
		return new POC_API();
	}

	/**
	 * Write log
	 *
	 * @param $log
	 */
	protected function write_log( $log )
	{
		if ( is_array( $log ) || is_object( $log ) ) {
			error_log( print_r( $log, true ) );
		} else {
			error_log( $log );
		}
	}
}