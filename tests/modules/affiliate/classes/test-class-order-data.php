<?php

use POC\Foundation\Modules\Affiliate\Classes\Order_Data;
use POC\Foundation\Tests\Helpers\WC_Helper_Product;
use Mockery as m;

class Test_Class_Order_Data extends \WP_UnitTestCase
{
	public $order;

	public $order_data;

	public function setUp()
	{
		parent::setUp();
	}

	public function test_get_revenue_share_total()
	{
		update_option( 'poc_foundation', serialize( array(
			'default_revenue_share' => 80
		) ) );

		$product1 = WC_Helper_Product::create_simple_product();
		$product1->set_regular_price( 2 );
		$product1->save();

		$product2 = WC_Helper_Product::create_simple_product();
		$product2->set_regular_price( 3 );
		$product2->add_meta_data( 'poc_foundation_revenue_share', 50 );
		$product2->save();

		$order = new WC_Order();
		$order->add_product( $product1, 1 );
		$order->add_product( $product2, 1 );
		$order->save();

		$order_data_mock = m::mock( Order_Data::class )->makePartial()->shouldAllowMockingProtectedMethods();
		$order_data_mock->shouldReceive( 'get_order' )->atLeast()->andReturn( $order );
		$order_data_mock->shouldReceive( 'get_poc_price' )->once()->andReturn( 1 );

		$this->assertEquals( 3.1, $order_data_mock->get_revenue_share_total() );
	}

	public function test_get_ref_by_string()
	{
		$order = new \WC_Order();
		$order->add_meta_data( 'ref_by', 'jenhacool' );
		$order->add_meta_data( 'ref_by_subid', 'flyforever123' );
		$order->save();

		$order_data = new Order_Data();
		$order_data->set_order_id( $order->get_id() );

		$this->assertEquals( 'jenhacool::flyforever123', $order_data->get_ref_by_string() );
	}
}