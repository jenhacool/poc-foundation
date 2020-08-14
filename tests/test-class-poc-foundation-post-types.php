<?php

use POC\Foundation\POC_Foundation_Post_Types;

class Test_POC_Foundation_Post_Types extends \WP_UnitTestCase
{
	public $instance;

	public function setUp()
	{
		parent::setUp();

		$this->instance = new POC_Foundation_Post_Types();
	}

	public function test_add_hooks()
	{
		$this->assertGreaterThan(
			0,
			has_action(
				'init',
				array( $this->instance, 'register_post_types' )
			)
		);

		$this->assertGreaterThan(
			0,
			has_filter(
				'manage_poc_foundation_lead_posts_columns',
				array( $this->instance, 'lead_posts_columns_head' )
			)
		);

		$this->assertGreaterThan(
			0,
			has_action(
				'manage_poc_foundation_lead_posts_custom_column',
				array( $this->instance, 'lead_posts_columns_content' )
			)
		);

		$this->assertGreaterThan(
			0,
			has_filter(
				'bulk_actions-edit-poc_foundation_lead',
				array( $this->instance, 'lead_posts_bulk_actions' )
			)
		);

		$this->assertGreaterThan(
			0,
			has_filter(
				'handle_bulk_actions-edit-poc_foundation_lead',
				array( $this->instance, 'handle_lead_posts_bulk_actions' )
			)
		);
	}

	public function test_register_post_types()
	{
		global $wp_post_types;

		$this->assertTrue( ! isset( $wp_post_types['poc_foundation_lead'] ) );

		$this->instance->register_post_types();

		$this->assertTrue( isset( $wp_post_types['poc_foundation_lead'] ) );
	}

	public function test_lead_posts_columns_head()
	{
		$defaults = array();

		$defaults = $this->instance->lead_posts_columns_head( $defaults );

		$this->assertTrue( ! isset( $defaults['title'] ) );

		$this->assertTrue( isset( $defaults['name'] ) );
		$this->assertTrue( isset( $defaults['phone'] ) );
		$this->assertTrue( isset( $defaults['email'] ) );
		$this->assertTrue( isset( $defaults['submitted_on'] ) );
		$this->assertTrue( isset( $defaults['campaign_name'] ) );
		$this->assertTrue( isset( $defaults['ref_by'] ) );
	}

	public function test_lead_posts_columns_content()
	{
		$post_id = $this->factory->post->create();
		$page_id = $this->factory->post->create( array( 'post_type' => 'page' ) );
		$page_title = get_post( $page_id )->post_title;

		update_post_meta( $post_id, 'poc_foundation_lead_data', array(
			'name' => 'John Doe',
			'phone' => '01234567890',
			'email' => 'admin@gmail.com',
			'submitted_on_id' => $page_id,
			'submitted_on' => $page_title,
			'campaign_name' => 'Example Campaign',
			'ref_by' => 'flyforever123'
		) );

		ob_start();
		$this->instance->lead_posts_columns_content( 'name', $post_id );
		$this->assertEquals( 'John Doe', ob_get_clean() );

		ob_start();
		$this->instance->lead_posts_columns_content( 'phone', $post_id );
		$this->assertEquals( '<a href="tel:01234567890" target="_blank">01234567890</a>', ob_get_clean() );

		ob_start();
		$this->instance->lead_posts_columns_content( 'email', $post_id );
		$this->assertEquals( '<a href="mailto:admin@gmail.com" target="_blank">admin@gmail.com</a>', ob_get_clean() );

		ob_start();
		$this->instance->lead_posts_columns_content( 'submitted_on', $post_id );
		$this->assertEquals( '<a href="' . get_permalink( $page_id ) . '">' . $page_title . '</a>', ob_get_clean() );

		ob_start();
		$this->instance->lead_posts_columns_content( 'campaign_name', $post_id );
		$this->assertEquals( 'Example Campaign', ob_get_clean() );

		ob_start();
		$this->instance->lead_posts_columns_content( 'ref_by', $post_id );
		$this->assertEquals( 'flyforever123', ob_get_clean() );
	}

	public function test_lead_posts_bulk_actions()
	{
		$bulk_actions = $this->instance->lead_posts_bulk_actions( array() );

		$this->assertTrue( isset( $bulk_actions['poc_foundation_send_to_bitrix24'] ) );
		$this->assertEquals( __( 'Send to Bitrix24', 'poc-foundation' ), $bulk_actions['poc_foundation_send_to_bitrix24'] );
	}

	public function test_handle_lead_posts_bulk_actions()
	{

	}
}