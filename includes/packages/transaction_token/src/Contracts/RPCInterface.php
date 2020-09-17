<?php
namespace Ezdefi\Poc\Contracts;

interface RPCInterface
{
    public function setConfig(array $config);

	public function getTransactionCount(string $data);

    public function getDataInTransaction(string $methodName, array $pram);

    public function sendRawTransaction(string $signTransaction);

}