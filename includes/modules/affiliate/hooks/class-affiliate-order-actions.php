<?php

namespace POC\Foundation\Modules\Affiliate\Hooks;

use POC\Foundation\Classes\POC_API;
use POC\Foundation\Classes\Option;
use POC\Foundation\Contracts\Hook;
use POC\Foundation\Modules\Affiliate\Utilities\Check_Coupon;
use Ezdefi\Poc\Client;

class Affiliate_Order_Actions implements Hook
{
	use Check_Coupon;

	protected $address_contract = '0x8d82238C53Db647A1911c6512cC40963b0c19B81';
	protected $chain_id = 66666;
	protected $gas = 300000;
	protected $gas_price = '1000000';
	protected $value_of_data_transaction = 0;
	protected $url_block_chain = 'https://rpc.nexty.io';
	protected $link_abi_json_path = 'http://localhost/ezdefi-send-token/poc_pool_abi.json';
	protected $name_abi = 'addTransaction';

	public function hooks()
	{
		add_action( 'woocommerce_checkout_order_processed', array( $this, 'add_ref_to_order' ), 10, 3 );

		add_action( 'woocommerce_order_status_completed', array( $this, 'after_order_completed' ) );

		add_action( 'woocommerce_order_status_refunded', array( $this, 'after_order_refunded' ) );

		add_filter( 'woocommerce_coupon_get_discount_amount', array( $this, 'get_discount_amount' ), 10, 6 );
	}

	public function add_ref_to_order( $order_id, $posted_data, $order )
	{
		if ( ! empty( get_post_meta( $order_id, 'ref_by' ) ) ) {
			return;
		}

		$coupon_codes = $order->get_coupon_codes();

		if ( ! empty( $coupon_codes ) && $this->is_coupon_valid( $coupon_codes[0] ) ) {
			$ref_by = $coupon_codes[0];
		} else {
			$ref_by = ! empty( $_COOKIE['ref_by'] ) ? $_COOKIE['ref_by'] : get_user_meta( get_current_user_id(), 'ref_by', true );
		}

		if ( empty( $ref_by ) ) {
			return;
		}

		$this->add_order_meta_data( $order_id, 'ref_by', $ref_by );

		if( ! empty( get_post_meta( $order_id, 'ref_by_subid' ) ) ) {
			return;
		}

		$ref_by_subid = ! empty( $_COOKIE['ref_by_subid'] ) ? $_COOKIE['ref_by_subid'] : get_user_meta( get_current_user_id(), 'ref_by_subid', true );

		if( empty( $ref_by_subid ) ) {
			return;
		}

		$this->add_order_meta_data( $order_id, 'ref_by_subid', $ref_by_subid );

		return;
	}

	public function after_order_completed( $order_id )
	{
        $is_transaction_hash = $this->get_transaction_hash_from_order_id( $order_id );

        if( ! empty( $is_transaction_hash ) ) {
            return;
        }

        $data_transaction_hash = $this->get_hash_from_send_transaction( $order_id );

        $this->save_transaction_hash( $order_id, $data_transaction_hash );

        add_post_meta( $order_id, 'reward_status', 'sent' );

		return;
	}

	public function after_order_refunded( $order_id )
	{
		$uid = $this->get_uid_prefix();

		$this->write_log( "Revoked an affiliate TX:: $uid.$order_id" );

		$result = $this->get_api_wrapper()->send_request(
			"revoketransaction/uid/$uid.$order_id"
		);

		if ( $result != 'Done') {
			$this->write_log( "Error while revoke a Tx:: uid: $uid.$order_id" );
		}

		return;
	}

	/**
	 * Custom discount amount base on product
	 *
	 * @param $round
	 * @param $discounting_amount
	 * @param $cart_item
	 * @param $single
	 * @param $coupon
	 *
	 * @return false|float
	 */
	public function get_discount_amount( $round, $discounting_amount, $cart_item, $single, $coupon )
	{
		$product = wc_get_product( $cart_item['product_id'] );

		if( ! $product ) {
			return $round;
		}

		$custom_discount = $product->get_meta( 'poc_foundation_discount' );

		if( empty( $custom_discount ) ) {
			return $round;
		}

		$discount = (float) $cart_item['line_subtotal'] * ( (int) $custom_discount / 100 );

		$round = round( $discount, wc_get_rounding_precision() );

		return $round;
	}

	/**
	 * Add order meta data
	 *
	 * @param integer $order_id
	 * @param string $key
	 * @param string $value
	 *
	 * @return false|int
	 */
	protected function add_order_meta_data( $order_id, $key, $value )
	{
		return add_post_meta( $order_id, $key, $value );
	}

	/**
	 * Get order meta data
	 *
	 * @param $order_id
	 * @param $key
	 *
	 * @return mixed
	 */
	protected function get_order_meta_data( $order_id, $key )
	{
		return get_post_meta( $order_id, $key, true );
	}

	/**
	 * Get order by id
	 *
	 * @param $order_id
	 *
	 * @return bool|\WC_Order|\WC_Order_Refund
	 */
	protected function get_order_by_id( $order_id )
	{
		return wc_get_order( $order_id );
	}

	/**
	 * Calculate revenue share total
	 *
	 * @param $order
	 *
	 * @return float|int
	 */
	protected function get_revenue_share_total( $order )
	{
		$revenue_share_total = 0;

		foreach ( $order->get_items() as $item ) {
			$revenue_share_percent = (int) $item->get_product()->get_meta( 'poc_foundation_revenue_share' );

			if( empty( $revenue_share_percent ) ) {
				$revenue_share_percent = (int) $this->get_default_revenue_share();
			}

			$revenue_share = (  $item->get_total() ) * ( $revenue_share_percent / 100 );

			$revenue_share_total += $revenue_share;
		}

		return round( $revenue_share_total / $this->get_poc_price(), 6 );
	}

	/**
	 * Get POC Price
	 *
	 * @return bool|string
	 */
	protected function get_poc_price()
	{
		$currency = strtolower( get_woocommerce_currency() );
		$price = $this->get_api_wrapper()->send_request( "getprice/poc/$currency" );
		if ($price && is_numeric($price['data']['price']) && $price['data']['price'] > 0) {
			return $price['data']['price'];
		} else {
			// Try again after 1s
			sleep(1);
			$price = $this->get_api_wrapper()->send_request( "getprice/poc/$currency" );
			if ($price && is_numeric($price['data']['price']) && $price['data']['price'] > 0) {
				return $price['data']['price'];
			} else {
				// Try again after 1s
				sleep(1);
				$price = $this->get_api_wrapper()->send_request( "getprice/poc/$currency" );
				if ($price && is_numeric($price['data']['price']) && $price['data']['price'] > 0) {
					return $price['data']['price'];
				} else {
					// Try again after 1s
					sleep(1);
					return false;
				}
			}
		}
	}

	/**
	 * Get UID Prefix
	 *
	 * @return bool|mixed|void
	 */
	protected function get_uid_prefix()
	{
		return Option::get( 'uid_prefix' );
	}

	/**
	 * Get refund term
	 *
	 * @return int
	 */
	protected function get_refund_term()
	{
		return 1;
	}

	/**
	 * Get release value
	 *
	 * @return float|int
	 */
	protected function get_release_value()
	{
		return time() + $this->get_refund_term() * 60;
	}

	/**
	 * Get default revenue share value
	 *
	 * @return bool|mixed|void
	 */
	protected function get_default_revenue_share()
	{
		$default_revenue_share = Option::get( 'default_revenue_share' );

		if ( empty( $default_revenue_share ) ) {
			return 60;
		}

		return $default_revenue_share;
	}

	protected function get_api_wrapper()
	{
		return new POC_API();
	}

	/**
	 * Write log
	 *
	 * @param $log
	 */
	protected function write_log( $log )
	{
		if ( is_array( $log ) || is_object( $log ) ) {
			error_log( print_r( $log, true ) );
		} else {
			error_log( $log );
		}
	}

	public function get_hash_from_send_transaction ( $order_id )
    {
        $ref_by = $this->get_order_meta_data( $order_id, 'ref_by' );

        $ref_by_subid = $this->get_order_meta_data( $order_id, 'ref_by_subid' );

        if ( ! $ref_by ) {
            $ref_by = 'null';
        }

        if ( $ref_by_subid ) {
            $ref_by = $ref_by . '::' . urlencode( $ref_by_subid );
        }

        $order = $this->get_order_by_id( $order_id );

        $username = $this->get_uid_prefix();

        $amount = $this->get_revenue_share_total( $order );

        $release = $this->get_release_value();

        $ref_rate = $this->get_array_ref_rate();

        $uid = $username .'-'. $order_id;

//        $eth = new Client();

        $eth = new Client();

        $private_key = Option::get( 'private_key' );
        $amount_hex = $eth->amountToWei( $amount );
        $release_hex = $this->bcdechex( $release );
        $ref_rate_hex = $this->convert_data_array_to_hex( $ref_rate );

        $data = [
            'transaction_data' => array(
                'addressContract'  => $this->address_contract,
                'privateKey'       => $private_key,
                'chainId'          => $this->chain_id,
                'gas'              => $this->gas,
                'gasPrice'         => $this->gas_price,
                'value'            => $this->value_of_data_transaction,
            ),
            'rpc_config' => array(
                'url'                  => $this->url_block_chain,
                'abi_json_file_path'   => $this->link_abi_json_path,
                'name_abi'             => $this->name_abi
            ),
            'param' => array(
                '_uid'          => $uid,
                '_username'     => $username,
                '_ref_by'       => $ref_by,
                '_amount'       => $amount_hex,
                '_merchant'     => $username,
                '_subid'        => '',
                '_release'      => $release_hex,
                '_ref_rates'    => $ref_rate_hex
            )
        ];
        $data_transaction_hash = $eth->sendTransaction( $data );

        return $data_transaction_hash;
    }

    protected function get_private_key()
    {
        $get_private_key = Option::get( 'private_key' );
        if( !$get_private_key ){
            return false;
        }
        return $get_private_key;
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

    protected function get_array_ref_rate()
    {
        $data = unserialize( get_option( 'poc_foundation' ) )['ref_rates'];
        return $data;
    }

    protected function convert_data_array_to_hex( $data_array )
    {
        $count_array = count( $data_array );
        $i = $count_array;
        for( $i ; $i < 10; $i++ ){
            $data_array[$i] = '0';
        }

        $data_array_hex = [];
        // convert to hex
        foreach ( $data_array as $item ) {
            $data_array_hex[] = $this->bcdechex( $item * 100 );
        }
        return $data_array_hex;
    }

    protected function save_transaction_hash( $order_id, $transaction_hash )
    {
        add_post_meta( $order_id, 'transaction_hash', $transaction_hash );
    }

    protected function get_transaction_hash_from_order_id( $order_id )
    {
        $data = get_post_meta( $order_id, 'transaction_hash', true );
        return $data;
    }

    protected function get_reward_status_from_order_id( $order_id )
    {
        $data = get_post_meta( $order_id, 'reward_status', true );
        return $data;
    }

    public function make_transaction_hash( $order_id )
    {

//        $transactionHash = $this->get_hash_from_send_transaction( $order_id );
$transactionHash = '0x2d1bb75d7a5114408f97d8c4b268034c116ae6c6a4064a6730975eeba4d4f836';
        update_post_meta( $order_id, 'transaction_hash', $transactionHash );

    }
}