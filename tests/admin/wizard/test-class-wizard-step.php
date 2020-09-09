<?php

use POC\Foundation\Admin\Wizard\Wizard_Step;

class Test_Class_Setup_Wizard extends \WP_UnitTestCase
{
	public $instance;

	public function setUp()
	{
		parent::setUp();

		$this->instance = new Wizard_Step();
	}

	public function test_get_steps()
	{
		$steps = $this->instance->get_steps();

		$this->assertEquals( 5, count( $steps ) );
		$this->assertArrayHasKey( 'intro', $steps );
		$this->assertArrayHasKey( 'license', $steps );
		$this->assertArrayHasKey( 'config', $steps );
		$this->assertArrayHasKey( 'plugins', $steps );
		$this->assertArrayHasKey( 'done', $steps );
	}
}