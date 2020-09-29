<?php
namespace Ezdefi\Poc;

use Ezdefi\Poc\Contracts\TransactionInterface;
use Ezdefi\Poc\Traits\RPCTraits;
use Web3p\EthereumUtil\Util;
use Web3p\EthereumTx\Transaction as TransactionHandle;

class Transaction implements TransactionInterface
{
    protected $util;
    protected $nonce;
    use RPCTraits;
    public function __construct()
    {
        $this->util = new Util();
    }

    public function sign($data)
    {

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

        $signedToken = $this->sign_for_token($data);

        if($data['name_abi'] != 'addTransaction') {
            return array(
                $signedToken['signedData']
            );
        }

        $data['nonce'] = $signedToken['nonce'] + 1 ;
        $signedPool = $this->sign_for_pool($data);

        return array(
            $signedToken['signedData'],
            $signedPool['signedData']
        );
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

    protected function sign_for_token($data)
    {
        if(empty($data['addressContractToken']) || !is_string($data['addressContractToken'])){
            throw new \Exception('Invalid address contract token and not address contract token');
        }

        $dataTransaction = [
            'nonce'     => $data['nonce'],
            'from'      => $this->getAddress($data['privateKey']),
            'to'        => $data['addressContractToken'],
            'chainId'   => $data['chainId'],
            'gas'       => $data['gas'],
            'gasPrice'  => 0,
            'data'      => $data['transactionData']['dataTx']
        ];
        return array(
            'signedData' => (new TransactionHandle($dataTransaction))->sign($data['privateKey']),
            'nonce' => $data['nonce']
        );
    }

    protected function sign_for_pool( $data )
    {
        if(empty($data['addressContractPool']) || !is_string($data['addressContractPool'])){
            throw new \Exception('Invalid address contract pool or not address contract pool');
        }

        $dataTransaction = [
            'nonce'     => $data['nonce'],
            'from'      => $this->getAddress($data['privateKey']),
            'to'        => $data['addressContractPool'],
            'chainId'   => $data['chainId'],
            'gas'       => $data['gas'],
            'gasPrice'  => $data['gasPrice'],
            'value'     => $data['value'],
            'data'      => $data['transactionData']['dataTxAddTransaction']
        ];
        return array(
            'signedData' => (new TransactionHandle($dataTransaction))->sign($data['privateKey']),
        );
    }
}