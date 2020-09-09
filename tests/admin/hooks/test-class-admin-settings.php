<?php

use POC\Foundation\Admin\Hooks\Admin_Settings;
use Mockery as m;

class Test_Class_Admin_Settings extends \WP_UnitTestCase
{
	public $instance;

	public function setUp()
	{
		parent::setUp();

		$this->instance = new Admin_Settings();
	}

	public function tearDown()
	{
		delete_option( 'poc_foundation' );

		m::close();

		parent::tearDown();
	}

	public function test_add_hooks()
	{
		$this->instance->hooks();

		$this->assertGreaterThan(
			0,
			has_action(
				'admin_init',
				array( $this->instance, 'save_settings' )
			)
		);
	}

	public function test_save_settings()
	{
		update_option( 'poc_foundation', serialize( array(
			'foo' => 'bar'
		) ) );

		$_POST['poc_foundation'] = array(
			'john' => 'doe'
		);

		$this->instance->save_settings();

		$settings = unserialize( get_option( 'poc_foundation' ) );

		$this->assertArrayHasKey( 'john', $settings );
		$this->assertEquals( 'doe', $settings['john'] );
	}
}