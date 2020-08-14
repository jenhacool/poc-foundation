<?php

namespace POC\Foundation;

class POC_Foundation_Post_Types
{
	public function __construct()
	{
		$this->add_hooks();
	}

	protected function add_hooks()
	{
		add_action( 'init', array( $this, 'register_post_types' ) );

		add_filter( 'manage_poc_foundation_lead_posts_columns', array( $this, 'lead_posts_columns_head' ), 10 );

		add_action( 'manage_poc_foundation_lead_posts_custom_column', array( $this, 'lead_posts_columns_content' ), 10, 2 );

		add_filter( 'bulk_actions-edit-poc_foundation_lead', array( $this, 'lead_posts_bulk_actions' ) );

		add_filter( 'handle_bulk_actions-edit-poc_foundation_lead', array( $this, 'handle_lead_posts_bulk_actions' ), 10, 3 );
	}

	public function register_post_types()
	{
		$labels = array(
			'name'               => _x( 'POC Foundation Leads', 'post type general name', 'poc-foundation' ),
			'singular_name'      => _x( 'Lead', 'post type singular name', 'poc-foundation' ),
			'menu_name'          => _x( 'Elementor DB', 'admin menu', 'poc-foundation' ),
			'name_admin_bar'     => _x( 'Elementor DB', 'add new on admin bar', 'poc-foundation' ),
			'add_new'            => _x( 'Add New', 'Elementor DB', 'poc-foundation' ),
			'add_new_item'       => __( 'Add New Elementor DB', 'poc-foundation' ),
			'new_item'           => __( 'New Elementor DB', 'poc-foundation' ),
			'edit_item'          => __( 'Edit Elementor DB', 'poc-foundation' ),
			'view_item'          => __( 'View Elementor DB', 'poc-foundation' ),
			'all_items'          => __( 'All Elementor DB', 'poc-foundation' ),
			'search_items'       => __( 'Search Elementor DB', 'poc-foundation' ),
			'parent_item_colon'  => __( 'Parent Elementor DB:', 'poc-foundation' ),
			'not_found'          => __( 'No contact form submissions found.', 'poc-foundation' ),
			'not_found_in_trash' => __( 'No contact form submissions found in Trash.', 'poc-foundation' )
		);

		$args = array(
			'labels'             => $labels,
			'description'        => __( 'For storing Elementor contact form submissions.', 'poc-foundation' ),
			'public'             => false,
			'publicly_queryable' => false,
			'show_ui'            => true,
			'show_in_menu'       => false,
			'query_var'          => true,
			'rewrite'            => false,
			'capability_type'    => 'post',
			'has_archive'        => false,
			'hierarchical'       => false,
			'menu_position'      => null,
			'menu_icon'          => 'dashicons-admin-comments',
			'supports'           => array( 'title' )
		);

		register_post_type( 'poc_foundation_lead', $args );
	}

	public function lead_posts_columns_head( $defaults )
	{
		unset( $defaults['title'] );

		$defaults['name'] = 'Name';
		$defaults['phone'] = 'Phone';
		$defaults['email'] = 'Email';
		$defaults['submitted_on'] = 'Submitted On';
		$defaults['campaign_name'] = 'Campaign';
		$defaults['ref_by'] = 'Ref By';

		return $defaults;
	}

	public function lead_posts_columns_content( $column_name, $post_id )
	{
		$lead = get_post( $post_id );
		$lead_data = get_post_meta( $post_id, 'poc_foundation_lead_data', true );

		switch ( $column_name ) {
			case 'phone' :
				$phone = $lead_data['phone'];
				$content = '<a href="tel:' . $phone . '" target="_blank">' . $phone . '</a>';
				break;

			case 'email' :
				$email = $lead_data['email'];
				$content = '<a href="mailto:' . $email . '" target="_blank">' . $email . '</a>';
				break;

			case 'submitted_on' :
				$page_id = $lead_data['submitted_on_id'];
				$page_title = $lead_data['submitted_on'];
				$content = '<a href="' . get_permalink( $page_id ) . '">' . $page_title . '</a>';
				break;

			default :
				$content = $lead_data[$column_name];
				break;
		}

		echo $content;
	}

	public function lead_posts_bulk_actions( $bulk_actions )
	{
		$bulk_actions['poc_foundation_send_to_bitrix24'] = __( 'Send to Bitrix24', 'poc-foundation' );

		return $bulk_actions;
	}
}