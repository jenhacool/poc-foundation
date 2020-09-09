<?php

namespace POC\Foundation\Modules\Bitrix24\Classes;

use POC\Foundation\Abstracts\API;
use POC\Foundation\Classes\Option;

class Bitrix24_API extends API
{
	public function get_default_headers()
	{
		return array();
	}

	public function get_endpoint()
	{
		$option = new Option();

		return $option->get( 'bitrix24_webhook' );
	}

	public function get_default_options()
	{
		return array(
			'user-agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/66.0.3359.181 Safari/537.36',
		);
	}

	public function get_deal_categories()
	{
		return $this->send_request( 'crm.dealcategory.list', 'POST' );
	}

	public function get_statuses()
	{
		return $this->send_request( 'crm.status.list', 'POST' );
	}

	public function add_deal( $deal_data )
	{
		return $this->send_request( 'crm.deal.add', 'POST', $deal_data );
	}

	public function add_contact( $contact_data )
	{
		return $this->send_request( 'crm.contact.add', 'POST', $contact_data );
	}

	public function parse_response( $response )
	{
		$data = parent::parse_response( $response );

		if ( ! isset( $data['result'] ) ) {
			return null;
		}

		return $data['result'];
	}
}