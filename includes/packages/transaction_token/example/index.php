<?php
require_once dirname(dirname(__FILE__)) . '/vendor/autoload.php';

use Ezdefi\Poc\TransactionPocToken;



$data = [
    'transaction_data' => array(
        'addressContractToken' => '0x14cCf9F6653Eac614a377eE827f0520601D3e68C', // contract token
        'addressContractPool' => '0x8d82238C53Db647A1911c6512cC40963b0c19B81', // contract pool
        'privateKey' => 'e6cba9375d93cd4356dfcacfba7e4dcdbd3b8c0868f6b6de6ccc7a5a799955b1',
        'chainId' => 66666,
        'gas' => 300000,
        'gasPrice' => '1000000',
        'value' => 0,
    ),
    'rpc_config' => array(
        'url' => 'https://rpc.nexty.io',
        'abi_json_file_path_token' => 'http://localhost/ezdefi-send-token/poc_token_abi.json',
        'abi_json_file_path_pool' => 'http://localhost/ezdefi-send-token/poc_pool_abi.json',
        'name_abi' => 'addTransaction'
//        'name_abi' => 'transfer'
    ),
    'param' => array(
//        'recipient'=> '0x524861A251f02ef0c31ab67326D59E6465990f9D',
//        'amount'=> (new \Ezdefi\Poc\Client())->amountToWei('0.001')
        '_uid'          => 'datpt.com-292',
        '_username'     => 'datpt.com',
        '_ref_by'       => 'pocadmin',
        '_amount'       => (new \Ezdefi\Poc\Client())->amountToWei('0.0001'),
        '_merchant'     => 'datpt.com',
        '_subid'        => '',
        '_release'      => '5f5988ec',
        '_ref_rates'    =>  ['2710', '0', '0', '0', '0', '0', '0', '0', '0', '0',],
    )
];

var_dump((new \Ezdefi\Poc\Client())->sendTransaction($data));