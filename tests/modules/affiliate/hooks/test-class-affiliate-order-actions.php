<?php

use POC\Foundation\Modules\Affiliate\Hooks\Affiliate_Order_Actions;
use POC\Foundation\Classes\POC_API;
use Mockery as m;

class Test_Class_Affiliate_Order_Actions extends \WP_UnitTestCase
{
	public $instance;

	public function setUp()
	{
		parent::setUp();

		$this->instance = new Affiliate_Order_Actions();
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
		$order = new \WC_Order();
		$order->apply_coupon( 'jenhacool' );
		$order->save();

		$mock = m::mock( Affiliate_Order_Actions::class )->makePartial()->shouldAllowMockingProtectedMethods();
		$mock->shouldReceive( 'is_coupon_valid' )->once()->with( 'jenhacool' )->andReturn( true );

		$this->assertNull( $mock->add_ref_to_order( $order->get_id(), array(), $order ) );
		$this->assertEquals( 'jenhacool', get_post_meta( $order->get_id(), 'ref_by', true ) );
	}

	public function test_add_ref_to_order_when_no_coupon_code()
	{
		$_COOKIE['ref_by'] = 'jenhacool';

		$order = new \WC_Order();
		$order->save();

		$this->assertNull( $this->instance->add_ref_to_order( $order->get_id(), array(), $order ) );
		$this->assertEquals( 'jenhacool', get_post_meta( $order->get_id(), 'ref_by', true ) );
	}

	public function test_after_order_completed()
	{
		$order = new \WC_Order();
		$order->save();

		$mock = m::mock( Affiliate_Order_Actions::class )->makePartial()->shouldAllowMockingProtectedMethods();
		$mock->shouldReceive( 'pay_reward' )->once()->with( $order->get_id() )->andReturn( 'test_hash' );

		$this->assertNull( $mock->after_order_completed( $order->get_id() ) );
		$this->assertEquals( 'test_hash', get_post_meta( $order->get_id(), 'transaction_hash', true ) );
		$this->assertEquals( 'sent', get_post_meta( $order->get_id(), 'reward_status', true ) );
	}

	public function test_after_order_refunded()
	{

	}
}