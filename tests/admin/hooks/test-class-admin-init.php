<?php

use POC\Foundation\Admin\Hooks\Admin_Init;
use Mockery as m;

class Test_Class_Admin_Init extends \WP_UnitTestCase
{
	public $instance;

	public function setUp()
	{
		$this->instance = new Admin_Init();
	}

	public function tearDown()
	{
		m::close();

		parent::tearDown();
	}

	public function test_hooks()
	{
		$this->instance->hooks();

		$this->assertGreaterThan(
			0,
			has_action(
				'admin_init',
				array( $this->instance, 'on_admin_init' )
			)
		);
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
		$mock = m::mock( Admin_Init::class )->makePartial()->shouldAllowMockingProtectedMethods();
		$mock->shouldReceive( 'is_new_install' )->once()->andReturn( true );
		$mock->shouldReceive( 'redirect_to_wizard_page' )->once()->andReturn( true );

		$this->assertTrue( $mock->admin_redirect() );
	}
}