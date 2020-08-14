<?php

use POC\Foundation\Admin\POC_Foundation_Admin;
use Mockery as m;

class Test_Class_POC_Foundation_Admin extends \WP_UnitTestCase
{
	public $instance;

	public function setUp()
	{
		$this->instance = new POC_Foundation_Admin();
	}

	public function tearDown() {
		m::close();

		parent::tearDown();
	}

	public function test_add_hooks()
	{
		$this->assertGreaterThan(
			0,
			has_action(
				'admin_init',
				array( $this->instance, 'on_admin_init' )
			)
		);

		$this->assertGreaterThan(
			0,
			has_action(
				'admin_notices',
				array( $this->instance, 'admin_license_notice' )
			)
		);

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
				'admin_menu',
				array( $this->instance, 'admin_menu_change_name' )
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

	public function test_admin_menu_change_name()
	{
		$user_id = $this->factory->user->create();

		$user = new \WP_User( $user_id );

		$user->add_role('administrator');

		wp_set_current_user( $user_id );

		global $submenu;

		$this->instance->register_options_page();

		$this->instance->admin_menu_change_name();

		$this->assertEquals( 'Settings', $submenu['poc-foundation'][0][0] );
	}

	public function test_is_new_install()
	{
		delete_option( 'poc_foundation_api_key' );

		$this->assertTrue( $this->instance->is_new_install() );

		delete_option( 'poc_foundation_uid_prefix' );

		$this->assertTrue( $this->instance->is_new_install() );
	}

	public function test_admin_redirect()
	{
		set_transient( 'poc_foundation_activation_redirect', 1, 30 );
		$mock = m::mock( POC_Foundation_Admin::class )->makePartial()->shouldAllowMockingProtectedMethods();
		$mock->shouldReceive( 'is_new_install' )->once()->andReturn( true );
		$mock->shouldReceive( 'redirect_to_wizard_page' )->once()->andReturn( true );

		$this->assertTrue( $mock->admin_redirect() );
	}
}