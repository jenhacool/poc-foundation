<?php

use POC\Foundation\Modules\Affiliate\Cart_Actions;
use Mockery as m;

class Test_Class_Cart_Actions extends \WP_UnitTestCase
{
	public $instance;

	public function setUp()
	{
		parent::setUp();

		$this->instance = new Cart_Actions();
	}

	public function tearDown()
	{
		m::close();

		parent::tearDown();
	}

	public function test_hooks()
	{
		$this->instance->hooks();

		$this->assertGreaterThan(
			0,
			has_action(
				'woocommerce_before_cart',
				array( $this->instance, 'apply_coupon_by_ref_by' )
			)
		);

		$this->assertGreaterThan(
			0,
			has_action(
				'woocommerce_checkout_update_order_review',
				array( $this->instance, 'apply_coupon_by_customer_info' )
			)
		);

		$this->assertGreaterThan(
			0,
			has_filter(
				'woocommerce_get_shop_coupon_data',
				array( $this->instance, 'create_virtual_coupon' )
			)
		);
	}

	public function test_apply_coupon_by_ref_by()
	{
		$_COOKIE['ref_by'] = 'jenhacool';

		$cart_mock = m::mock( \WC_Cart::class )->makePartial()->shouldAllowMockingProtectedMethods();
		$cart_mock->shouldReceive( 'get_applied_coupons' )->once()->andReturn( array() );
		$cart_mock->shouldReceive( 'add_discount' )->once()->with( 'jenhacool' )->andReturn( true );

		$mock = m::mock( Cart_Actions::class )->makePartial()->shouldAllowMockingProtectedMethods();
		$mock->shouldReceive( 'get_cart' )->once()->andReturn( $cart_mock );
		$mock->shouldReceive( 'is_coupon_valid' )->once()->with( 'jenhacool' )->andReturn( true );

		$this->assertNull( $mock->apply_coupon_by_ref_by() );
	}

	public function test_apply_coupon_by_customer_info()
	{
		$post_id = $this->factory->post->create( array(
			'post_type' => 'poc_foundation_lead'
		) );

		update_post_meta( $post_id, 'phone', '01234567890' );
		update_post_meta( $post_id, 'email', 'admin@gmail.com' );
		update_post_meta( $post_id, 'ref_by', 'jenhacool' );

		$post_data = 'billing_phone=01234567890&billing_email=admin%40gmail.com';

		$cart_mock = m::mock( \WC_Cart::class )->makePartial()->shouldAllowMockingProtectedMethods();
		$cart_mock->shouldReceive( 'get_applied_coupons' )->once()->andReturn( array() );
		$cart_mock->shouldReceive( 'add_discount' )->once()->with( 'jenhacool' )->andReturn();

		$mock = m::mock( Cart_Actions::class )->makePartial()->shouldAllowMockingProtectedMethods();
		$mock->shouldReceive( 'get_cart' )->once()->andReturn( $cart_mock );
		$mock->shouldReceive( 'is_coupon_valid' )->once()->with( 'jenhacool' )->andReturn( true );

		$this->assertNull( $mock->apply_coupon_by_customer_info( $post_data ) );
	}
}