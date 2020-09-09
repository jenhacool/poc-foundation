<?php

use POC\Foundation\Modules\Bitrix24\Hooks\Bitrix24_AJAX;
use POC\Foundation\Modules\Bitrix24\Classes\Bitrix24_API;
use POC\Foundation\Modules\Bitrix24\Classes\Bitrix24_Data;
use Mockery as m;

class Test_Class_Bitrix24_AJAX extends \WP_UnitTestCase
{
	public $instance;

	public function setUp()
	{
		$this->instance = new Bitrix24_AJAX();
	}

	public function tearDown()
	{
		parent::tearDown();
	}

	public function test_hooks()
	{
		$this->instance->hooks();

		$this->assertGreaterThan(
			0,
			has_action(
				'wp_ajax_poc_foundation_create_bitrix24_deal',
				array( $this->instance, 'create_bitrix24_deal' )
			)
		);

		$this->assertGreaterThan(
			0,
			has_action(
				'wp_ajax_nopriv_poc_foundation_create_bitrix24_deal',
				array( $this->instance, 'create_bitrix24_deal' )
			)
		);

		$this->assertGreaterThan(
			0,
			has_action(
				'wp_ajax_poc_foundation_reload_bitrix24_stages',
				array( $this->instance, 'reload_bitrix24_stages' )
			)
		);

		$this->assertGreaterThan(
			0,
			has_action(
				'wp_ajax_nopriv_poc_foundation_reload_bitrix24_stages',
				array( $this->instance, 'reload_bitrix24_stages' )
			)
		);
	}

	public function test_create_bitrix24_deal()
	{
		$post_id = $this->factory->post->create();

		update_post_meta( $post_id, 'name', 'John Doe' );
		update_post_meta( $post_id, 'phone', '01234567890' );
		update_post_meta( $post_id, 'email', 'admin@gmail.com' );
		update_post_meta( $post_id, 'campaign_name', 'Example Campaign' );
		update_post_meta( $post_id, 'ref_by', 'flyforever123' );

		$_POST['post_id'] = $post_id;
		$_POST['stage_id'] = 'C3:NEW';

		$api_mock = m::mock( Bitrix24_API::class )->makePartial()->shouldAllowMockingProtectedMethods();
		$api_mock->shouldReceive( 'add_contact' )
		         ->once()
		         ->with( array(
			         'fields' => array(
				         'NAME' => 'John Doe',
				         'SECOND_NAME' => '_',
				         'LAST_NAME' => '_',
				         'PHONE' => '01234567890',
				         'EMAIL' => 'admin@gmail.com'
			         )
		         ) )
		         ->andReturn( array( '1' ) );
		$api_mock->shouldReceive( 'add_deal' )
		         ->once()
		         ->with( array(
			         'fields' => array(
				         'TITLE' => 'New deal for John Doe',
				         'STAGE_ID' => 'C3:NEW',
				         'CONTACT_ID' => '1',
				         'CATEGORY_ID' => 3
			         )
		         ) )
		         ->andReturn( array() );

		$mock = m::mock( Bitrix24_AJAX::class )->makePartial()->shouldAllowMockingProtectedMethods();
		$mock->shouldReceive( 'get_bitrix24_api' )->twice()->andReturn( $api_mock );
		$mock->shouldReceive( 'success_response' )->once()->andReturn( true );

		$this->assertTrue( $mock->create_bitrix24_deal() );
		$this->assertEquals( 'sent', get_post_meta( $post_id, 'bitrix24_status', true ) );
	}

	public function test_reload_bitrix24_stages()
	{
		$data_mock = m::mock( Bitrix24_Data::class )->makePartial()->shouldAllowMockingProtectedMethods();
		$data_mock->shouldReceive( 'get_stages' )->once()->with( true )->andReturn( array( 'foo' => 'bar' ) );

		$mock = m::mock( Bitrix24_AJAX::class )->makePartial()->shouldAllowMockingProtectedMethods();
		$mock->shouldReceive( 'get_bitrix24_data' )->once()->andReturn( $data_mock );
		$mock->shouldReceive( 'success_response' )->once()->andReturn( true );

		$this->assertTrue( $mock->reload_bitrix24_stages() );
	}
}