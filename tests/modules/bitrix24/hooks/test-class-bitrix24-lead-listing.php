<?php

use POC\Foundation\Modules\Bitrix24\Hooks\Bitrix24_Lead_Listing;

class Test_Class_Bitrix24_Lead_Listing extends \WP_UnitTestCase
{
	public $instance;

	public function setUp()
	{
		$this->instance = new Bitrix24_Lead_Listing();
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
				'bulk_actions-edit-poc_foundation_lead',
				array( $this->instance, 'bulk_actions' )
			)
		);

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
			has_action(
				'restrict_manage_posts',
				array( $this->instance, 'custom_filter_select' )
			)
		);

		$this->assertGreaterThan(
			0,
			has_filter(
				'parse_query',
				array( $this->instance, 'custom_filter_query' )
			)
		);
	}

	public function test_bulk_actions()
	{
		$bulk_actions = $this->instance->bulk_actions( array() );

		$this->assertTrue( isset( $bulk_actions['send_bitrix24'] ) );
		$this->assertEquals( __( 'Send to Bitrix24', 'poc-foundation' ), $bulk_actions['send_bitrix24'] );
	}

	public function test_columns_head()
	{
		$defaults = array();

		$defaults = $this->instance->columns_head( $defaults );

		$this->assertTrue( isset( $defaults['bitrix24_status'] ) );
	}

	public function test_columns_content()
	{
		$post_id = $this->factory->post->create();

		ob_start();
		$this->instance->columns_content( 'bitrix24_status', $post_id );
		$this->assertEquals( 'Unscheduled', ob_get_clean() );

		update_post_meta( $post_id, 'poc_foundation_bitrix24_status', 'scheduled' );

		ob_start();
		$this->instance->columns_content( 'bitrix24_status', $post_id );
		$this->assertEquals( 'Scheduled', ob_get_clean() );

		update_post_meta( $post_id, 'poc_foundation_bitrix24_status', 'sent' );

		ob_start();
		$this->instance->columns_content( 'bitrix24_status', $post_id );
		$this->assertEquals( 'Sent', ob_get_clean() );
	}

	public function test_custom_filter_query()
	{
		global $pagenow;

		$pagenow = 'edit.php';

		$_GET = array(
			'post_type'	=> 'poc_foundation_lead',
			'bitrix24_status' => 'sent'
		);

		set_current_screen( 'edit-post' );

		$query = $this->instance->custom_filter_query( new \WP_Query() );

		$this->assertArrayHasKey( 'meta_key', $query->query_vars );
		$this->assertEquals( 'poc_foundation_bitrix24_status', $query->query_vars['meta_key'] );

		$this->assertArrayHasKey( 'meta_value', $query->query_vars );
		$this->assertEquals( 'sent', $query->query_vars['meta_value'] );

		$this->assertArrayHasKey( 'meta_compare', $query->query_vars );
		$this->assertEquals( '=', $query->query_vars['meta_compare'] );
	}
}