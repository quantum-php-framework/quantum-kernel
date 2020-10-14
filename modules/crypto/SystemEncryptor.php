<?php

namespace Quantum;

/**
 * Class SystemEncryptor
 * @package Quantum
 */
class SystemEncryptor
{

    /**
     * SystemEncryptor constructor.
     */
    public function __construct()
    {
    }

    /**
     * @param $data
     * @param int $rounds
     * @return string
     * @throws \Defuse\Crypto\Exception\BadFormatException
     * @throws \Defuse\Crypto\Exception\EnvironmentIsBrokenException
     */
    public static function encrypt($data, $rounds = 1)
    {
        for ($i = 0; $i < $rounds; ++$i)
        {
            $data = Crypto::encrypt($data, Config::getInstance()->getMasterEncryptionKey() );
        }

        return $data;
    }


    /**
     * @param $data
     * @param int $rounds
     * @return string
     * @throws \Defuse\Crypto\Exception\BadFormatException
     * @throws \Defuse\Crypto\Exception\EnvironmentIsBrokenException
     */
    public static function decrypt($data, $rounds = 1)
    {
        for ($i = 0; $i < $rounds; ++$i)
        {
            $data = Crypto::decrypt($data, Config::getInstance()->getMasterEncryptionKey());
        }

        return $data;
    }
}