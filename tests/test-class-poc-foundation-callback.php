<?php

use POC\Foundation\POC_Foundation_Callback;
use Mockery as m;
use POC\Foundation\Admin\ElementorPro\POC_Foundation_Elementor_Pro;
use POC\Foundation\Admin\ElementorPro\POC_Foundation_Elementor_Pro_App;

class Test_POC_Foundation_Callback extends \WP_UnitTestCase
{
	public $instance;

	public function setUp()
	{
		$this->instance = new POC_Foundation_Callback();
	}

	public function tearDown() {
		m::close();

		parent::tearDown();
	}

	public function test_add_hooks()
	{
		$this->assertGreaterThan(
			0,
			has_action(
				'init',
				array( $this->instance, 'handle' )
			)
		);
	}

	public function test_action_install_elementor_pro()
	{
		$_GET['download_link'] = 'http://foo.bar';

		$elementor_pro_mock = m::mock( POC_Foundation_Elementor_Pro::class )->makePartial()->shouldAllowMockingProtectedMethods();
		$elementor_pro_mock->shouldReceive( 'install' )->once()->with( $_GET['download_link'] )->andReturn( true );

		$mock = m::mock( POC_Foundation_Callback::class )->makePartial()->shouldAllowMockingProtectedMethods();
		$mock->shouldReceive( 'get_elementor_pro_handler' )->once()->andReturn( $elementor_pro_mock );

		$this->assertNull( $mock->action_install_elementor_pro() );
	}

	public function test_action_activate_elementor_pro()
	{
		$_GET['nonce'] = 'nonce';
		$_GET['state'] = 'state';
		$_GET['code'] = 'code';

		$app_mock = m::mock( POC_Foundation_Elementor_Pro_App::class )->makePartial();
		$app_mock->shouldReceive( 'activate' )->once()->with( array (
			'nonce' => 'nonce',
			'state' => 'state',
			'code' => 'code'
		) )->andReturn( true );

		$elementor_pro_mock = m::mock( POC_Foundation_Elementor_Pro::class )->makePartial()->shouldAllowMockingProtectedMethods();
		$elementor_pro_mock->shouldReceive( 'get_elementor_pro_app' )->andReturn( $app_mock );

		$mock = m::mock( POC_Foundation_Callback::class )->makePartial()->shouldAllowMockingProtectedMethods();
		$mock->shouldReceive( 'get_elementor_pro_handler' )->once()->andReturn( $elementor_pro_mock );

		$this->assertNull( $mock->action_activate_elementor_pro() );
	}
}