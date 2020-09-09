<?php

use POC\Foundation\Classes\Option;

class Test_Class_Option extends \WP_UnitTestCase
{
	public $instance;

	public function setUp()
	{
		parent::setUp();

		$this->instance = new Option();

		update_option( 'poc_foundation', serialize( array(
			'foo' => 'bar'
		) ) );
	}

	public function tearDown()
	{
		delete_option( 'poc_foundation' );

		parent::tearDown();
	}

	public function test_get()
	{
		$this->assertEquals( 'bar', $this->instance->get( 'foo' ) );

		$this->assertEquals( '', $this->instance->get( 'non_exist' ) );
	}
}