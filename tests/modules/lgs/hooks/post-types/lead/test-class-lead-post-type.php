<?php

use POC\Foundation\PostType\Lead_Post_Type;

class Test_Class_Lead_Post_Type extends \WP_UnitTestCase
{
	public $instance;

	public function setUp()
	{
		parent::setUp();

		$this->instance = new Lead_Post_Type();
	}
}