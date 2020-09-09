<?php

use POC\Foundation\Modules\Affiliate\Product_Options;

class Test_Class_Product_Options extends \WP_UnitTestCase
{
	public $instance;

	public function setUp()
	{
		$this->instance = new Product_Options();
	}

	public function test_hooks()
	{
		$this->instance->hooks();

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
	}
}