<?php

use POC\Foundation\Modules\Affiliate\Hooks\Affiliate_Checkout;

class Test_Class_Affiliate_Checkout extends \WP_UnitTestCase
{
	public $instance;

	public function setUp()
	{
		parent::setUp();

		$this->instance = new Affiliate_Checkout();
	}

	public function test_hooks()
	{
		$this->instance->hooks();

		$this->assertGreaterThan(
			0,
			has_action(
				'woocommerce_after_checkout_form',
				array( $this->instance, 'checkout_form_script' )
			)
		);
	}
}