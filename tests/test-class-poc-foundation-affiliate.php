<?php

use POC\Foundation\POC_Foundation_Affiliate;
use POC\Foundation\POC_Foundation_API;
use Mockery as m;

class Test_Class_POC_Foundation_Affiliate extends \WP_UnitTestCase
{
	public $instance;

	public function setUp()
	{
		parent::setUp();

		$this->instance = new POC_Foundation_Affiliate();
	}

	public function tearDown()
	{
		m::close();

		parent::tearDown();
	}

	public function test_add_hooks()
	{
		$this->assertGreaterThan(
			0,
			has_action(
				'wp_login',
				array( $this->instance, 'add_ref_to_user' )
			)
		);

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
				'woocommerce_checkout_order_processed',
				array( $this->instance, 'add_ref_to_order' )
			)
		);

		$this->assertGreaterThan(
			0,
			has_action(
				'woocommerce_order_status_completed',
				array( $this->instance, 'after_order_completed' )
			)
		);

		$this->assertGreaterThan(
			0,
			has_action(
				'woocommerce_order_status_refunded',
				array( $this->instance, 'after_order_refunded' )
			)
		);

		$this->assertGreaterThan(
			0,
			has_action(
				'woocommerce_product_options_general_product_data',
				array( $this->instance, 'add_custom_product_data_field' )
			)
		);

		$this->assertGreaterThan(
			0,
			has_action(
				'woocommerce_process_product_meta',
				array( $this->instance, 'save_custom_product_data_field' )
			)
		);

		$this->assertGreaterThan(
			0,
			has_action(
				'woocommerce_get_shop_coupon_data',
				array( $this->instance, 'create_virtual_coupon' )
			)
		);

		$this->assertGreaterThan(
			0,
			has_action(
				'woocommerce_coupon_get_discount_amount',
				array( $this->instance, 'get_discount_amount' )
			)
		);
	}

	public function test_add_ref_to_user()
	{
		$user_id = $this->factory->user->create();

		$user = new \WP_User( $user_id );

		$_COOKIE['ref_by'] = 'jenhacool';
		$_COOKIE['ref_by_subid'] = 'subid';

		$this->instance->add_ref_to_user( $user_id, $user );

		$this->assertEquals( 'jenhacool', get_user_meta( $user_id, 'ref_by', true ) );
		$this->assertEquals( 'subid', get_user_meta( $user_id, 'ref_by_subid', true ) );

		wp_delete_user( $user_id );
	}

	public function test_apply_coupon_by_ref_by()
	{
		$_COOKIE['ref_by'] = 'jenhacool';

		$cart_mock = m::mock( WC_Cart::class )->makePartial()->shouldAllowMockingProtectedMethods();
		$cart_mock->shouldReceive( 'get_applied_coupons' )->once()->andReturn( array() );
		$cart_mock->shouldReceive( 'add_discount' )->once()->with( 'jenhacool' )->andReturn( true );

		$mock = m::mock( POC_Foundation_Affiliate::class )->makePartial()->shouldAllowMockingProtectedMethods();
		$mock->shouldReceive( 'get_cart' )->once()->andReturn( $cart_mock );
		$mock->shouldReceive( 'is_coupon_valid' )->once()->with( 'jenhacool' )->andReturn( true );

		$this->assertNull( $mock->apply_coupon_by_ref_by() );
	}

	public function test_add_ref_to_order_from_coupon_code()
	{
		$order_mock = m::mock( WC_Order::class )->makePartial()->shouldAllowMockingProtectedMethods();
		$order_mock->shouldReceive( 'get_coupon_codes' )->once()->andReturn( array( 'jenhacool' ) );

		$mock = m::mock( POC_Foundation_Affiliate::class )->makePartial()->shouldAllowMockingProtectedMethods();
		$mock->shouldReceive( 'is_coupon_valid' )->once()->with( 'jenhacool' )->andReturn( true );
		$mock->shouldReceive( 'add_order_meta_data' )->once()->with( 1, 'ref_by', 'jenhacool' )->andReturn( true );

		$this->assertNull( $mock->add_ref_to_order( 1, array(), $order_mock ) );
	}

	public function test_add_ref_to_order_when_no_coupon_code()
	{
		$_COOKIE['ref_by'] = 'jenhacool';

		$order_mock = m::mock( WC_Order::class )->makePartial()->shouldAllowMockingProtectedMethods();
		$order_mock->shouldReceive( 'get_coupon_codes' )->once()->andReturn( array() );

		$mock = m::mock( POC_Foundation_Affiliate::class )->makePartial()->shouldAllowMockingProtectedMethods();
		$mock->shouldReceive( 'add_order_meta_data' )->once()->with( 1, 'ref_by', 'jenhacool' )->andReturn( true );

		$this->assertNull( $mock->add_ref_to_order( 1, array(), $order_mock ) );
	}

	public function test_after_order_completed()
	{
		$api_mock = m::mock( POC_Foundation_API::class );
		$api_mock->shouldReceive( 'send_request' )->once()->with(
			'transaction/addtransaction/username/foo.bar/ref_by/jenhacool/uid/foo.bar-1/amount/10/merchant/foo.bar/release/100'
		)->andReturn( array( 'message' => 'Done' ) );

		$order_mock = m::mock( WC_Order::class )->makePartial()->shouldAllowMockingProtectedMethods();

		$mock = m::mock( POC_Foundation_Affiliate::class )->makePartial()->shouldAllowMockingProtectedMethods();
		$mock->shouldReceive( 'get_order_meta_data' )->once()->with( 1, 'ref_by' )->andReturn( 'jenhacool' );
		$mock->shouldReceive( 'get_order_meta_data' )->once()->with( 1, 'ref_by_subid' )->andReturn( '' );
		$mock->shouldReceive( 'get_order_by_id' )->once()->with( 1 )->andReturn( $order_mock );
		$mock->shouldReceive( 'get_revenue_share_total')->once()->with( $order_mock )->andReturn( 10 );
		$mock->shouldReceive( 'get_uid_prefix' )->once()->andReturn( 'foo.bar' );
		$mock->shouldReceive( 'get_release_value' )->once()->andReturn( 100 );
		$mock->shouldReceive( 'get_api_wrapper' )->once()->andReturn( $api_mock );

		$this->assertNull( $mock->after_order_completed( 1 ) );
	}

	public function test_after_order_refunded()
	{
		$api_mock = m::mock( POC_Foundation_API::class );
		$api_mock->shouldReceive( 'send_request' )->once()->with(
			'revoketransaction/uid/foo.bar.1'
		)->andReturn( array( 'message' => 'Done' ) );

		$mock = m::mock( POC_Foundation_Affiliate::class )->makePartial()->shouldAllowMockingProtectedMethods();
		$mock->shouldReceive( 'get_uid_prefix' )->once()->andReturn( 'foo.bar' );
		$mock->shouldReceive( 'get_api_wrapper' )->once()->andReturn( $api_mock );

		$this->assertNull( $mock->after_order_refunded( 1 ) );
	}
}