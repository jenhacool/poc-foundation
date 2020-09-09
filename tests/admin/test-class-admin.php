<?php

use POC\Foundation\Admin\Admin;
use Mockery as m;

class Test_Class_Admin extends \WP_UnitTestCase
{
	public $instance;

	public function setUp()
	{
		$this->instance = new Admin();
	}

	public function tearDown() {
		m::close();

		parent::tearDown();
	}
}