<?php

namespace POC\Foundation\Classes;

class API
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
	public static function send_request( $url, $method = 'GET', $data = [], $headers = [], $options = [] )
	{
		$method = strtolower( $method );

		if( $method === 'post' ) {
			return self::parse_response(
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

		return self::parse_response(
			wp_remote_get(
				$url,
				array(
					'headers' => $headers
				)
			)
		);
	}

	/**
	 * Parse API response
	 *
	 * @param $response
	 *
	 * @return mixed|null
	 */
	public static function parse_response( $response )
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