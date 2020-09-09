<?php

namespace POC\Foundation\Modules\Affiliate;

use POC\Foundation\Contracts\Hook;

class Product_Options implements Hook
{
	public function hooks()
	{
		add_action( 'woocommerce_product_options_general_product_data', array( $this, 'add_custom_product_data_field' ) );

		add_action( 'woocommerce_process_product_meta', array( $this, 'save_custom_product_data_field' ) );
	}

	/**
	 * Add custom product data field
	 */
	public function add_custom_product_data_field()
	{
		woocommerce_wp_text_input( array(
			'id' => 'poc_foundation_discount',
			'label' => __( 'POC Discount' ),
		) );

		woocommerce_wp_text_input( array(
			'id' => 'poc_foundation_revenue_share',
			'label' => __( 'POC Revenue share' ),
		) );
	}

	/**
	 * Save custom product data field
	 *
	 * @param $post_id
	 */
	public function save_custom_product_data_field( $post_id )
	{
		if( ! isset( $_POST['poc_foundation_discount'] ) && ! isset( $_POST['poc_foundation_revenue_share'] ) ) {
			return;
		}

		$product = wc_get_product( $post_id );

		if( ! $product ) {
			return;
		}

		$discount = ( $_POST['poc_foundation_discount'] ) ? sanitize_text_field( $_POST['poc_foundation_discount'] ) : '';
		$revenue_share = ( $_POST['poc_foundation_revenue_share'] ) ? sanitize_text_field( $_POST['poc_foundation_revenue_share'] ) : '';

		$product->update_meta_data( 'poc_foundation_discount', $discount );
		$product->update_meta_data( 'poc_foundation_revenue_share', $revenue_share );

		$product->save();
	}
}