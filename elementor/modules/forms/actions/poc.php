<?php
/**
 * Class pocAffiliateNotify
 * Customization Form Registration for Poc Affiliate
 * Sendy Registration via API
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

use POC\Foundation\POC_Foundation_API;

class POC_Affiliate_Notifier extends \ElementorPro\Modules\Forms\Classes\Action_Base
{
	public $api;

	public function __construct()
	{
		$this->api = new POC_Foundation_API();
	}

	public function get_name() {
		return 'pocaffiliatenotifier';
	}

	public function get_label() {
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

	public function run( $record, $ajax_handler ) {
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

	protected function get_campaign_data_by_key( $key )
	{
		$campaigns = unserialize( get_option( 'poc_foundation_campaign', array() ) );

		if ( empty( $campaigns ) ) {
			return null;
		}

		$index = array_search( $key, array_column( $campaigns, 'campaign_key' ) );

		if ( $index == false ) {
			return null;
		}

		return $campaigns[$index];
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