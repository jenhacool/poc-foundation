<?php

use POC\Foundation\API;

class Test_Class_API extends \WP_UnitTestCase
{
	public $instance;

	public function setUp()
	{
		parent::setUp();

		$this->instance = new API();
	}
}