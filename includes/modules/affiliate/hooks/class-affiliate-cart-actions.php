<?php

namespace POC\Foundation\Modules\Affiliate\Hooks;

use POC\Foundation\Classes\Helper;
use POC\Foundation\Classes\Option;
use POC\Foundation\Contracts\Hook;
use POC\Foundation\Modules\Affiliate\Utilities\Check_Coupon;

class Affiliate_Cart_Actions implements Hook
{
	use Check_Coupon;

	public function hooks()
	{
		add_action( 'woocommerce_before_cart', array( $this, 'apply_coupon_by_ref_by' ) );

		add_action( 'woocommerce_checkout_update_order_review', array( $this, 'apply_coupon_by_customer_info' ) );

		add_filter( 'woocommerce_get_shop_coupon_data', array( $this, 'create_virtual_coupon' ), 10, 2  );
	}

	public function apply_coupon_by_ref_by()
	{
		if ( $this->has_applied_coupons() ) {
			return;
		}

		$ref_by = ! empty( $_COOKIE['ref_by'] ) ? $_COOKIE['ref_by'] : '';

		if ( empty( $ref_by ) ) {
			return;
		}

		return $this->apply_coupon( $ref_by, true );
	}

	public function apply_coupon_by_customer_info( $post_data )
	{
		if ( $this->has_applied_coupons() ) {
			return;
		}

		$data = array();

		$post_data_array = explode( '&', $post_data );

		foreach ( $post_data_array as $k => $value ) {
			$v = explode( '=', urldecode( $value ) );
			$data[$v[0]] = $v[1];
		}

		$data = array_merge( array(
			'billing_email' => '',
			'billing_phone' => '',
		), $data );

		$meta_query_args = array();

		if ( ! empty( $data['billing_phone'] ) ) {
			$this->get_cart()->get_customer()->set_billing_phone( $data['billing_phone'] );

			$meta_query_args[] = array(
				'key' => 'phone',
				'value' => Helper::sanitize_phone_number( $data['billing_phone'] ),
				'compare' => '='
			);
		}

		if ( ! empty( $data['billing_email'] ) ) {
			$this->get_cart()->get_customer()->set_billing_email( $data['billing_email'] );

			$meta_query_args[] = array(
				'key' => 'email',
				'value' => sanitize_email( $data['billing_email'] ),
				'compare' => '='
			);
		}

		if ( empty( $meta_query_args ) ) {
			$this->get_cart()->remove_coupons();
			return;
		}

		if ( count( $meta_query_args ) === 2 ) {
			$meta_query_args['relation'] = 'OR';
		}

		$args = array(
			'meta_query' => $meta_query_args,
			'post_type' => 'poc_foundation_lead',
			'posts_per_page' => 1
		);

		$query = new \WP_Query( $args );

		$leads = $query->get_posts();

		if ( empty( $leads ) ) {
			$this->get_cart()->remove_coupons();
			return;
		}

		$lead = $leads[0];

		$ref_by = $lead->ref_by;

		if ( empty( $ref_by ) ) {
			$this->get_cart()->remove_coupons();
			return;
		}

		return $this->apply_coupon( $ref_by );
	}

	protected function apply_coupon( $ref_by, $print_notices = false )
	{
		$cart = $this->get_cart();

		$applied_coupons = $cart->get_applied_coupons();

		// Check if cart has coupon or not
		if ( in_array( $ref_by, $applied_coupons ) ) {
			return;
		}

		// Check if coupon is valid or not
		if ( ! $this->is_coupon_valid( $ref_by ) ) {
			return;
		}

		// If valid, apply it
		$cart->add_discount( $ref_by );

		if ( $print_notices ) {
			wc_print_notices();
		}

		return;
	}

	/**
	 * Create virtual coupon
	 *
	 * @param $false
	 * @param $data
	 *
	 * @return array|null
	 */
	public function create_virtual_coupon( $false, $data )
	{
		if ( is_admin() ) {
			return $false;
		}

		$coupon_settings = null;

		if ( ! $this->is_coupon_valid( $data ) ) {
			return $false;
		}

		$coupon_settings = array(
			'discount_type' => 'percent',
			'amount' => (int) $this->get_default_discount(),
			'individual_use' => true,
		);

		return $coupon_settings;
	}

	/**
	 * Get default discount value
	 *
	 * @return bool|mixed|void
	 */
	protected function get_default_discount()
	{
		return Option::get( 'default_discount' );
	}

	/**
	 * Get cart object
	 *
	 * @return \WC_Cart|null
	 */
	protected function get_cart()
	{
		return WC()->cart;
	}

	protected function has_applied_coupons()
	{
		return ! empty( $this->get_cart()->get_applied_coupons() );
	}
}