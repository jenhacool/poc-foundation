<?php
namespace Ezdefi\Poc;

use Ezdefi\Poc\Contracts\RPCInterface;
use Ezdefi\Poc\Contracts\TransactionInterface;
use Ezdefi\Poc\Traits\RPCTraits;
class Client
{
    use RPCTraits;

	public $rpc = null;

	public $transaction = null;

	public function sendTransaction(array $data)
	{
        $transaction = $this->getTransaction();
        $RPC = $this->getRPC();

        if(empty($data['rpc_config']['abi_json_file_path']) || !is_string($data['rpc_config']['abi_json_file_path'])){
            throw new \Exception('Invalid file path abi json');
        }

        $abiData = $this->readFileJson($data['rpc_config']['abi_json_file_path']);

        if(!is_array($abiData)) {
            throw new \Exception('Invalid read file abi Json');
        }

        $data['rpc_config']['abi_data'] = $abiData;
        unset($data['rpc_config']['abi_json_file_path']);
        $RPC->setConfig($data['rpc_config']);

        try {
            $data['transaction_data']['transactionData'] = $RPC->getDataInTransaction(
                $data['rpc_config']['name_abi'],
                $data['param']
            );
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }

        if(empty($data['transaction_data']['privateKey']) || !is_string($data['transaction_data']['privateKey'])) {
            throw new \Exception('Invalid privateKey');
        }

        $data['transaction_data']['nonce'] = $RPC->getTransactionCount($transaction->getAddress($data['transaction_data']['privateKey']));

		$signedData = $transaction->sign($data['transaction_data']);

		return $RPC->sendRawTransaction($signedData);
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
}