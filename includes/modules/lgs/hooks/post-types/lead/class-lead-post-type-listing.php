<?php

namespace POC\Foundation\Modules\LGS\Hooks\PostTypes\Lead;

use POC\Foundation\Contracts\Hook;

class Lead_Post_Type_Listing implements Hook
{
	public function hooks()
	{
		add_filter( 'manage_poc_foundation_lead_posts_columns', array( $this, 'columns_head' ), 10 );

		add_action( 'manage_poc_foundation_lead_posts_custom_column', array( $this, 'columns_content' ), 10, 2 );
	}

	public function columns_head( $defaults )
	{
		unset( $defaults['title'] );

		$defaults['name'] = 'Name';
		$defaults['phone'] = 'Phone';
		$defaults['email'] = 'Email';
		$defaults['campaign_name'] = 'Campaign';
		$defaults['ref_by'] = 'Ref By';

		return $defaults;
	}

	public function columns_content( $column_name, $post_id )
	{
		switch ( $column_name ) {
			case 'phone' :
				$phone = get_post_meta( $post_id, 'phone', true );
				$content = '<a href="tel:' . $phone . '" target="_blank">' . $phone . '</a>';
				break;

			case 'email' :
				$email = get_post_meta( $post_id, 'email', true );
				$content = '<a href="mailto:' . $email . '" target="_blank">' . $email . '</a>';
				break;

			default :
				$content = get_post_meta( $post_id, $column_name, true );
				break;
		}

		echo $content;
	}
}
