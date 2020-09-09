<?php

use POC\Foundation\LGS;

class Test_Class_LGS extends \WP_UnitTestCase
{
	public $instance;

	public function setUp()
	{
		$this->instance = new LGS();
	}
}