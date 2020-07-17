<?php

use POC\Foundation\Admin\POC_Foundation_Setup_Wizard;

class Test_POC_Foundation_Setup_Wizard extends \WP_UnitTestCase
{
	public $instance;

	public function setUp()
	{
		parent::setUp();

		$this->instance = new POC_Foundation_Setup_Wizard();
	}

	public function test_get_steps()
	{
		$steps = $this->instance->get_steps();

		$this->assertEquals( 5, count( $steps ) );
		$this->assertArrayHasKey( 'intro', $steps );
		$this->assertArrayHasKey( 'license', $steps );
		$this->assertArrayHasKey( 'plugins', $steps );
		$this->assertArrayHasKey( 'config', $steps );
		$this->assertArrayHasKey( 'done', $steps );
	}
}