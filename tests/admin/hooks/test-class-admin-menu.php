<?php

use POC\Foundation\Admin\Hooks\Admin_Menu;

class Test_Class_Admin_Menu extends \WP_UnitTestCase
{
	public $instance;

	public $user_id;

	public function setUp()
	{
		$this->instance = new Admin_Menu();

		$this->user_id = $this->factory->user->create();

		$user = new \WP_User( $this->user_id );

		$user->add_role('administrator');

		wp_set_current_user( $this->user_id );

		add_filter( 'poc_foundation_admin_submenu_pages', function () {
			return array(
				array(
					'page_title' => 'Test',
					'menu_title' => 'Test',
					'menu_slug' => 'test'
				)
			);
		} );
	}

	public function tearDown()
	{
		wp_delete_user( $this->user_id );

		parent::tearDown();
	}

	public function test_hooks()
	{
		$this->instance->hooks();

		$this->assertGreaterThan(
			0,
			has_action(
				'admin_menu',
				array( $this->instance, 'init_admin_menu' )
			)
		);
	}

	public function test_add_admin_menu()
	{
		global $submenu, $menu;

		$this->assertEmpty( $menu );

		$this->instance->init_admin_menu();

		$this->assertEquals( 1, count( $menu ) );
		$this->assertEquals( 'POC Foundation', $menu[0][0] );
		$this->assertEquals( 'manage_options', $menu[0][1] );
		$this->assertEquals( 'poc-foundation', $menu[0][2] );
		$this->assertEquals( 'POC Foundation', $menu[0][3] );
		$this->assertArrayHasKey( 'poc-foundation', $submenu );
	}

	public function test_change_admin_menu_name()
	{
		global $submenu;

		$this->instance->init_admin_menu();

		$this->assertEquals( 'Settings', $submenu['poc-foundation'][0][0] );
	}
}