<?php

use POC\Foundation\Modules\Bitrix24\Classes\Bitrix24_API;
use POC\Foundation\Modules\Bitrix24\Classes\Bitrix24_Data;
use Mockery as m;

class Test_Class_Bitrix24_Data extends \WP_UnitTestCase
{
	public $instance;

	public function setUp()
	{
		parent::setUp();

		$this->instance = new Bitrix24_Data();
	}

	public function tearDown()
	{
		m::close();

		delete_transient( 'poc_foundation_bitrix24_stages' );

		parent::tearDown();
	}

	public function test_get_stages()
	{
		$stages = array( 'foo' => 'bar' );

		set_transient( 'poc_foundation_bitrix24_stages', $stages );

		$this->assertEquals( $stages, $this->instance->get_stages() );
	}

	public function test_get_stages_force_renew()
	{
		$deal_categories = array(
			array(
				'ID' => 1,
				'NAME' => 'Category Name 1'
			),
			array(
				'ID' => 2,
				'NAME' => 'Category Name 2'
			)
		);

		$statuses = array(
			array(
				'STATUS_ID' => 'ID_1',
				'NAME' => 'Status Name 1',
				'ENTITY_ID' => 'DEAL_STAGE_1'
			),
			array(
				'STATUS_ID' => 'ID_2',
				'NAME' => 'Status Name 2',
				'ENTITY_ID' => 'DEAL_STAGE_2'
			),
			array(
				'STATUS_ID' => 'ID_3',
				'NAME' => 'Status Name 3',
				'ENTITY_ID' => 'DEAL_STAGE_3'
			)
		);

		$stages = array(
			'ID_1' => 'Category Name 1 - Status Name 1',
			'ID_2' => 'Category Name 2 - Status Name 2'
		);

		$api_mock = m::mock( Bitrix24_API::class )->makePartial()->shouldAllowMockingProtectedMethods();
		$api_mock->shouldReceive( 'get_deal_categories' )->once()->andReturn( $deal_categories );
		$api_mock->shouldReceive( 'get_statuses' )->once()->andReturn( $statuses );

		$mock = $this->get_mock();
		$mock->shouldReceive( 'get_api_client' )->twice()->andReturn( $api_mock );

		$this->assertEquals(
			$stages,
			$mock->get_stages( true )
		);

		$this->assertEquals(
			$stages,
			get_transient( 'poc_foundation_bitrix24_stages' )
		);
	}

	public function get_mock()
	{
		return m::mock( Bitrix24_Data::class )->makePartial()->shouldAllowMockingProtectedMethods();
	}
}