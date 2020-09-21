<?php

namespace POC\Foundation\Modules\Bitrix24\Hooks;

use POC\Foundation\Contracts\Hook;

class Bitrix24_Lead_Listing implements Hook
{
	public function hooks()
	{
	    add_filter( 'bulk_actions-edit-poc_foundation_lead', array( $this, 'bulk_actions' ) );

		add_filter( 'manage_poc_foundation_lead_posts_columns', array( $this, 'columns_head' ), 10 );

		add_action( 'manage_poc_foundation_lead_posts_custom_column', array( $this, 'columns_content' ), 10, 2 );

		add_action( 'restrict_manage_posts', array( $this, 'custom_filter_select' ) );

		add_filter( 'parse_query', array( $this, 'custom_filter_query' ) );
	}

	public function bulk_actions( $bulk_actions )
	{
		$bulk_actions['send_bitrix24'] = __( 'Send to Bitrix24', 'poc-foundation' );

		return $bulk_actions;
	}

	public function columns_head( $defaults )
	{
		$defaults['bitrix24_status'] = 'Bitrix24 Status';

		return $defaults;
	}

	public function columns_content( $column_name, $post_id )
	{
		if ( $column_name != 'bitrix24_status' ) {
			return;
		}

		$bitrix24_status = get_post_meta( $post_id, 'bitrix24_status', true );

		if ( empty( $bitrix24_status ) ) {
			echo 'Unscheduled';
			return;
		}

		echo ucfirst( $bitrix24_status );
		return;
	}

	public function custom_filter_select()
	{
		global $typenow;

		if ( $typenow != 'poc_foundation_lead' ) {
			return;
		}

		$options = array(
            'unscheduled' => __( 'Unscheduled', 'poc-foundation' ),
			'sent' => __( 'Sent', 'poc-foundation' )
        );

		$selected = isset( $_GET['bitrix24_status'] ) ? $_GET['bitrix24_status'] : '';

		?>
			<select name="bitrix24_status" id="bitrix24_status">
				<option value="all" <?php selected( 'all', $selected ); ?>><?php echo __( 'All Bitrix24 Status', 'poc-foundation' ); ?></option>
				<?php foreach ( $options as $value => $text ) : ?>
					<option value="<?php echo esc_attr( $value ); ?>" <?php selected( $value, $selected ); ?>><?php echo $text; ?></option>
				<?php endforeach; ?>
			</select>
		<?php
	}

	public function custom_filter_query( $query )
	{
		global $pagenow;

		$post_type = isset( $_GET['post_type'] ) ? $_GET['post_type'] : '';

		if ( ! is_admin() || $pagenow != 'edit.php' || $post_type != 'poc_foundation_lead' || ! isset( $_GET['bitrix24_status'] ) || $_GET['bitrix24_status'] === 'all' ) {
			return $query;
		}

		if ( $_GET['bitrix24_status'] === 'sent' ) {
			$query->query_vars['meta_query'] = array(
			    array(
				    'key' => 'bitrix24_status',
				    'value' => 'sent',
				    'compare' => '=',
                )
            );

			return $query;
		}

	    $query->query_vars['meta_query'] = array(
            'relation' => 'OR',
            array(
                'key' => 'bitrix24_status',
                'compare' => 'NOT EXISTS'
            ),
            array(
	            'key' => 'bitrix24_status',
	            'value' => 'sent',
	            'compare' => '!='
            )
        );

		return $query;
	}
}