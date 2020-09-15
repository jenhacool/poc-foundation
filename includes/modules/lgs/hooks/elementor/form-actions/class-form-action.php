<?php

namespace POC\Foundation\Modules\LGS\Hooks\Elementor\FormActions;

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
		$page_options = array();

		foreach ( get_pages() as $page ) {
			$page_options[$page->ID] = $page->post_title;
		}

		$widget->start_controls_section(
			'section_poc',
			[
				'label' => __( 'POC', 'poc-foundation' ),
				'condition' => [
					'submit_actions' => $this->get_name(),
				],
			]
		);

		$widget->add_control(
			'campaign_key',
			array(
				'label' => __( 'Campaign Key', 'poc-foundation' ),
				'type' => \Elementor\Controls_Manager::TEXT,
			)
		);

		$widget->add_control(
			'success_page',
			array(
				'label' => __( 'Success Page', 'poc-foundation' ),
				'type' => \Elementor\Controls_Manager::SELECT,
				'default' => array_keys( $page_options )[0],
				'options' => $page_options
			)
		);

		$widget->end_controls_section();
	}

	public function on_export( $element )
	{

	}

	public function run( $record, $ajax_handler )
	{
		$this->save_to_db( $record );

		$raw_fields = $record->get( 'fields' );

		$fields = [];

		$settings = $record->get( 'form_settings' );

		if ( ! isset( $settings['poc_campaign'] ) || empty( $settings['poc_campaign'] ) ) {
			return $this->add_error_message( $ajax_handler );
		}

		$fields['campaign_key'] = $settings['poc_campaign'];

		foreach ( $raw_fields as $id => $field ) {
			$fields[$id] = $field['value'];
		}

		if ( ! isset( $fields['ref_by'] ) ) {
			$fields['ref_by'] = ( isset( $_COOKIE['ref_by'] ) ) ? $_COOKIE['ref_by'] : '';
		}

		if ( empty( $fields['ref_by'] ) ) {
			return $this->add_error_message( $ajax_handler );
		}

		if ( ! isset( $fields['subid'] ) ) {
			$fields['subid'] = ( isset( $_COOKIE['subid'] ) ) ? $_COOKIE['subid'] : '';
		}

		if ( ! isset( $fields['name'] ) || empty( $fields['name'] ) ) {
			$ajax_handler->add_error( 'name', 'Name is required' );
			return;
		}

		if ( ! isset( $fields['email'] ) || empty( $fields['email'] ) ) {
			$ajax_handler->add_error( 'email', 'Email is required' );
			return;
		}

		if ( ! isset( $fields['phone'] ) || empty( $fields['phone'] ) ) {
			$ajax_handler->add_error( 'phone', 'Phone is required' );
			return;
		}

		$notify_data = $this->api->send_request( 'notify/new_ref_lead', 'POST', $fields );

		if( is_null( $notify_data ) ) {
			return $this->add_error_message( $ajax_handler );
		}

		if ( $notify_data->message != 'done' ) {
			return $this->add_error_message( $ajax_handler );
		}

		$permalink = get_permalink( get_option( 'poc_foundation_redirect_page', '' ) );

		if( ! $permalink ) {
			$ajax_handler->add_success_message( __( 'Success', 'poc-foundation' ) );
			return;
		}

		$fanpage_data = $this->api->send_request( '/website/get_fanpage/' . $fields['ref_by'] );

		if( is_null( $fanpage_data ) || empty( $fanpage_data ) || ! isset( $fanpage_data['data'] ) || ! isset( $fanpage_data['data']['fanpage_url'] ) || ! isset( $fanpage_data['data']['fanpage_id'] ) ) {
			$ajax_handler->add_response_data( 'redirect_url', $permalink );
			return;
		}

		$transient_key = wp_generate_password( 13, false );

		set_transient(
			$transient_key,
			array(
				'poc_foundation_fanpage_url' => $fanpage_data['data']['fanpage_url'],
				'poc_foundation_fanpage_id' => $fanpage_data['data']['fanpage_id'],
			),
			HOUR_IN_SECONDS
		);

		$ajax_handler->add_response_data( 'redirect_url', $permalink . '?poc_key=' . $transient_key );
		return;
	}

	protected function save_to_db( $record )
	{
		$fields = $record->get( 'fields' );

		error_log( print_r( $fields, true ) );

		if ( ! $fields ) {
			return;
		}

		$data  = array();

		$email = false;

		foreach ( $fields as $key => $value ) {
			$data[$key] = sanitize_text_field( $value['raw_value'] );
		}

		$current_page = get_post( $_POST['post_id'] );

		$data = array_merge( $data, array(
			'submitted_on' => $current_page->post_title,
			'submitted_on_id' => $current_page->ID,
			'campaign_name' => 'Campaign Name'
		) );

		$db_ins = array(
			'post_title'  => $record->get_form_settings( 'form_name' ) . ' - ' . date( 'Y-m-d H:i:s' ),
			'post_status' => 'publish',
			'post_type'   => 'poc_foundation_lead',
		);

		$post_id = wp_insert_post( $db_ins );

		if ( ! $post_id ) {
			return;
		}

		update_post_meta( $post_id, 'email', $data['email'] );
		update_post_meta( $post_id, 'phone', $data['phone'] );
		update_post_meta( $post_id, 'name', $data['name'] );
		update_post_meta( $post_id, 'ref_by', $data['ref_by'] );
		update_post_meta( $post_id, 'campaign_name', $data['campaign_name'] );
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