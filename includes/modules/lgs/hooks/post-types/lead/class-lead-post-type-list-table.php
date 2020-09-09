<?php

namespace POC\Foundation\Modules\LGS\Hooks\PostTypes\Lead;

if ( ! class_exists( 'WP_List_Table' ) ) {
	require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}

if ( ! class_exists( 'WP_Posts_List_Table' ) ) {
	require_once( ABSPATH . 'wp-admin/includes/class-wp-posts-list-table.php' );
}

class List_Table extends \WP_Posts_List_Table
{
	public function inline_edit()
	{
	    parent::inline_edit();
		ob_start(); ?>
		    <h2>ababasba</h2>
		<?php echo ob_get_clean();
	}
}