<?php

use POC\Foundation\POC_Foundation_License;
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
//		delete_transient( 'poc_foundation_license_data' );

		parent::tearDown();
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