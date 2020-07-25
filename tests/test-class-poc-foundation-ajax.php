<?php

use POC\Foundation\POC_Foundation_AJAX;
use POC\Foundation\POC_Foundation_License_Server;
use POC\Foundation\POC_Foundation_Plugin_Manager;
use Mockery as m;

class Test_Class_POC_Foundation_AJAX extends \WP_Ajax_UnitTestCase
{
	public $instance;

	public function setUp() {
		parent::setUp();

		$this->instance = new POC_Foundation_AJAX();
	}

	public function test_add_hooks()
	{
		$this->assertGreaterThan(
			0,
			has_action(
				'wp_ajax_poc_foundation_check_license_key',
				array( $this->instance, 'check_license_key' )
			)
		);

		$this->assertGreaterThan(
			0,
			has_action(
				'wp_ajax_nopriv_poc_foundation_check_license_key',
				array( $this->instance, 'check_license_key' )
			)
		);

		$this->assertGreaterThan(
			0,
			has_action(
				'wp_ajax_poc_foundation_setup_plugin',
				array( $this->instance, 'setup_plugin' )
			)
		);

		$this->assertGreaterThan(
			0,
			has_action(
				'wp_ajax_nopriv_poc_foundation_setup_plugin',
				array( $this->instance, 'setup_plugin' )
			)
		);

		$this->assertGreaterThan(
			0,
			has_action(
				'wp_ajax_poc_foundation_clear_update_cache',
				array( $this->instance, 'clear_update_cache' )
			)
		);

		$this->assertGreaterThan(
			0,
			has_action(
				'wp_ajax_nopriv_poc_foundation_clear_update_cache',
				array( $this->instance, 'clear_update_cache' )
			)
		);

		$this->assertGreaterThan(
			0,
			has_action(
				'wp_ajax_poc_foundation_save_config',
				array( $this->instance, 'save_config' )
			)
		);

		$this->assertGreaterThan(
			0,
			has_action(
				'wp_ajax_nopriv_poc_foundation_save_config',
				array( $this->instance, 'save_config' )
			)
		);
	}

	public function test_check_license_key()
	{
		$license_server_mock = m::mock( POC_Foundation_License_Server::class );
		$license_server_mock->shouldReceive( 'check' )->andReturnUsing( function ( $arg ) {
			if ( $arg === 'valid_license_key' ) {
				return array(
					'status' => 'Active'
				);
			}

			return array(
				'status' => 'Invalid'
			);
		} );

		$ajax_mock = m::mock( POC_Foundation_AJAX::class )->makePartial()->shouldAllowMockingProtectedMethods();
		$ajax_mock->shouldReceive( 'get_license_server' )->andReturn( $license_server_mock );

		$_POST['license_key'] = 'valid_license_key';

		$is_valid = array( 'is_valid' => true );

		$ajax_mock->shouldReceive( 'success_response' )->with( $is_valid )->andReturn( $is_valid );

		$this->assertEquals( $is_valid, $ajax_mock->check_license_key() );

		$_POST['license_key'] = 'invalid_license_key';

		$is_invalid = array( 'is_valid' => false );

		$ajax_mock->shouldReceive( 'success_response' )->with( $is_invalid )->andReturn( $is_invalid );

		$this->assertEquals( $is_invalid, $ajax_mock->check_license_key() );
	}

	public function test_setup_plugins()
	{
		$plugin_manager_mock = m::mock( POC_Foundation_Plugin_Manager::class )->makePartial()->shouldAllowMockingProtectedMethods();
		$plugin_manager_mock->shouldReceive( 'setup_plugin' )->once()->with( 'elementor-pro' )->andReturn( true );

		$ajax_mock = m::mock( POC_Foundation_AJAX::class )->makePartial()->shouldAllowMockingProtectedMethods();
		$ajax_mock->shouldReceive( 'get_plugin_manager' )->andReturn( $plugin_manager_mock );
		$ajax_mock->shouldReceive( 'success_response' )->andReturn( true );

		$_POST['slug'] = 'elementor-pro';

		$this->assertTrue( $ajax_mock->setup_plugin() );
	}

	public function test_save_config()
	{
		$_POST['poc_foundation_config'] = array(
			'foo' => 'bar',
			'john' => 'doe'
		);

		$mock = m::mock( POC_Foundation_AJAX::class )->makePartial()->shouldAllowMockingProtectedMethods();
		$mock->shouldReceive( 'success_response' )->andReturn( true );

		$this->assertTrue( $mock->save_config() );
		$this->assertEquals( 'bar', get_option( 'foo' ) );
		$this->assertEquals( 'doe', get_option( 'john' ) );

		delete_option( 'foo' );
		delete_option( 'john' );
	}
}