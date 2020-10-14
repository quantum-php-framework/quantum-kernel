<?php

namespace Quantum;

use Closure;


/**
 * Class Cache
 * @package Quantum
 */
class Cache
{
    public static $runtime_cache = null;
    public static $use_runtime_cache = true;


    public function __construct()
    {

    }

    public static function enableRuntimeCache($shouldBeEnabled)
    {
        self::$use_runtime_cache = $shouldBeEnabled;
    }

    const DEFAULT_EXPIRATION = 300;
    /**
     * Set a value to a key\
     * @param $key
     * @param mixed|Closure $var
     * @param int $expiration
     * @return mixed
     */
    public static function set($key, $var, $expiration = 0)
    {
        $provider = self::getProvider();

        if (is_closure($var))
        {
            $var = call_user_func($var);
            $provider->set($key, $var, $expiration);
        }
        else
        {
            $provider->set($key, $var, $expiration);
        }

        if (self::$use_runtime_cache) {
            self::runtime_cache()->set($key, $var);
        }

        return $var;
    }




    /**
     * Set a value to a key provided from a callback call
     * @param $key
     * @param $callback
     * @return mixed
     */
    public static function setWithCallback($key, $callback, $expiration = 0)
    {
        $provider = self::getProvider();
        $var = call_user_func($callback);
        $provider->set($key, $var, $expiration);

        if (self::$use_runtime_cache) {
            self::runtime_cache()->set($key, $var);
        };

        return $var;
    }



    /**
     * Get a value  previously set by the provided key.
     * If the value is not found and the fallback var is provided
     * it will be set to it.
     * The fallback can be a callable which must return a value.
     * @param $key, $fallback, $expiration
     * @return mixed
     */
    public static function get($key, $fallback = null, $expiration = null)
    {
        if (self::$use_runtime_cache && self::runtime_cache()->has($key)) {
            return self::runtime_cache()->get($key);
        }

        $provider = self::getProvider();

        if (!$provider->has($key) && $fallback != null) {
            return self::set($key, $fallback, $expiration);
        }

        $value = $provider->get($key);

        if (self::$use_runtime_cache) {
            self::runtime_cache()->set($key, $value);
        };

        return $value;
    }

    /**
     * @param $key
     * @param $var
     * @return mixed
     */
    public static function replace($key, $var)
    {
        if (self::$use_runtime_cache) {
            self::runtime_cache()->set($key, $var);
        };

        return self::getProvider()->replace($key, $var);
    }

    /**
     * @param $key
     * @return mixed
     */
    public static function delete($key)
    {
        if (self::$use_runtime_cache && self::runtime_cache()->has($key)) {
            self::runtime_cache()->remove($key);
        }

        $provider = self::getProvider();

        return $provider->delete($key);
    }

    /**
     * @param $key
     * @return mixed
     */
    public static function has($key)
    {
        if (self::$use_runtime_cache && self::runtime_cache()->has($key)) {
            return true;
        }

        $provider = self::getProvider();

        return $provider->has($key);
    }

    /**
     * @return mixed
     */
    public static function flush()
    {
        if (self::$use_runtime_cache) {
            self::runtime_cache()->clear();
        };

        $provider = self::getProvider();

        return $provider->flush();
    }

    /**
     * @param $items
     * @return mixed
     */
    public static function setParams($items)
    {
        if (self::$use_runtime_cache) {
            self::runtime_cache()->setProperties($items);
        };

        $provider = self::getProvider();

        return $provider->setParams($items);
    }

    /**
     * @return mixed
     */
    public static function useRedis()
    {
        return self::setDriver('redis');
    }

    /**
     * @return mixed
     */
    public static function useMemcache()
    {
        return self::setDriver('memcache');
    }

    /**
     * @return mixed
     */
    public static function useFiles()
    {
        return self::setDriver('files');
    }

    /**
     * @return mixed
     */
    public static function useEncryptedFiles()
    {
        return self::setDriver('encrypted');
    }

    /**
     * @return mixed
     */
    public static function useAPC()
    {
        return self::setDriver('apc');
    }

    /**
     * @return mixed
     */
    public static function useEaccelerator()
    {
        return self::setDriver('eaccelerator');
    }

    /**
     * @return mixed
     */
    public static function useDatabase()
    {
        return self::setDriver('db');
    }

    /**
     * @return mixed
     */
    public static function useMongoDB()
    {
        return self::setDriver('mongodb');
    }


    /**
     * @param $driver
     * @return mixed
     */
    public static function setDriver ($driver)
    {
        $provider = self::getProvider();

        return $provider->setDriver($driver);
    }

    /**
     * @param $key
     * @param int $expiration
     * @return mixed
     */
    public function setExpiration ($key, $expiration = 0)
    {
        $provider = self::getProvider();

        return $provider->setExpiration($key, $expiration);
    }

    /**
     * @param $key
     * @param int $offset
     * @param int $initial_value
     * @param int $expiry
     * @return mixed
     */
    public static function increment($key, $offset = 1, $initial_value = 0, $expiry = 0)
    {
        if (self::$use_runtime_cache) {
            self::runtime_cache()->increment($key, $offset, $initial_value);
        };

        return self::getProvider()->increment($key, $offset, $initial_value, $expiry);
    }

    /**
     * @param $key
     * @param int $offset
     * @param int $initial_value
     * @param int $expiry
     * @return mixed
     */
    public static function decrement($key, $offset = 1, $initial_value = 0, $expiry = 0)
    {
        if (self::$use_runtime_cache) {
            self::runtime_cache()->decrement($key, $offset, $initial_value);
        };

        return self::getProvider()->decrement($key, $offset, $initial_value, $expiry);
    }

    /**
     * @param $key
     * @param $callback
     * @return mixed
     */
    public static function incrementWithCallback($key, $callback)
    {
        if (self::$use_runtime_cache) {
            self::runtime_cache()->increment($key, $callback());
        };

        return self::getProvider()->increment($key, $callback());
    }

    /**
     * @param $key
     * @param $callback
     * @return mixed
     */
    public static function decrementWithCallback($key, $callback)
    {
        if (self::$use_runtime_cache) {
            self::runtime_cache()->decrement($key, $callback());
        };

        return self::getProvider()->decrement($key, $callback());
    }

    /**
     * @return mixed
     */
    public static function getProvider()
    {
        return \Quantum\Cache\ServiceProvider::getInstance();
    }

    /**
     * @param $driver
     * @return mixed
     */
    public static function storage($driver)
    {
        return self::getProvider()->storage($driver);
    }

    /**
     * @param $driver
     * @return mixed
     */
    public function setBackend($driver)
    {
        return self::setDriver($driver);
    }

    public static function addToArray($key, $data_to_add)
    {
        $data = new_vt(self::get($key));

        $data->add($data_to_add);

        self::set($key, $data->toStdArray());

        return $data;
    }

    /**
     * @return ValueTree
     */
    private static function runtime_cache()
    {
        if (self::$runtime_cache == null) {
            self::$runtime_cache = new ValueTree();
        }

        return self::$runtime_cache;
    }



}