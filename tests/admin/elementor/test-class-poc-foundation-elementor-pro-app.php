<?php

use POC\Foundation\Admin\ElementorPro\POC_Foundation_Elementor_Pro_App;
use Mockery as m;

class Test_Class_POC_Foundation_Elementor_Pro_App extends \WP_UnitTestCase
{
	public function tearDown()
	{
		m::close();

		parent::tearDown();
	}

	public function test_activate()
	{
		$params = array(
			'nonce' => 'fake_nonce',
			'state' => 'fake_state',
			'code' => 'fake_code'
		);

		$mock = m::mock( POC_Foundation_Elementor_Pro_App::class )->makePartial()->shouldAllowMockingProtectedMethods();

		$mock->shouldReceive( 'get_token' )
		     ->with( $params )
		     ->once()->andReturn( array( 'foo' => 'bar' ) );

		$this->assertTrue( $mock->activate( $params ) );
	}
}