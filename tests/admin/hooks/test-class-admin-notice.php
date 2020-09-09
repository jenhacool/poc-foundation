<?php

use POC\Foundation\Admin\Admin_Notice;

class Test_Class_Admin_Asset extends \WP_UnitTestCase
{
	public $instance;

	public function setUp()
	{
		parent::setUp();

		$this->instance = new Admin_Notice();
	}

	public function test_hooks()
	{
		$this->instance->hooks();

		$this->assertGreaterThan(
			0,
			has_action(
				'admin_notices',
				array( $this->instance, 'admin_license_notice' )
			)
		);
	}
}