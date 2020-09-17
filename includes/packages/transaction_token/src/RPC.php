<?php
namespace Ezdefi\Poc;

use Ezdefi\Poc\Contracts\RPCInterface;
use \Ezdefi\Poc\Traits\RPCTraits;
use Ethereum\Ethereum;
use Ethereum\DataType\EthD;
use Ethereum\DataType\EthD20;
use Ethereum\DataType\EthBlockParam;
use Ethereum\Abi;
use Ethereum\DataType\EthQ;
use Ethereum\DataType\EthD32;
use Ethereum\DataType\EthS;

class RPC implements RPCInterface
{
	use RPCTraits;

    protected $config;

	public function sendRawTransaction(string $signTransaction)
	{
        return $this->getEthereumInstance()->eth_sendRawTransaction(
            $this->getEthDataType( 'D' , $signTransaction)
        )->encodedHexVal();
	}

    public function getTransactionCount(string $addressFrom)
    {
        return $this->getEthereumInstance()->eth_getTransactionCount(
            $this->getEthDataType('D20', $addressFrom),
            new EthBlockParam()
        )->val();
	}

	protected function getEthereumInstance()
    {
        if(empty($this->config['url']) || !is_string($this->config['url'])) {
            throw new \Exception('Invalid Url connection to node');
        }
        return new Ethereum($this->config['url']);
    }

    public function getDataInTransaction(string $methodName, array $param)
    {
        if ( $methodName == 'addTransaction' ) {
            $dataAbiPocPool = new AbiPocPool($this->config['abi_data']);
            return $dataAbiPocPool->encodeFunction($methodName, $param);
        }

       $param = $this->refactorDataOfArrayPramAbi($param);
        return $this->refactorData(
            $this->getAbiClass()->encodeFunction(
                $methodName,
                $param
            )->encodedHexVal()
        );
	}

	public function refactorDataOfArrayPramAbi($data)
    {
        if(isset($data['amount'])) {
            $data['amount'] = $this->amountToWei($data['amount']);
        }

        if (isset($data['_amount'])) {
            $data['_amount'] = $this->amountToWei($data['_amount']);
        }
        $dataInsert = [];
        foreach ($data as $key => $value){
            $dataInsert[] = $this->getEthDataType('D', $value);
        }
        return $dataInsert;
    }

    protected function getEthDataType($type, $value)
    {
        $result = null;
        switch ($type) {
            case 'D20':
                try {
                    $result = new EthD20($value);
                } catch (\Exception $e) {
                    throw new \InvalidArgumentException($e->getMessage());
                }
                break;
            case 'D':
                try {
                    $result = new EthD($value);
                } catch (\Exception $e) {
                    throw new \InvalidArgumentException($e->getMessage());
                }
                break;
            case 'Q':
                try {
                    $result = new EthQ($value);
                } catch (\Exception $e) {
                    throw new \InvalidArgumentException($e->getMessage());
                }
                break;
            case 'D32':
                try {
                    $result = new EthD32($value);
                } catch (\Exception $e) {
                    throw new \InvalidArgumentException($e->getMessage());
                }
                break;
            case 'S':
                try {
                    $result = new EthS($value);
                } catch (\Exception $e) {
                    throw new \InvalidArgumentException($e->getMessage());
                }
                break;
        }
        return $result;
    }

    public function getConfig()
    {
        return $this->config;
    }

    public function setConfig(array $config)
    {
        $this->config = $config;
    }

    public function getAbiClass() : Abi
    {
        return new Abi( $this->config['abi_data'] );
    }

    public function amountToWei($amount)
    {
        $amount = strval($amount);
        $amount = $this->toWei($amount);
        $amountHex = $this->bcdechex($amount);
        return $amountHex;
    }

}