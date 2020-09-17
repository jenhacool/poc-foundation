<?php
namespace Ezdefi\Poc;

use Ethereum\DataType\EthQ;
use Ethereum\EthereumStatic;
use Ezdefi\Poc\Traits\RPCTraits;
use Web3p\RLP\Types\Str;
use InvalidArgumentException;

class AbiPocPool extends EthereumStatic
{
    use RPCTraits;

    private $abi;

    // Non not RLP encoded values have a hex padding length of 64 (strlen).
    // See:https://github.com/ethereum/wiki/wiki/RLP
    const HEXPADDING = 64;
    //  if a string is 0-55 bytes long, the RLP encoding consists
    // of a single byte with value 0x80 plus the length of the string
    // followed by the string. The range of the first byte is thus [0x80, 0xb7].
    const THRESHOLD_LONG = 110; // As we count hex chars this value is 110

    public function __construct(array $abi)
    {
        $this->abi = $abi;
    }

    public function encodeFunction(string $methodName, array $values)
    {
        $m = $this->getParamDefinition($methodName);

        if (count($m->inputs) !== count($values)) {
            throw new \InvalidArgumentException('Expected ' . count($m->inputs) . ' params but got ' . count($values));
        }

        // [METHOD 4bytes] + [PARAMS]
        $params = $this->getSignature($m);
        $paramsOfString = '';
        $dataAbiPool = $m->inputs;
        $local = 544;
        foreach ($dataAbiPool as $key => $value1) {
            foreach ($values as $i => $val) {
                if($value1->name == $i) {

                    if($value1->type == 'string') {
                        $length = strlen(EthereumStatic::removeHexPrefix($this->encodeStringWithTypeString($val)));
                        $paramsOfString .= EthereumStatic::removeHexPrefix($this->encodeStringWithTypeString($val));
                        $params .= EthereumStatic::removeHexPrefix($this->padLeft($this->bcdechex($local)));
                        $local = $local + ($length/2);
                    }

                    if($value1->type == 'uint256') {
                        $params .= EthereumStatic::removeHexPrefix($this->padLeft($val));
                    }

                    if($value1->type == 'uint256[10]') {
                        foreach ($val as $i2 => $val2) {
                            $params .= EthereumStatic::removeHexPrefix($this->padLeft($val2));
                        }
                    }

                }
            }
        }
        $dataTx = $params.$paramsOfString;
        return $dataTx;
    }

    public function getParamDefinition(string $methodName)
    {
        foreach ($this->abi as $item) {
            if (isset($item->name)
                && isset($item->type)
                && $item->type === 'function'
                && $item->name === $methodName
            ) {
                return $item;
            }
        }
        throw new \Exception('Called undefined contract method: ' . $methodName . '.');
    }

    private static function getSignature($m)
    {
        $sign = $m->name . '(';
        foreach ($m->inputs as $i => $item) {
            $sign .= $item->type;
            if ($i < count($m->inputs) - 1) {
                $sign .= ',';
            }
        }
        $sign .= ')';
        return self::getMethodSignature($sign);
    }

    function encodeStringWithTypeString(string $val)
    {
        $val = self::removeHexPrefix($val);
        $valHex =Str::encode($val);
        $length = strlen($valHex);
        if ($length % 2 && ctype_xdigit($val)) {
            throw new InvalidArgumentException('Can not decode. Invalid hex value.');
        }
        if ($length < self::THRESHOLD_LONG) {
            // if a string is 0-55 bytes long, the RLP encoding consists
            // of a single byte with value 0x80 plus the length of the
            // string followed by the string. The range of the first byte is thus [0x80, 0xb7].
            $lengthInByte = self::getByteLength($length / 2);

        }
        else {
            // If a string is more than 55 bytes long, the RLP encoding
            // consists of a single byte with value 0xb7 plus the length
            // in bytes of the length of the string in binary form, followed
            // by the length of the string, followed by the string.
            $lengthInByte = self::getByteLength($length / 2);
        }
        return '0x'.self::removeHexPrefix($lengthInByte) . self::padRight($valHex);
    }

    public function getByteLength(int $l)
    {
        return (new EthQ($l, ['abi' => 'uint256']))->hexVal();
    }

    function padRight($val)
    {
        $fillUp = 64 - (strlen($val) % 64);
        if ($fillUp < 64) {
            return $val . str_repeat("0", $fillUp);
        }
        return $val;
    }

    function padLeft(string $val)
    {
        $unprefixed = self::removeHexPrefix($val);
        $fillUp = self::HEXPADDING - (strlen($unprefixed) % self::HEXPADDING);
        return self::ensureHexPrefix(str_repeat("0", $fillUp) . $unprefixed);
    }


}