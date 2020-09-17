<?php

namespace POC\Foundation\Modules\LGS\Hooks\Elementor\FormActions;

use POC\Foundation\Classes\Helper;
use POC\Foundation\Classes\POC_API;

class Form_Action extends \ElementorPro\Modules\Forms\Classes\Action_Base
{
	public $api;

	public function __construct()
	{
		$this->api = new POC_API();
	}

	public function get_name()
	{
		return 'pocaffiliatenotifier';
	}

	public function get_label()
	{
		return __( 'POC Affiliate Notifier', 'poc-foundation' );
	}

	public function register_settings_section( $widget )
	{

	}

	public function on_export( $element )
	{

	}

	public function run( $record, $ajax_handler )
	{
		$saved = $this->save_to_db( $record );

		if ( ! $saved ) {
			$ajax_handler->add_error_message( __( 'Failed. Please try again.', 'poc-foundation' ) );
			return;
		}

		return;
	}

	protected function save_to_db( $record )
	{
		$fields = $record->get( 'fields' );

		if ( ! $fields ) {
			return false;
		}

		$data  = array();

		foreach ( $fields as $key => $value ) {
			$data[$key] = sanitize_text_field( $value['raw_value'] );
		}

		$post_id = wp_insert_post( array(
			'post_status' => 'publish',
			'post_type'   => 'poc_foundation_lead',
		) );

		if ( ! $post_id ) {
			return false;
		}

		update_post_meta( $post_id, 'email', sanitize_email( $data['email'] ) );
		update_post_meta( $post_id, 'phone', Helper::sanitize_phone_number( $data['phone'] ) );
		update_post_meta( $post_id, 'name', $data['name'] );
		update_post_meta( $post_id, 'ref_by', $data['ref_by'] );
		update_post_meta( $post_id, 'form_name', sanitize_text_field( $record->get_form_settings( 'form_name' ) ) );

		return true;
	}

	protected function add_error_message( $ajax_handler, $message = '' )
	{
		if ( empty( $message ) ) {
			$message = __( 'Failed. Please try again.', 'poc-foundation' );
		}

		$ajax_handler->add_error_message( $message );

		return;
	}
}