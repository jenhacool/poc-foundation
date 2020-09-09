<?php

use POC\Foundation\Modules\LGS\Hooks\PostTypes\Lead\Lead_Post_Type_Bulk_Actions;

class Test_Class_Lead_Post_Type_Bulk_Actions extends \WP_UnitTestCase
{
	public $instance;

	public function setUp()
	{
		$this->instance = new Lead_Post_Type_Bulk_Actions();
	}
}