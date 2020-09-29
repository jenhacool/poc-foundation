<?php
namespace Ezdefi\Poc;

use Ezdefi\Poc\Contracts\RPCInterface;
use Ezdefi\Poc\Contracts\TransactionInterface;
use Ezdefi\Poc\Traits\RPCTraits;
use http\Message;

class Client
{
    use RPCTraits;

    public $rpc = null;

    public $transaction = null;

    public function sendTransaction(array $data)
    {
        $transaction = $this->getTransaction();
        $RPC = $this->getRPC();

        $this->checkEmptyAndString($data['rpc_config']['abi_json_file_path_token'], 'Invalid file path abi json token' );

        $abiDataToken   = $this->readFileJson($data['rpc_config']['abi_json_file_path_token']);

        $this->checkArray($abiDataToken, 'Invalid read file abi token json');

        $data['rpc_config']['abi_data_token'] = $abiDataToken;

        unset($data['rpc_config']['abi_json_file_path_token']);

        if($data['rpc_config']['name_abi'] === 'addTransaction') {

            $this->checkEmptyAndString($data['rpc_config']['abi_json_file_path_pool'], 'Invalid file path abi json pool');

            $abiDataPool = $this->readFileJson($data['rpc_config']['abi_json_file_path_pool']);

            $this->checkArray($abiDataPool, 'Invalid read file abi pool json');

            $data['rpc_config']['abi_data_pool'] = $abiDataPool;

            unset($data['rpc_config']['abi_json_file_path_pool']);

            $this->checkEmptyAndString($data['transaction_data']['addressContractPool'], 'Invalid address contract pool');

            $data['param']['addressContractPool'] = $data['transaction_data']['addressContractPool'];
        }

        $RPC->setConfig($data['rpc_config']);

        try {
            $data['transaction_data']['transactionData'] = $RPC->getDataInTransaction(
                $data['rpc_config']['name_abi'],
                $data['param']
            );
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }

        $this->checkEmptyAndString($data['transaction_data']['privateKey'] , 'Invalid privateKey' );

        $data['transaction_data']['name_abi'] = $data['rpc_config']['name_abi'];

        $data['transaction_data']['nonce'] = $RPC->getTransactionCount($transaction->getAddress($data['transaction_data']['privateKey']));

        $signedData = $transaction->sign($data['transaction_data']);

        if( $signedData < 2 ){
            return $RPC->sendRawTransaction($signedData);
        }

        $hash = [];
        foreach ( $signedData as $key => $item ) {
            $hash[] = $RPC->sendRawTransaction($item);
        }
        return $hash[count($signedData) - 1];
    }

    public function getRPC() :RPCInterface
    {
        if(is_null($this->rpc)){
            $this->rpc = new RPC();
        }

        return $this->rpc;
    }

    public function setRPC(RPCInterface $rpc)
    {
        $this->rpc = $rpc;
    }

    public function getTransaction() : TransactionInterface
    {
        if(is_null($this->transaction)) {
            $this->transaction = new Transaction();
        }

        return $this->transaction;
    }

    public function setTransaction(TransactionInterface $transaction)
    {
        $this->transaction = $transaction;
    }

    protected function readFileJson(string $path)
    {
        $dataJson = file_get_contents($path);
        if(!$dataJson) {
            throw new \Exception('Get file error');
        }
        return json_decode($dataJson);
    }

    public function amountToWei($amount)
    {
        $amount = strval($amount);
        $amount = $this->toWei($amount);
        $amountHex = $this->bcdechex($amount);
        return $amountHex;
    }

    protected function checkEmptyAndString($data, $message)
    {
        if(empty($data) || !is_string($data)) {
            throw new \Exception($message);
        }
    }

    protected function checkArray($data, $message)
    {
        if(!is_array($data)) {
            throw new \Exception($message);
        }
    }
}