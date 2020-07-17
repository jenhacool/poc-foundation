<?php

use POC\Foundation\POC_Foundation_Admin;

class Test_Class_POC_Foundation_Admin extends \WP_UnitTestCase
{
	public $instance;

	public function setUp()
	{
		$this->instance = new POC_Foundation_Admin();
	}

	public function test_add_hooks()
	{
		$this->assertGreaterThan(
			0,
			has_action(
				'admin_menu',
				array( $this->instance, 'register_options_page' )
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

	public function test_register_options_page()
	{
		$user_id = $this->factory->user->create();

		$user = new \WP_User( $user_id );

		$user->add_role('administrator');

		wp_set_current_user( $user_id );

		global $submenu, $menu;

		$this->assertEmpty( $menu );

		$this->instance->register_options_page();

		$this->assertEquals( 1, count( $menu ) );
		$this->assertEquals( 'POC Foundation', $menu[0][0] );
		$this->assertEquals( 'manage_options', $menu[0][1] );
		$this->assertEquals( 'poc-foundation', $menu[0][2] );
		$this->assertEquals( 'POC Foundation', $menu[0][3] );
		$this->assertArrayHasKey( 'poc-foundation', $submenu );

		wp_delete_user( $user_id );
	}
}