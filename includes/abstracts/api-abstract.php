<?php

namespace POC\Foundation\Abstracts;

abstract class API
{
	/**
	 * Send API request
	 *
	 * @param string $url
	 * @param string $method
	 * @param array $data
	 * @param array $headers
	 * @param array $options
	 *
	 * @return mixed|null
	 */
	public function send_request( $path, $method = 'GET', $data = [], $headers = [], $options = [] )
	{
		$url = $this->build_path( $path ) ;

		$method = strtolower( $method );

		$headers = array_merge( $this->get_default_headers(), $headers );

		$options = array_merge( $this->get_default_options(), $options );

		if( $method === 'post' ) {
			return $this->parse_response(
				wp_remote_post(
					$url,
					array_merge( array(
						'headers' => $headers,
						'body' => $data,
					), $options )
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
		return rtrim( $this->get_endpoint(), '/' ) . '/' . $path;
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

		$body = wp_remote_retrieve_body( $response );

		$data = json_decode( $body, true );

		if( ! $data ) {
			return null;
		}

		return $data;
	}

	/**
	 * Get API Endpoint
	 *
	 * @return string
	 */
	abstract public function get_endpoint();

	/**
	 * Get default API request headers
	 *
	 * @return array
	 */
	abstract public function get_default_headers();

	/**
	 * Get default API request options
	 *
	 * @return array
	 */
	abstract public function get_default_options();
}