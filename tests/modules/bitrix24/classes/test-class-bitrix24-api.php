<?php

use POC\Foundation\Modules\Bitrix24\Classes\Bitrix24_API;
use Mockery as m;

class Test_Class_Bitrix24_API extends \WP_UnitTestCase
{
	public $instance;

	public function setUp()
	{
		parent::setUp();

		$this->instance = new Bitrix24_API();
	}

	public function tearDown()
	{
		m::close();

		parent::tearDown();
	}

	public function test_get_deal_categories()
	{
		$mock = $this->get_mock();
		$mock->shouldReceive( 'send_request' )->once()->with( 'crm.dealcategory.list', 'POST' )->andReturn( true );

		$this->assertTrue( $mock->get_deal_categories() );
	}

	public function test_get_statuses()
	{
		$mock = $this->get_mock();
		$mock->shouldReceive( 'send_request' )->once()->with( 'crm.status.list', 'POST' )->andReturn( true );

		$this->assertTrue( $mock->get_statuses() );
	}

	public function get_mock()
	{
		return m::mock( Bitrix24_API::class )->makePartial()->shouldAllowMockingProtectedMethods();
	}
}