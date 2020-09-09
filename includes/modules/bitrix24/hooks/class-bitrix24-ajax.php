<?php

namespace POC\Foundation\Modules\Bitrix24\Hooks;

use POC\Foundation\Contracts\Hook;
use POC\Foundation\Modules\Bitrix24\Classes\Bitrix24_API;
use POC\Foundation\Modules\Bitrix24\Classes\Bitrix24_Data;

class Bitrix24_AJAX implements Hook
{
	public $bitrix24_api;

	public $bitrix24_data;

	public function hooks()
	{
		add_action( 'wp_ajax_poc_foundation_create_bitrix24_deal', array( $this, 'create_bitrix24_deal' ) );

		add_action( 'wp_ajax_nopriv_poc_foundation_create_bitrix24_deal', array( $this, 'create_bitrix24_deal' ) );

		add_action( 'wp_ajax_poc_foundation_reload_bitrix24_stages', array( $this, 'reload_bitrix24_stages' ) );

		add_action( 'wp_ajax_nopriv_poc_foundation_reload_bitrix24_stages', array( $this, 'reload_bitrix24_stages' ) );
	}

	public function create_bitrix24_deal()
	{
		$post_id = $_POST['post_id'];

		$add_contact = $this->get_bitrix24_api()->add_contact(
			$this->get_contact_data( $post_id )
		);

		if ( is_null( $add_contact ) ) {
			return $this->error_response();
		}

		$contact_id = $add_contact[0];

		$stage_id = $_POST['stage_id'];

		$add_deal = $this->get_bitrix24_api()->add_deal(
			$this->get_deal_data( $post_id, $contact_id, $stage_id )
		);

		if ( is_null( $add_deal ) ) {
			return $this->error_response();
		}

		update_post_meta( $post_id, 'bitrix24_status', 'sent' );

		return $this->success_response();
	}

	public function reload_bitrix24_stages()
	{
		$stages = $this->get_bitrix24_data()->get_stages( true );

		ob_start(); ?>
				<?php foreach ( $stages as $id => $stage ) : ?>
					<option value="<?php echo $id; ?>"><?php echo $stage; ?></option>
				<?php endforeach; ?>
		<?php
		$html = ob_get_clean();

		return $this->success_response( array(
			'html' => $html
		) );
	}

	protected function get_contact_data( $post_id )
	{
		return array(
			'fields' => array(
				'NAME' => get_post_meta( $post_id, 'name', true ),
				'SECOND_NAME' => '_',
				'LAST_NAME' => '_',
				'PHONE' => get_post_meta( $post_id, 'phone', true ),
				'EMAIL' => get_post_meta( $post_id, 'email', true )
			)
		);
	}

	protected function get_deal_data( $post_id, $contact_id, $stage_id )
	{
		return array(
			'fields' => array(
				'TITLE' => 'New deal for ' . get_post_meta( $post_id, 'name', true ),
				'STAGE_ID' => $stage_id,
				'CONTACT_ID' => $contact_id,
				'CATEGORY_ID' => str_replace('C', '', explode( ':', $stage_id )[0] )
			)
		);
	}

	protected function get_bitrix24_api()
	{
		if ( is_null( $this->bitrix24_api ) || ! $this->bitrix24_api instanceof Bitrix24_API ) {
			$this->bitrix24_api = new Bitrix24_API();
		}

		return $this->bitrix24_api;
	}

	protected function get_bitrix24_data()
	{
		if ( is_null( $this->bitrix24_data ) || ! $this->bitrix24_data instanceof Bitrix24_Data ) {
			$this->bitrix24_data = new Bitrix24_Data();
		}

		return $this->bitrix24_data;
	}

	protected function success_response( $message = '' )
	{
		return wp_send_json_success( $message );
	}

	protected function error_response( $message = '' )
	{
		return wp_send_json_error( $message );
	}
}