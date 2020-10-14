<?php

namespace Quantum;

/**
 * Class Crypto
 * @package Quantum
 */
class Crypto
{

    /**
     * Crypto constructor.
     */
    function __construct() {
	
    }

    /**
     * @param $data
     * @param $key_string
     * @return string
     * @throws \Defuse\Crypto\Exception\BadFormatException
     * @throws \Defuse\Crypto\Exception\EnvironmentIsBrokenException
     */
    public static function encrypt($data, $key_string)
   {

       $key = \Defuse\Crypto\Key::loadFromAsciiSafeString($key_string, $do_not_trim = false);

       $cyphertext = \Defuse\Crypto\Crypto::encrypt($data, $key);

       return $cyphertext;
   }

    /**
     * @param $data
     * @param $key_string
     * @return string
     * @throws \Defuse\Crypto\Exception\BadFormatException
     * @throws \Defuse\Crypto\Exception\EnvironmentIsBrokenException
     */
    public static function decrypt($data, $key_string)
   {
       $key = \Defuse\Crypto\Key::loadFromAsciiSafeString($key_string, $do_not_trim = false);

       try {
           $text = \Defuse\Crypto\Crypto::decrypt($data, $key);
       }
       catch (\Defuse\Crypto\Exception\WrongKeyOrModifiedCiphertextException $ex)
       {
           ApiException::custom("Invalid Encrypted Data", "Invalid encrypted data", "Data Decryption failed");
       }
       return $text;
   }

    /**
     * @return string
     * @throws \Defuse\Crypto\Exception\EnvironmentIsBrokenException
     */
    public static function genKey()
   {
       $k = \Defuse\Crypto\Key::createNewRandomKey();
       return $k->saveToAsciiSafeString();
   }

    /**
     * @param $string
     * @return \Defuse\Crypto\Key
     * @throws \Defuse\Crypto\Exception\BadFormatException
     * @throws \Defuse\Crypto\Exception\EnvironmentIsBrokenException
     */
    public static function getKeyFromString($string)
   {
       $key = \Defuse\Crypto\Key::loadFromAsciiSafeString($string, $do_not_trim = false);
       return $key;
   }

    /**
     * @param $data
     * @return string
     * @throws \Defuse\Crypto\Exception\BadFormatException
     * @throws \Defuse\Crypto\Exception\EnvironmentIsBrokenException
     */
    public static function encryptWithLocalKey($data)
   {
       $environment_encryption_key = \QM::environment()->environment_encryption_key;

       return self::encrypt($data, $environment_encryption_key);
   }

    /**
     * @param $data
     * @return string
     * @throws \Defuse\Crypto\Exception\BadFormatException
     * @throws \Defuse\Crypto\Exception\EnvironmentIsBrokenException
     */
    public static function decryptWithLocalKey($data)
    {
        $environment_encryption_key = \QM::environment()->environment_encryption_key;

        return self::decrypt($data, $environment_encryption_key);
    }


    /**
     * @param o
     * @return string
     * @throws \Defuse\Crypto\Exception\BadFormatException
     * @throws \Defuse\Crypto\Exception\EnvironmentIsBrokenException
     */
    public static function encryptWithSharedKey($data)
    {
        $environment_encryption_key = \QM::environment()->shared_encryption_key;

        return self::encrypt($data, $environment_encryption_key);
    }

    /**
     * @param $data
     * @return string
     * @throws \Defuse\Crypto\Exception\BadFormatException
     * @throws \Defuse\Crypto\Exception\EnvironmentIsBrokenException
     */
    public static function decryptWithSharedKey($data)
    {
        $environment_encryption_key = \QM::environment()->shared_encryption_key;

        return self::decrypt($data, $environment_encryption_key);
    }


    /**
     * @param $data
     * @return string
     * @throws \Defuse\Crypto\Exception\BadFormatException
     * @throws \Defuse\Crypto\Exception\EnvironmentIsBrokenException
     */
    public static function encryptWithActiveAppKey($data)
    {
        $environment_encryption_key = \QM::config()->getActiveAppConfig()->getEncryptionKey();

        return self::encrypt($data, $environment_encryption_key);
    }

    /**
     * @param $data
     * @return string
     * @throws \Defuse\Crypto\Exception\BadFormatException
     * @throws \Defuse\Crypto\Exception\EnvironmentIsBrokenException
     */
    public static function decrypWithActiveAppKey($data)
    {
        $environment_encryption_key = \QM::config()->getActiveAppConfig()->getEncryptionKey();

        return self::decrypt($data, $environment_encryption_key);
    }
    
    
    
}