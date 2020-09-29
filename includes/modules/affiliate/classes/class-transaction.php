<?php

namespace POC\Foundation\Modules\Affiliate\Classes;

use POC\Foundation\Modules\Affiliate\Classes\Explorer_API;
use Ezdefi\Poc\Client;

class Transaction
{
	const ADDRESS_CONTRACT_TOKEN = '0x14cCf9F6653Eac614a377eE827f0520601D3e68C';
	const ADDRESS_CONTRACT_POOL = '0x8d82238C53Db647A1911c6512cC40963b0c19B81';
	const CHAIN_ID = 66666;
	const GAS = 300000;
	const GAS_PRICE = '1000000';
	const DATA_TRANSACTION_VALUE = 0;
	const RPC_URL = 'https://rpc.nexty.io';
	const RPC_NAME_ABI = 'addTransaction';

	/**
	 * Check transaction status
	 *
	 * @param $transaction_hash
	 *
	 * @return string
	 */
	public static function check_status( $transaction_hash )
	{
		$result = ( new Explorer_API() )->check_status_transaction( $transaction_hash );

		return ( $result === '1' ) ? 'error' : 'success';
	}

	/**
	 * Send transaction
	 *
	 * @param $private_key
	 * @param $param
	 *
	 * @return mixed
	 * @throws \Exception
	 */
	public static function send( $private_key, $param )
	{
		$eth = new Client();

		return $eth->sendTransaction( array(
			'transaction_data' => array(
				'addressContractToken'  => self::ADDRESS_CONTRACT_TOKEN,
				'addressContractPool'  => self::ADDRESS_CONTRACT_POOL,
				'privateKey' => $private_key,
				'chainId' => self::CHAIN_ID,
				'gas' => self::GAS,
				'gasPrice' => self::GAS_PRICE,
				'value' => self::DATA_TRANSACTION_VALUE,
			),
			'rpc_config' => array(
				'url' => self::RPC_URL,
				'abi_json_file_path_pool' => self::get_ab_json_file_path_pool(),
				'abi_json_file_path_token' => self::get_ab_json_file_path_token(),
				'name_abi' => self::RPC_NAME_ABI
			),
			'param' => array_merge( self::default_param(), $param )
		) );
	}

	public static function default_param()
	{
		return array(
			'_uid' => '',
			'_username' => '',
			'_ref_by' => '',
			'_amount' => '',
			'_merchant' => '',
			'_subid' => '',
			'_release' => '',
			'_ref_rates' => ''
		);
	}

	public static function get_ab_json_file_path_pool()
	{
		return POC_FOUNDATION_PLUGIN_DIR . 'includes/packages/transaction_token/poc_pool_abi.json';
	}

    public static function get_ab_json_file_path_token()
    {
        return POC_FOUNDATION_PLUGIN_DIR . 'includes/packages/transaction_token/poc_token_abi.json';
	}
}