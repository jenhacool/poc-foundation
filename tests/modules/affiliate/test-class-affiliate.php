<?php

use POC\Foundation\Modules\Affiliate\Affiliate;
use Mockery as m;

class Test_Class_Affiliate extends \WP_UnitTestCase
{
	public $instance;

	public function setUp()
	{
		parent::setUp();

		$this->instance = new Affiliate();
	}

	public function tearDown()
	{
		m::close();

		parent::tearDown();
	}
}