<?php

use POC\Foundation\Modules\Affiliate\Hooks\Affiliate_Admin;

class Test_Affiliate_Admin extends \WP_UnitTestCase
{
	public $instance;

	public function setUp()
	{
		parent::setUp();

		$this->instance = new Affiliate_Admin();
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
			has_filter(
				'poc_foundation_admin_submenu_pages',
				array( $this->instance, 'submenu_pages' )
			)
		);
	}
}