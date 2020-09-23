<?php

namespace POC\Foundation\Modules\Bitrix24\Classes;

use POC\Foundation\Classes\Option;
use Ezdefi\Poc\Client;
use POC\Foundation\Modules\Affiliate\Classes\Order_Data;
use POC\Foundation\Modules\Affiliate\Classes\Transaction;

class Order_Reward
{
	const REFUND_TERM = 1;

	public $order_id;

	/**
	 * Set order id
	 *
	 * @param $order_id
	 */
	public function set_order_id( $order_id )
	{
		$this->order_id = $order_id;
	}

	/**
	 * Pay reward
	 *
	 * @return mixed
	 */
	public function pay()
	{
		return Transaction::send(
			Option::get( 'private_key' ),
			$this->get_transaction_params()
		);
	}

	protected function get_transaction_params()
	{
		return array(
			'_uid' => $this->get_uid(),
			'_username' => $this->get_username(),
			'_ref_by' => $this->get_ref_by(),
			'_amount' => $this->get_amount(),
			'_merchant' => $this->get_username(),
			'_subid' => '',
			'_release' => $this->get_release(),
			'_ref_rates' => $this->get_ref_rates()
		);
	}

	protected function get_uid()
	{
		return $this->get_username() . '-' . $this->order_id;
	}

	protected function get_username()
	{
		return Option::get( 'uid_prefix' );
	}

	protected function get_ref_by()
	{
		return $this->get_order_data_object()->get_ref_by_string();
	}

	protected function get_amount()
	{
		$eth = new Client();

		return $eth->amountToWei(
			$this->get_order_data_object()->get_revenue_share_total()
		);
	}

	protected function get_release()
	{
		return $this->bcdechex( time() + self::REFUND_TERM * 60 );
	}

	protected function get_ref_rates()
	{
		return $this->convert_data_array_to_hex(
			unserialize( get_option( 'poc_foundation' ) )['ref_rates']
		);
	}

	protected function convert_data_array_to_hex( $data_array )
	{
		$count_array = count( $data_array );

		$i = $count_array;

		for( $i ; $i < 10; $i++ ) {
			$data_array[$i] = '0';
		}

		$data_array_hex = [];

		foreach ( $data_array as $item ) {
			$data_array_hex[] = $this->bcdechex( $item * 100 );
		}

		return $data_array_hex;
	}

	protected function bcdechex( $dec )
	{
		$hex = '';
		do {
			$last   = bcmod( $dec, 16 );
			$hex    = dechex( $last ).$hex;
			$dec    = bcdiv( bcsub( $dec, $last ), 16 );
		} while( $dec > 0 );
		return $hex;
	}

	protected function get_order_data_object()
	{
		$order_data = new Order_Data();
		$order_data->set_order_id( $this->order_id );

		return $order_data;
	}
}