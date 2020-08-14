<?php

use POC\Foundation\POC_Foundation;
use Mockery as m;

class Test_Class_POC_Foundation extends \WP_UnitTestCase
{
	public $instance;

	public $user_id;

	public function setUp() {
		parent::setUp();

		$this->instance = POC_Foundation::instance();
	}

	public function tearDown()
	{
		m::close();
	}

	public function test_add_hooks()
	{
		$this->assertGreaterThan(
			0,
			has_filter(
				'wp_headers',
				array( $this->instance, 'modify_wp_headers' )
			)
		);
	}

	public function test_modify_wp_headers()
	{
		$default_headers = array( 'X-Frame-Options' => 'SAMEORIGIN' );

		update_option( 'poc_foundation_allowed_iframe_domain', 'foo.bar' );

		$headers = $this->instance->modify_wp_headers( $default_headers );

		$this->assertEquals( 'ALLOW-FROM foo.bar', $headers['X-Frame-Options'] );

		delete_option( 'poc_foundation_allowed_iframe_domain' );

		$headers = $this->instance->modify_wp_headers( $default_headers );

		$this->assertEquals( 'SAMEORIGIN', $headers['X-Frame-Options'] );
	}
}