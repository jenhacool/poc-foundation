<?php

use POC\Foundation\Modules\LGS\Hooks\Elementor\Elementor_Tags;

class Test_Class_Elementor_Tags extends \WP_UnitTestCase
{
	public $instance;

	public function setUp()
	{
		parent::setUp();

		$this->instance = new Elementor_Tags();
	}

	public function test_hooks()
	{
		$this->instance->hooks();

		$this->assertGreaterThan(
			0,
			has_action(
				'elementor/dynamic_tags/register_tags',
				array( $this->instance, 'register_dynamic_tags' )
			)
		);
	}
}