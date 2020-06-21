<?php
/**
 * Class pocAffiliateNotify
 * Customization Form Registration for Poc Affiliate
 * Sendy Registration via API
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class POC_Affiliate_Notifier extends \ElementorPro\Modules\Forms\Classes\Action_Base {
	protected $config;

	public function __construct( $config )
	{
		$this->config = $config;
	}

	public function get_name() {
		return 'pocaffiliatenotifier';
	}

	public function get_label() {
		return __( 'POC Affiliate Notifier', 'poc-foundation' );
	}

	public function register_settings_section( $widget )
	{
		$widget->start_controls_section(
			'section_poc',
			[
				'label' => __( 'POC', 'poc-foundation' ),
				'condition' => [
					'submit_actions' => $this->get_name(),
				],
			]
		);

		$widget->end_controls_section();
	}

	public function on_export( $element )
	{

	}

	public function run( $record, $ajax_handler ) {
		$raw_fields = $record->get( 'fields' );

		$fields = [];

		foreach ( $raw_fields as $id => $field ) {
			$fields[$id] = $field['value'];
		}

		$fields['domain'] = $this->config['domain'];

		if ( isset( $_COOKIE['ref_by'] ) ) {
			$fields['ref_by'] = $_COOKIE['ref_by'];
		}

		if ( isset( $_COOKIE['subid'] ) ) {
			$fields['subid'] = $_COOKIE['subid'];
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

		$send_notify = wp_remote_post( $this->config['api_endpoint'] . '/notify/new_ref_lead', array(
			'headers' => array(
				'Content-Type' => 'application/json; charset=utf-8',
				'api-key' => $this->config['api_key']
			),
			'body' => json_encode( $fields ),
			'data_format' => 'body',
		) );

		if( is_wp_error( $send_notify ) ) {
			$ajax_handler->add_error_message( __( 'Failed. Please try again.', 'poc-foundation' ) );
			return;
		}

		$notify_data = json_decode( $send_notify['body'] );

		if ( $notify_data->message != 'done' ) {
			$ajax_handler->add_error_message( __( 'Failed. Please try again.', 'poc-foundation' ) );
			return;
		}

		$permalink = get_permalink( get_option( 'poc_foundation_redirect_page', '' ) );

		if( ! $permalink || ! $_COOKIE['ref_by'] ) {
			$ajax_handler->add_success_message( __( 'Success', 'poc-foundation' ) );
			return;
		}

		$response = wp_remote_get( $this->config['api_endpoint'] . '/website/get_fanpage/' . $_COOKIE['ref_by'], array(
			'headers' => array(
				'Content-Type' => 'application/json; charset=utf-8',
				'api-key' => get_option( 'poc_foundation_api_key' )
			),
		) );

		if( is_wp_error( $response ) ) {
			setcookie('poc_foundation_fanpage_url', get_option( 'poc_foundation_fanpage_url' ), time() + ( 86400 * 30 ), '/' );
			setcookie('poc_foundation_fanpage_id', get_option( 'poc_foundation_fanpage_id' ), time() + ( 86400 * 30 ), '/' );
			$ajax_handler->add_response_data( 'redirect_url', $permalink );
			return;
		}

		$fanpage_data = json_decode( wp_remote_retrieve_body( $response ), true );

		if( empty( $fanpage_data ) || ! isset( $fanpage_data['data'] ) || ! isset( $fanpage_data['data']['fanpage_url'] ) || ! isset( $fanpage_data['data']['fanpage_id'] ) ) {
			setcookie('poc_foundation_fanpage_url', get_option( 'poc_foundation_fanpage_url' ), time() + ( 86400 * 30 ), '/' );
			setcookie('poc_foundation_fanpage_id', get_option( 'poc_foundation_fanpage_id' ), time() + ( 86400 * 30 ), '/' );
			$ajax_handler->add_response_data( 'redirect_url', $permalink );
			return;
		}

		setcookie('poc_foundation_fanpage_url', $fanpage_data['data']['fanpage_url'], time() + (86400 * 30), "/");
		setcookie('poc_foundation_fanpage_id', $fanpage_data['data']['fanpage_id'], time() + (86400 * 30), "/");
		$ajax_handler->add_response_data( 'redirect_url', $permalink );
		return;
	}
}