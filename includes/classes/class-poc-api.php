<?php

namespace POC\Foundation\Classes;

use POC\Foundation\Abstracts\API;

class POC_API extends API
{
	public $domain;

	public function __construct( $domain = '' )
	{
		if ( empty( $domain ) ) {
			$domain = $this->get_default_domain();
		}

		$this->domain = $domain;
	}

	public function get_endpoint()
	{
		return 'https://api.poc.me/api';
	}

	public function get_default_headers()
	{
		return array(
			'api-key' => $this->get_api_key(),
			'Content-Type' => 'application/json; charset=utf-8',
		);
	}

	public function get_default_options()
	{
		return array( 'data_format' => 'body' );
	}

	protected function get_api_key()
	{
		return Option::get( 'api_key' );
	}

	protected function get_default_domain()
	{
		$protocols = array( 'http://', 'https://', 'http://www.', 'https://www.', 'www.' );

		return str_replace( $protocols, '', site_url() );
	}
}