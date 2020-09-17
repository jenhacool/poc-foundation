<?php
namespace Ezdefi\Poc\Contracts;

interface TransactionInterface
{
    public function getAddress(string $privateKey);

	public function sign(array $data);
}