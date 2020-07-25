<?php

use Mockery as m;
use POC\Foundation\Admin\ElementorPro\POC_Foundation_Elementor_Pro;
use POC\Foundation\Admin\ElementorPro\POC_Foundation_Elementor_Pro_App;
use POC\Foundation\POC_Foundation_Plugin_Manager;

class Test_Class_POC_Foundation_Elementor_Pro extends \WP_UnitTestCase
{
	public $instance;

	public function setUp()
	{
		parent::setUp();

		$plugin_manager = new POC_Foundation_Plugin_Manager();
		$this->instance = new POC_Foundation_Elementor_Pro( $plugin_manager );
	}

	public function tearDown()
	{
		m::close();

		parent::tearDown();
	}

	public function test_waiting_get_download_link()
	{
		$mock = m::mock( POC_Foundation_Elementor_Pro::class )->makePartial()->shouldAllowMockingProtectedMethods();
		$mock->shouldReceive( 'get_install_callback' )->once()->andReturn( 'http://foo.bar' );
		$mock->shouldReceive( 'call_api' )->once()->with( 'get_download_link', array( 'callback' => 'http://foo.bar' ) )->andReturn( array( 'status' => 'processing' ) );

		$this->assertTrue( $mock->waiting_get_download_link() );
	}

	public function test_waiting_get_activate_license_params()
	{
		$mock = m::mock( POC_Foundation_Elementor_Pro::class )->makePartial()->shouldAllowMockingProtectedMethods();
		$mock->shouldReceive( 'get_remote_authorize_url' )->once()->andReturn( 'http://example.php' );
		$mock->shouldReceive( 'get_activate_license_callback' )->once()->andReturn( 'http://foo.bar' );
		$mock->shouldReceive( 'call_api' )->once()->with( 'activate_elementor_pro', array(
			'url' => 'http://example.php',
			'callback' => 'http://foo.bar'
		) )->andReturn( array( 'status' => 'processing' ) );

		$this->assertTrue( $mock->waiting_get_activate_license_params() );
	}

	public function test_is_license_valid()
	{
		set_current_screen( 'edit-post' );

		update_option( 'elementor_pro_license_key', '' );

		$this->assertTrue( ! $this->instance->is_license_valid() );

		delete_option( 'elementor_pro_license_key' );

		$this->assertTrue( ! $this->instance->is_license_valid() );

		$mock = m::mock( POC_Foundation_Elementor_Pro::class )->makePartial()->shouldAllowMockingProtectedMethods();
		$mock->shouldReceive( 'get_license_data' )->andReturn( '' );

		$this->assertTrue( ! $mock->is_license_valid() );

		$mock->shouldReceive( 'get_license_data' )->andReturn( 'abc' );
		$mock->shouldReceive( 'is_license_active' )->andReturn( false );

		$this->assertTrue( ! $mock->is_license_valid() );
	}
}