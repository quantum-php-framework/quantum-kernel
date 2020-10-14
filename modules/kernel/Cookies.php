<?php



namespace Quantum;

/**
 * Class Cookies
 * @package Quantum
 */
class Cookies extends Singleton
{

    /**
     * Cookies constructor.
     */
    function __construct()
    {

    }

    /**
     * @param $key
     * @param $val
     * @param int $expires
     * @param string $path
     * @param string $domain
     * @param bool $secure
     * @param bool $httponly
     * @return bool
     */
    public function set($key, $val, $expires = 0, $path = "", $domain = "", $secure = false, $httponly = false)
    {
        return setcookie($key, $val, $expires, $path, $domain, $secure, $httponly);
    }

    /**
     * @param $key
     * @param $val
     * @param int $expires
     * @param string $path
     * @param string $domain
     * @param bool $secure
     * @param bool $httponly
     * @return bool
     */
    public function setEncrypted($key, $val, $expires = 0, $path = "", $domain = "", $secure = false, $httponly = false)
    {
        if (!empty($val))
            $val = SystemEncryptor::encrypt($val);

        return setcookie($key, $val, $expires, $path, $domain, $secure, $httponly);
    }


    /**
     * @param $key
     * @param bool $fallback
     * @return bool|mixed
     */
    public function get($key, $fallback = false)
    {
        if ($this->has($key))
            return $_COOKIE[$key];

        return $fallback;
    }

    /**
     * @param $key
     * @param bool $fallback
     * @return bool|mixe
     */
    public function getDecrypted($key, $fallback = false)
    {
        if ($this->has($key))
        {
            $value = $_COOKIE[$key];

            if (!empty($value))
                $value = SystemEncryptor::decrypt($value);

            return $value;
        }

        return $fallback;
    }


    /**
     * @param $key
     * @return bool
     */
    public function has($key)
    {
        if (!isset($_COOKIE) || !is_array($_COOKIE))
            return false;

        return array_key_exists($key, $_COOKIE);
    }


    /**
     * @param $key
     */
    public function markForDeletion($key)
    {
        setcookie($key, '', time()-1000);
        setcookie($key, '', time()-1000, '/');
    }
    




    
    
    
}