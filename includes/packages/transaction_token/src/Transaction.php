<?php
namespace Ezdefi\Poc;

use Ezdefi\Poc\Contracts\TransactionInterface;
use Ezdefi\Poc\Traits\RPCTraits;
use Web3p\EthereumUtil\Util;
use Web3p\EthereumTx\Transaction as TransactionHandle;

class Transaction implements TransactionInterface
{
    protected $util;
    use RPCTraits;
    public function __construct()
    {
        $this->util = new Util();
    }

	public function sign($data)
	{

	    if(empty($data['addressContract']) || !is_string($data['addressContract'])) {
	        throw new \Exception('addressContract');
        }

	    $this->validateLength($data['addressContract']);

        if(empty($data['chainId']) || !is_numeric($data['chainId']) ) {
            throw new \InvalidArgumentException('Invalid chainId');
        }

        if(!isset($data['gas']) || !($data['gas'] >= 21000) ) {
            throw new \InvalidArgumentException('Invalid gas');
        }

        if(!isset($data['gasPrice'])) {
            throw new \InvalidArgumentException('Invalid gasPrice');
        }

        if(!isset($data['value'])) {
            throw new \InvalidArgumentException('Invalid value');
        }

        $dataTransaction = [
            'nonce'     => $data['nonce'],
            'from'      => $this->getAddress($data['privateKey']),
            'to'        => $data['addressContract'],
            'chainId'   => $data['chainId'],
            'gas'       => $data['gas'],
            'gasPrice'  => $data['gasPrice'],
            'value'     => $data['value'],
            'data'      => $data['transactionData']
        ];

        return (new TransactionHandle($dataTransaction))->sign($data['privateKey']);
	}

    public function getAddress(string $privateKey)
    {
        try {
            return $this->util->publicKeyToAddress(
                $this->util->privateKeyToPublicKey($privateKey)
            );
        } catch (\Exception $e) {
            throw new \InvalidArgumentException($e->getMessage());
        }

    }
}