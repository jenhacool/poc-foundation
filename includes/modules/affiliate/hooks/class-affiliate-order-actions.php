<?php

namespace POC\Foundation\Modules\Affiliate\Hooks;

use POC\Foundation\Contracts\Hook;
use POC\Foundation\Modules\Affiliate\Utilities\Check_Coupon;
use POC\Foundation\Modules\Affiliate\Classes\Order_Reward;

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

	/**
	 * Add ref by information to order
	 *
	 * @param $order_id
	 * @param $posted_data
	 * @param $order
	 */
	public function add_ref_to_order( $order_id, $posted_data, $order )
	{
		if ( ! empty( get_post_meta( $order_id, 'ref_by' ) ) ) {
			return;
		}

		$coupon_codes = $order->get_coupon_codes();

		if ( ! empty( $coupon_codes ) && $this->is_coupon_valid( $coupon_codes[0] ) ) {
			$ref_by = $coupon_codes[0];
		} else {
			$ref_by = isset( $_COOKIE['ref_by'] ) ? $_COOKIE['ref_by'] : '';
		}

		if ( empty( $ref_by ) ) {
			return;
		}

		update_post_meta( $order_id, 'ref_by', $ref_by );

		if ( ! empty( get_post_meta( $order_id, 'ref_by_subid' ) ) ) {
			return;
		}

		$ref_by_subid = isset( $_COOKIE['ref_by_subid'] ) ? $_COOKIE['ref_by_subid'] : '';

		if( empty( $ref_by_subid ) ) {
			return;
		}

		update_post_meta( $order_id, 'ref_by_subid', $ref_by_subid );

		return;
	}

	/**
	 * Send reward transaction when order completed
	 *
	 * @param $order_id
	 */
	public function after_order_completed( $order_id )
	{
        $transaction_hash = $this->pay_reward( $order_id );

        if ( ! $transaction_hash || empty( $transaction_hash ) ){
        	return;
        }

		add_post_meta( $order_id, 'transaction_hash', $transaction_hash );
        add_post_meta( $order_id, 'reward_status', 'sent' );

		return;
	}

	/**
	 * Pay reward
	 *
	 * @param $order_id
	 *
	 * @return mixed
	 */
	protected function pay_reward( $order_id )
	{
		$order_reward = new Order_Reward();
		$order_reward->set_order_id( $order_id );

		return $order_reward->pay();
	}

	public function after_order_refunded( $order_id )
	{
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
}