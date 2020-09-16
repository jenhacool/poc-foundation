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
			'menu_name'          => _x( 'Lead', 'admin menu', 'poc-foundation' ),
			'name_admin_bar'     => _x( 'Lead', 'add new on admin bar', 'poc-foundation' ),
			'add_new'            => _x( 'Add New', 'Lead', 'poc-foundation' ),
			'add_new_item'       => __( 'Add New Lead', 'poc-foundation' ),
			'new_item'           => __( 'New Lead', 'poc-foundation' ),
			'edit_item'          => __( 'Edit Lead', 'poc-foundation' ),
			'view_item'          => __( 'View Lead', 'poc-foundation' ),
			'all_items'          => __( 'All Leads', 'poc-foundation' ),
			'search_items'       => __( 'Search Lead', 'poc-foundation' ),
			'parent_item_colon'  => __( 'Parent Lead:', 'poc-foundation' ),
			'not_found'          => __( 'No leads found.', 'poc-foundation' ),
			'not_found_in_trash' => __( 'No leads found in Trash.', 'poc-foundation' )
		);

		$args = array(
			'labels'             => $labels,
			'description'        => __( 'For storing POC Foundation lead.', 'poc-foundation' ),
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