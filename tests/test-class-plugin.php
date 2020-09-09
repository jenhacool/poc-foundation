<?php

use POC\Foundation\Plugin;

class Test_Class_Plugin extends \WP_UnitTestCase
{
	public $instance;

	public $user_id;

	public function setUp() {
		parent::setUp();

		$this->instance = Plugin::instance();
	}

	public function test_add_hooks()
	{
		$this->assertGreaterThan(
			0,
			has_action(
				'wp_enqueue_scripts',
				array( $this->instance, 'add_scripts' )
			)
		);
	}
}