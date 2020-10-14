<?php

namespace Quantum\Cache\Backend;

use Quantum\Cache\Backend;
use Quantum\Cache\StorageSetupException;

/**
 * Class Xcache
 * @package Quantum\Cache
 */
class Xcache extends Backend
{

    /**
     * List of available options
     *
     * @var array
     */
    protected $_options = array(
        'user' => null,
        'password' => null
    );


    /**
     * Storage constructor.
     * @throws \Exception
     */
    public function __construct($initFromEnv = true)
    {
        if (!extension_loaded('xcache'))
            throw new StorageSetupException("'xcache' extension not loaded");

        if  ($initFromEnv)
            $this->initFromEnvironmentConfig();
    }


    /**
     * @throws StorageSetupException
     */
    private function initFromEnvironmentConfig()
    {
        $config = new_vt(Config::getInstance()->getEnvironment());

        if (empty($config))
            throw new StorageSetupException('no active environment config');

        if (!$config->has('xcache_user'))
            throw new StorageSetupException('xcache_user not defined in environment config');

        if (!$config->has('xcache_password'))
            throw new StorageSetupException('xcache_password not defined in environment config');

        $this->_options['user'] = $config->get('xcache_user');

        $this->_options['password'] = $config->get('xcache_password');

    }


    /**
     * @param $key
     * @param $var
     * @param int $expiration
     * @return mixed
     */
    public function set($key, $data, $expiration = 0)
    {
        $lifetime = $expiration;

        $result = xcache_set($key, array($data, time()), $lifetime);

        return $result;
    }

    /**
     * @param $key
     * @return string
     */
    public function get($key)
    {
        $tmp = xcache_get($key);
        if (is_array($tmp)) {
            return $tmp[0];
        }
        return false;
    }

    /**
     * @param $key
     * @return int
     */
    public function has($key)
    {
        return xcache_isset($key);
    }


    /**
     * @return bool
     */
    public function flush()
    {
        // Necessary because xcache_clear_cache() need basic authentification
        $backup = array();
        if (isset($_SERVER['PHP_AUTH_USER'])) {
            $backup['PHP_AUTH_USER'] = $_SERVER['PHP_AUTH_USER'];
        }
        if (isset($_SERVER['PHP_AUTH_PW'])) {
            $backup['PHP_AUTH_PW'] = $_SERVER['PHP_AUTH_PW'];
        }
        if ($this->_options['user']) {
            $_SERVER['PHP_AUTH_USER'] = $this->_options['user'];
        }
        if ($this->_options['password']) {
            $_SERVER['PHP_AUTH_PW'] = $this->_options['password'];
        }
        $cnt = xcache_count(XC_TYPE_VAR);
        for ($i=0; $i < $cnt; $i++) {
            xcache_clear_cache(XC_TYPE_VAR, $i);
        }
        if (isset($backup['PHP_AUTH_USER'])) {
            $_SERVER['PHP_AUTH_USER'] = $backup['PHP_AUTH_USER'];
            $_SERVER['PHP_AUTH_PW'] = $backup['PHP_AUTH_PW'];
        }
        return true;
    }

    /**
     * @param $key
     * @param int $time
     * @return bool
     */
    public function delete($key)
    {
        return xcache_unset($key);
    }





}