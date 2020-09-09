<?php

use POC\Foundation\Modules\LGS\Hooks\PostTypes\Lead\Lead_Post_Type_Register;

class Test_Class_Lead_Post_Type_Register extends \WP_UnitTestCase
{
	public $instance;

	public function setUp()
	{
		$this->instance = new Lead_Post_Type_Register();
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
				'init',
				array( $this->instance, 'register' )
			)
		);
	}

	public function test_register()
	{
		global $wp_post_types;

		$this->assertTrue( isset( $wp_post_types['poc_foundation_lead'] ) );
	}
}