<?php
namespace Ezdefi\Poc\Traits;

trait RPCTraits
{
    private $unitMap = [
        'poc' => '1',
        'wei' => '1000000000000000000',
    ];

	public function refactorData($data)
	{
	    $data1 = substr($data, 0, 10);
	    $data2 = '000000000000000000000000';
	    $data3 = substr($data, 10);
	    $data  = $data1 . $data2 . $data3;
		return $data;
	}

	public function toWei(string $amount, string $unit = 'wei')
	{
		if ($unit == 'poc') {
		    return $amount;
        }

		return $this->getBCMUL($amount, $this->getValueOfUnit($unit));
	}

    public function bcdechex($dec)
    {
        $hex = '';
        do {
            $last   = bcmod($dec, 16);
            $hex    = dechex($last).$hex;
            $dec    = bcdiv(bcsub($dec, $last), 16);
        } while($dec > 0);
        return $hex;
	}

	protected function getBCMUL($amount, $valueOfUnit)
    {
        return bcmul($amount, $valueOfUnit);
    }

    public function getValueOfUnit(string $unit = 'wei')
    {
        if (!isset($this->unitMap[$unit])) {
            throw new \UnexpectedValueException(
                sprintf('A unit "%s" doesn\'t exist, please use the one of the following units: %s', $unit, implode(', ', array_keys($this->unitMap)))
            );
        }

        return $this->unitMap[$unit];
    }

    public function validateLength($val)
    {
        $un_padded = $this->unPadEnsureLength($val);
        if ($un_padded) {
            return $un_padded;
        } else {
            throw new \Exception('Invalid length for hex binary: ' . $val);
        }
    }
    public static function hasHexPrefix($str)
    {
        return substr($str, 0, 2) === '0x';
    }

    public static function ensureHexPrefix($str)
    {
        if (self::hasHexPrefix($str)) {
            return $str;
        }
        return '0x' . $str;
    }

    public static function unPadEnsureLength($string)
    {
        // Remove leading zeros.
        // See: https://regex101.com/r/O2Rpei/5
        $matches = [];
        if (preg_match('/^0x0*([0-9,a-f]{40})$/is', self::ensureHexPrefix($string), $matches)) {
            $address = '0x' . $matches[1];
            // Throws an Exception if not valid.
            if (self::isValidAddress($address, true)) {
                return $address;
            }
        }
        return null;
    }

    public static function isValidAddress($address, $throw = false)
    {
        if (!self::hasHexPrefix($address)) {
            return false;
        }
        // Address should be 20bytes=40 HEX-chars + prefix.
        if (strlen($address) !== 42) {
            return false;
        }
        $return = ctype_xdigit(self::removeHexPrefix($address));
        return $return;
    }

    public static function removeHexPrefix($str)
    {
        if (!self::hasHexPrefix($str)) {
            return $str;
        }
        return substr($str, 2);
    }
}