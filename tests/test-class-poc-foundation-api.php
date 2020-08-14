<?php

use POC\Foundation\POC_Foundation_API;

class Test_POC_Foundation_API extends \WP_UnitTestCase
{
	public $instance;

	public function setUp()
	{
		parent::setUp();

		$this->instance = new POC_Foundation_API();
	}
}