<?php

namespace POC\Foundation\Modules\Bitrix24\Classes;

class Bitrix24_Data
{
	const STAGES_TRANSIENT_KEY = 'poc_foundation_bitrix24_stages';

	public $bitrix24_api = null;

	public function __construct()
	{
		$this->bitrix24_api = new Bitrix24_API();
	}

	public function get_stages( $force_renew = false )
	{
		if ( ! $force_renew ) {
			$stages = get_transient( self::STAGES_TRANSIENT_KEY );

			if ( $stages && ! empty( $stages ) ) {
				return $stages;
			}
		}

		$stages = array();

		$deal_categories = $this->get_api_client()->get_deal_categories();

		$statuses = $this->get_api_client()->get_statuses();

		foreach ( $deal_categories as $deal_category ) {
			foreach ( $statuses as $status ) {
				if ( $status['ENTITY_ID'] ===  'DEAL_STAGE_' . $deal_category['ID'] ) {
					$stages[$status['STATUS_ID']] = $deal_category['NAME'] . ' - ' . $status['NAME'];
				}
			}
		}

		set_transient( self::STAGES_TRANSIENT_KEY, $stages, DAY_IN_SECONDS );

		return $stages;
	}

	public function get_api_client()
	{
		if ( is_null( $this->bitrix24_api ) ) {
			$this->bitrix24_api = new Bitrix24_API();
		}

		return $this->bitrix24_api;
	}
}