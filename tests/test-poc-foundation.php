<?php

use POC\Foundation\POC_Foundation;

class Test_POC_Foundation extends \WP_UnitTestCase
{
	public function test_define_constant_variables()
	{
		$this->assertTrue( defined( 'POC_FOUNDATION_PLUGIN_FILE' ) );
		$this->assertTrue( defined( 'POC_FOUNDATION_PLUGIN_DIR' ) );
		$this->assertTrue( defined( 'POC_FOUNDATION_PLUGIN_URL' ) );
	}

	public function test_register_activation_hook()
	{
		$this->assertGreaterThan(
			0,
			has_action(
				'activate_' . plugin_basename( POC_FOUNDATION_PLUGIN_FILE ),
				array( POC_Foundation::class, 'activate' )
			)
		);
	}

	public function test_register_deactivation_hook()
	{
		$this->assertGreaterThan(
			0,
			has_action(
				'deactivate_' . plugin_basename( POC_FOUNDATION_PLUGIN_FILE ),
				array( POC_Foundation::class, 'deactivate' )
			)
		);
	}
}