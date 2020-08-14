<?php

use POC\Foundation\License\POC_Foundation_License;
use POC\Foundation\License\POC_Foundation_License_Server;
use Mockery as m;

class Test_Class_POC_Foundation_License extends \WP_UnitTestCase
{
	public $instance;

	public function setUp()
	{
		parent::setUp();

		$this->instance = new POC_Foundation_License();
	}

	public function tearDown() {
		delete_transient( 'poc_foundation_license_data' );
		delete_option( 'poc_foundation_license' );
		m::close();
		parent::tearDown();
	}

	public function test_get_license_data_without_local_key()
	{
		delete_transient( 'poc_foundation_license_data' );

		$license_data = array(
			'status' => 'Active'
		);

		$license_server_mock = m::mock( POC_Foundation_License_Server::class );
		$license_server_mock->shouldReceive( 'check' )->once()->with( 'license_key', '' )->andReturn( $license_data );

		$mock = m::mock( POC_Foundation_License::class )->makePartial()->shouldAllowMockingProtectedMethods();
		$mock->shouldReceive( 'get_license_server' )->once()->andReturn( $license_server_mock );

		$mock->get_license_data( 'license_key' );

		$this->assertEquals( $license_data, get_transient( 'poc_foundation_license_data' ) );
	}

	public function test_get_license_data_with_local_key()
	{
		delete_transient( 'poc_foundation_license_data' );

		update_option( 'poc_foundation_license_key', 'saved_license_key' );
		update_option( 'poc_foundation_license_local_key', 'saved_local_key' );

		$license_data = array(
			'status' => 'Active'
		);

		$license_server_mock = m::mock( POC_Foundation_License_Server::class );
		$license_server_mock->shouldReceive( 'check' )->once()->with( 'license_key', 'saved_local_key' )->andReturn( $license_data );

		$mock = m::mock( POC_Foundation_License::class )->makePartial()->shouldAllowMockingProtectedMethods();
		$mock->shouldReceive( 'get_license_server' )->once()->andReturn( $license_server_mock );

		$this->assertEquals( $license_data, $mock->get_license_data( 'license_key' ) );
	}

	public function test_check_license()
	{
		set_transient(
			'poc_foundation_license_data',
			array(
				'status' => 'Active'
			),
			12 * HOUR_IN_SECONDS
		);

		$this->assertTrue( $this->instance->check_license() );
	}
}