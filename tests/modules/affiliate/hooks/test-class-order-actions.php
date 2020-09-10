<?php

use POC\Foundation\Modules\Affiliate\Hooks\Order_Actions;
use POC\Foundation\API;
use Mockery as m;

class Test_Class_Order_Actions extends \WP_UnitTestCase
{
	public $instance;

	public function setUp()
	{
		parent::setUp();

		$this->instance = new Order_Actions();
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
			has_filter(
				'woocommerce_coupon_get_discount_amount',
				array( $this->instance, 'get_discount_amount' )
			)
		);
	}

	public function test_add_ref_to_order_from_coupon_code()
	{
		$order_mock = m::mock( WC_Order::class )->makePartial()->shouldAllowMockingProtectedMethods();
		$order_mock->shouldReceive( 'get_coupon_codes' )->once()->andReturn( array( 'jenhacool' ) );

		$mock = m::mock( Order_Actions::class )->makePartial()->shouldAllowMockingProtectedMethods();
		$mock->shouldReceive( 'is_coupon_valid' )->once()->with( 'jenhacool' )->andReturn( true );
		$mock->shouldReceive( 'add_order_meta_data' )->once()->with( 1, 'ref_by', 'jenhacool' )->andReturn( true );

		$this->assertNull( $mock->add_ref_to_order( 1, array(), $order_mock ) );
	}

	public function test_add_ref_to_order_when_no_coupon_code()
	{
		$_COOKIE['ref_by'] = 'jenhacool';

		$order_mock = m::mock( WC_Order::class )->makePartial()->shouldAllowMockingProtectedMethods();
		$order_mock->shouldReceive( 'get_coupon_codes' )->once()->andReturn( array() );

		$mock = m::mock( Order_Actions::class )->makePartial()->shouldAllowMockingProtectedMethods();
		$mock->shouldReceive( 'add_order_meta_data' )->once()->with( 1, 'ref_by', 'jenhacool' )->andReturn( true );

		$this->assertNull( $mock->add_ref_to_order( 1, array(), $order_mock ) );
	}

	public function test_after_order_completed()
	{
		$api_mock = m::mock( API::class );
		$api_mock->shouldReceive( 'send_request' )->once()->with(
			'transaction/addtransaction/username/foo.bar/ref_by/jenhacool/uid/foo.bar-1/amount/10/merchant/foo.bar/release/100'
		)->andReturn( array( 'message' => 'Done' ) );

		$order_mock = m::mock( \WC_Order::class )->makePartial()->shouldAllowMockingProtectedMethods();

		$mock = m::mock( Order_Actions::class )->makePartial()->shouldAllowMockingProtectedMethods();
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
		$api_mock = m::mock( API::class );
		$api_mock->shouldReceive( 'send_request' )->once()->with(
			'revoketransaction/uid/foo.bar.1'
		)->andReturn( array( 'message' => 'Done' ) );

		$mock = m::mock( Order_Actions::class )->makePartial()->shouldAllowMockingProtectedMethods();
		$mock->shouldReceive( 'get_uid_prefix' )->once()->andReturn( 'foo.bar' );
		$mock->shouldReceive( 'get_api_wrapper' )->once()->andReturn( $api_mock );

		$this->assertNull( $mock->after_order_refunded( 1 ) );
	}
}