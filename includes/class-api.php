<?php

namespace POC\Foundation;

class API
{
	public static $api_endpoint = 'https://api.poc.me/api';

	public $domain;

	public function __construct( $domain = '' )
	{
		if ( empty( $domain ) ) {
			$domain = $this->get_default_domain();
		}

		$this->domain = $domain;
	}

	protected function get_default_domain()
	{
		$protocols = array( 'http://', 'https://', 'http://www.', 'https://www.', 'www.' );

		return str_replace( $protocols, '', site_url() );
	}

	/**
	 * Send API request
	 *
	 * @param $path
	 * @param string $method
	 * @param array $data
	 *
	 * @return mixed|null
	 */
	public function send_request( $path, $method = 'GET', $data = [] )
	{
		$url = $this->build_path( $path ) ;

		$method = strtolower( $method );

		$headers = $this->get_headers();

		if( $method === 'post' ) {
			return $this->parse_response(
				wp_remote_post(
					$url,
					array(
						'headers' => $headers,
						'body' => json_encode( $data ),
						'data_format' => 'body',
					)
				)
			);
		}

		if( ! empty( $data ) ) {
			$url = sprintf("%s?%s", $url, http_build_query( $data ) );
		}

		return $this->parse_response(
			wp_remote_get(
				$url,
				array(
					'headers' => $headers
				)
			)
		);
	}

	/**
	 * Build full API path
	 *
	 * @param $path
	 *
	 * @return string
	 */
	protected function build_path( $path )
	{
		return rtrim( self::$api_endpoint, '/' ) . '/' . $path;
	}

	/**
	 * Get API request headers
	 *
	 * @return array
	 */
	protected function get_headers()
	{
		return array(
			'api-key' => $this->get_api_key(),
			'Content-Type' => 'application/json; charset=utf-8',
		);
	}

	/**
	 * Get API Key
	 *
	 * @return bool|mixed|void
	 */
	protected function get_api_key()
	{
		return get_option( 'poc_foundation_api_key', true );
	}

	/**
	 * Parse API response
	 *
	 * @param $response
	 *
	 * @return mixed|null
	 */
	protected function parse_response( $response )
	{
		if ( is_wp_error( $response ) ) {
			return null;
		}

		$response = json_decode( wp_remote_retrieve_body( $response ), true );

		if( ! $response ) {
			return null;
		}

		return $response;
	}
}