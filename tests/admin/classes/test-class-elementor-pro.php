<?php

use Mockery as m;
use POC\Foundation\Admin\Classes\Elementor_Pro;
use POC\Foundation\Admin\Classes\Plugin_Manager;

class Test_Class_Elementor_Pro extends \WP_UnitTestCase
{
	public $instance;

	public function setUp()
	{
		parent::setUp();

		$plugin_manager = new Plugin_Manager();
		$this->instance = new Elementor_Pro( $plugin_manager );
	}

	public function tearDown()
	{
		m::close();

		parent::tearDown();
	}

	public function test_is_license_valid() {
		set_current_screen( 'edit-post' );

		update_option( 'elementor_pro_license_key', '' );

		$this->assertTrue( ! $this->instance->is_license_valid() );

		delete_option( 'elementor_pro_license_key' );

		$this->assertTrue( ! $this->instance->is_license_valid() );

		$mock = m::mock( Elementor_Pro::class )->makePartial()->shouldAllowMockingProtectedMethods();
		$mock->shouldReceive( 'get_license_data' )->andReturn( '' );

		$this->assertTrue( ! $mock->is_license_valid() );

		$mock->shouldReceive( 'get_license_data' )->andReturn( 'abc' );
		$mock->shouldReceive( 'is_license_active' )->andReturn( false );

		$this->assertTrue( ! $mock->is_license_valid() );
	}

	public function test_get_download_link()
	{
		$mock = m::mock( Elementor_Pro::class )->makePartial()->shouldAllowMockingProtectedMethods();
		$mock->shouldReceive( 'call_api' )->once()->with( 'get_download_link' )->andReturn( array( 'download_link' => 'http://foo.bar' ) );

		$this->assertEquals( 'http://foo.bar', $mock->get_download_link() );
	}

	public function test_get_license_key()
	{
		$mock = m::mock( Elementor_Pro::class )->makePartial()->shouldAllowMockingProtectedMethods();
		$mock->shouldReceive( 'call_api' )->once()->with( 'get_license_key' )->andReturn( array( 'license_key' => 'abc_def' ) );

		$this->assertEquals( 'abc_def', $mock->get_license_key() );
	}
}