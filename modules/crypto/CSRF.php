<?php

namespace Quantum;


/**
 * Class CSRF
 * @package Quantum
 */
class CSRF
{


    /**
     * CSRF constructor.
     */
    function __construct() {
	
    }


    /**
     * @param int $length
     * @return bool|string
     * @throws \Exception
     */
    public static function create($length = 32)
    {
        if(!isset($length) || intval($length) <= 8 ){
            $length = 32;
        }
        if (\function_exists('random_bytes')) {
            return \bin2hex(\random_bytes($length));
        }
        if (\function_exists('mcrypt_create_iv')) {
            return \bin2hex(\mcrypt_create_iv($length, MCRYPT_DEV_URANDOM));
        }
        if (\function_exists('openssl_random_pseudo_bytes')) {
            return \bin2hex(\openssl_random_pseudo_bytes($length));
        }

        if (@file_exists('/dev/urandom')) { // Get 100 bytes of random data
            $randomData = file_get_contents('/dev/urandom', false, null, 0, 20);
            if ($randomData !== false && strlen($randomData) === 20) {
                return bin2hex($randomData);
            }
        }

        return QString::random(48)->hash('sha512')->substring(0, $length)->toStdString();

    }
    
    
    
}