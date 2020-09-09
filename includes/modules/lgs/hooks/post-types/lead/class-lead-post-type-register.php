<?php

namespace POC\Foundation\Modules\LGS\Hooks\PostTypes\Lead;

use POC\Foundation\Contracts\Hook;

class Lead_Post_Type_Register implements Hook
{
	public function hooks()
	{
		add_action( 'init', array( $this, 'register' ) );
	}

	public function register()
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
}