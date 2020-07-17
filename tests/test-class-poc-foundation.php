<?php

use POC\Foundation\POC_Foundation;
use Mockery as m;

class Test_Class_POC_Foundation extends \WP_UnitTestCase
{
	public $instance;

	public $user_id;

	public function setUp()
	{
		parent::setUp();

		$this->instance = POC_Foundation::instance();

		$this->user_id = $this->factory->user->create();

		$user = new \WP_User( $this->user_id );

		$user->add_role('administrator');

		wp_set_current_user( $this->user_id );
	}

	public function tearDown()
	{
		m::close();

		wp_delete_user( $this->user_id );
	}

	public function test_check_license()
	{
		$this->expectNotToPerformAssertions();

		// If license valid
		$mock = m::mock( POC_Foundation::class )->makePartial()->shouldAllowMockingProtectedMethods();
		$mock->shouldReceive( 'check_license' )->once()->andReturn( true );
		$mock->shouldReceive( 'add_hooks' )->once();
		$mock->shouldReceive( 'add_license_notice' )->never();

		$mock->init();

		// If license not valid
		$mock = m::mock( POC_Foundation::class )->makePartial()->shouldAllowMockingProtectedMethods();
		$mock->shouldReceive( 'check_license' )->once()->andReturn( false );
		$mock->shouldReceive( 'add_hooks' )->never();
		$mock->shouldReceive( 'add_license_notice' )->once();

		$mock->init();
	}

	public function test_add_hooks()
	{

	}
}