<?php

use POC\Foundation\Modules\Bitrix24\Hooks\Bitrix24_Admin;

class Test_Class_Bitrix24_Admin extends \WP_UnitTestCase
{
	public $instance;

	public function setUp()
	{
		$this->instance = new Bitrix24_Admin();
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
			has_filter(
				'poc_foundation_admin_settings_tabs',
				array( $this->instance, 'settings_tab' )
			)
		);

		$this->assertGreaterThan(
			0,
			has_action(
				'admin_footer',
				array( $this->instance, 'bitrix24_dialog' )
			)
		);

		$this->assertGreaterThan(
			0,
			has_action(
				'admin_enqueue_scripts',
				array( $this->instance, 'enqueue_scripts' )
			)
		);
	}
}