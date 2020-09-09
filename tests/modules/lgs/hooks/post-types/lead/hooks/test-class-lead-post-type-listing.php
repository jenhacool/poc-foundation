<?php

use POC\Foundation\Modules\LGS\Hooks\PostTypes\Lead\Lead_Post_Type_Listing;

class Test_Class_Lead_Post_Type_Listing extends \WP_UnitTestCase
{
	public $instance;

	public function setUp()
	{
		$this->instance = new Lead_Post_Type_Listing();
	}

	public function tearDown()
	{
		parent::tearDown();
	}

	public function test_hooks()
	{
		$this->instance->hooks();

		$this->assertGreaterThan(
			0,
			has_filter(
				'manage_poc_foundation_lead_posts_columns',
				array( $this->instance, 'columns_head' )
			)
		);

		$this->assertGreaterThan(
			0,
			has_action(
				'manage_poc_foundation_lead_posts_custom_column',
				array( $this->instance, 'columns_content' )
			)
		);

		$this->assertGreaterThan(
			0,
			has_filter(
				'views_edit-poc_foundation_lead',
				array( $this->instance, 'custom_list_table' )
			)
		);
	}

	public function test_columns_head()
	{
		$defaults = array();

		$defaults = $this->instance->columns_head( $defaults );

		$this->assertTrue( ! isset( $defaults['title'] ) );

		$this->assertTrue( isset( $defaults['name'] ) );
		$this->assertTrue( isset( $defaults['phone'] ) );
		$this->assertTrue( isset( $defaults['email'] ) );
		$this->assertTrue( isset( $defaults['campaign_name'] ) );
		$this->assertTrue( isset( $defaults['ref_by'] ) );
	}

	public function test_columns_content()
	{
		$post_id = $this->factory->post->create();

		update_post_meta( $post_id, 'name', 'John Doe' );
		update_post_meta( $post_id, 'phone', '01234567890' );
		update_post_meta( $post_id, 'email', 'admin@gmail.com' );
		update_post_meta( $post_id, 'campaign_name', 'Example Campaign' );
		update_post_meta( $post_id, 'ref_by', 'flyforever123' );

		ob_start();
		$this->instance->columns_content( 'name', $post_id );
		$this->assertEquals( 'John Doe', ob_get_clean() );

		ob_start();
		$this->instance->columns_content( 'phone', $post_id );
		$this->assertEquals( '<a href="tel:01234567890" target="_blank">01234567890</a>', ob_get_clean() );

		ob_start();
		$this->instance->columns_content( 'email', $post_id );
		$this->assertEquals( '<a href="mailto:admin@gmail.com" target="_blank">admin@gmail.com</a>', ob_get_clean() );

		ob_start();
		$this->instance->columns_content( 'campaign_name', $post_id );
		$this->assertEquals( 'Example Campaign', ob_get_clean() );

		ob_start();
		$this->instance->columns_content( 'ref_by', $post_id );
		$this->assertEquals( 'flyforever123', ob_get_clean() );
	}
}