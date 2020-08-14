<?php

use POC\Foundation\POC_Foundation_LGS;

class Test_POC_Foundation_LGS extends \WP_UnitTestCase
{
	public $instance;

	public function setUp()
	{
		$this->instance = new POC_Foundation_LGS();
	}

	public function test_add_hooks()
	{
		$this->assertGreaterThan(
			0,
			has_action(
				'elementor_pro/init',
				array( $this->instance, 'add_elementor_form_action' )
			)
		);

		$this->assertGreaterThan(
			0,
			has_action(
				'elementor/dynamic_tags/register_tags',
				array( $this->instance, 'register_dynamic_tags' )
			)
		);
	}
}