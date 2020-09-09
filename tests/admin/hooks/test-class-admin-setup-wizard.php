<?php

use POC\Foundation\Admin\Hooks\Admin_Setup_Wizard;

class Test_Class_Admin_Setup_Wizard extends \WP_UnitTestCase
{
	public $instance;

	public function __construct()
	{
		$this->instance = new Admin_Setup_Wizard();
	}

	public function test_hooks()
	{
		$this->instance->hooks();

		$this->assertGreaterThan(
			0,
			has_action(
				'admin_menu',
				array( $this->instance, 'add_setup_wizard_page' )
			)
		);

		$this->assertGreaterThan(
			0,
			has_action(
				'admin_init',
				array( $this->instance, 'show_setup_wizard_page' )
			)
		);

		$this->assertGreaterThan(
			0,
			has_action(
				'admin_enqueue_scripts',
				array( $this->instance, 'enqueue_scripts' )
			)
		);
	}
}