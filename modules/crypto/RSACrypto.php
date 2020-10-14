<?php

namespace Quantum;

use phpseclib\Crypt\RSA;

/**
 * Class RSACrypto
 * @package Quantum
 */
class RSACrypto
{

    /**
     * RSACrypto constructor.
     */
    function __construct()
    {
	
    }

    /**
     * @param $data
     * @param $key
     * @return string
     */
    public static function encrypt($data, $key)
   {
       $rsa = new RSA();
       $rsa->loadKey($key); // public key

       return $rsa->encrypt($data);
   }

    /**
     * @param $data
     * @param $key
     * @return string
     */
    public static function decrypt($data, $key)
   {
       $rsa = new RSA();
       $rsa->loadKey($key); // private key

       return $rsa->decrypt($data);
   }

    /**
     * @return array
     */
    public static function genKey()
   {
       $rsa = new RSA();

       return $rsa->createKey();
   }

    /**
     * @param $data
     * @param $key
     * @return string
     */
    public static function base64Encrypt($data, $key)
    {
        $rsa = new RSA();
        $rsa->loadKey($key); // public key

        return Utilities::base64_url_encode($rsa->encrypt($data));
    }

    /**
     * @param $data
     * @param $key
     * @return string
     */
    public static function base64Decrypt($data, $key)
    {
        $rsa = new RSA();
        $rsa->loadKey($key); // private key

        return $rsa->decrypt(Utilities::base64_url_decode($data));
    }

    
    
    
}