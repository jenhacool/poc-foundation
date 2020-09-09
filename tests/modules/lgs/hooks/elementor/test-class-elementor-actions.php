<?php

use POC\Foundation\Modules\LGS\Hooks\Elementor\Elementor_Actions;

class Test_Class_Elementor_Actions extends \WP_UnitTestCase
{
	public $instance;

	public function setUp()
	{
		parent::setUp();

		$this->instance = new Elementor_Actions();
	}

	public function test_hooks()
	{
		$this->instance->hooks();

		$this->assertGreaterThan(
			0,
			has_action(
				'elementor_pro/init',
				array( $this->instance, 'add_elementor_form_action' )
			)
		);
	}
}