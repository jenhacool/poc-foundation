<?php

use POC\Foundation\Admin\Hooks\Admin_Asset;

class Test_Class_Admin_Asset extends \WP_UnitTestCase {
	public $instance;

	public function setUp()
	{
		parent::setUp();

		$this->instance = new Admin_Asset();
	}

	public function test_add_hooks()
	{
		$this->instance->hooks();

		$this->assertGreaterThan(
			0,
			has_action(
				'admin_enqueue_scripts',
				array( $this->instance, 'enqueue_scripts' )
			)
		);
	}
}