<?php

use POC\Foundation\POC_Foundation;
use Mockery as m;

class Test_Class_POC_Foundation extends \WP_UnitTestCase
{
	public $instance;

	public function setUp()
	{
		parent::setUp();

		$this->instance = POC_Foundation::instance();
	}

	public function tearDown()
	{
		m::close();
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
		$this->assertGreaterThan(
			0,
			has_action(
				'wp_login',
				array( $this->instance, 'add_ref_to_user' )
			)
		);
	}
}