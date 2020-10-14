<?php

namespace Quantum\Cache;

use Quantum\Singleton;

/**
 * Class StorageSetupException
 * @package Quantum\Cache
 */
class StorageSetupException extends \Exception {};

/**
 * Class Backend
 * @package Quantum\Cache
 */
abstract class Backend extends Singleton
{
    /**
     * Backend constructor.
     */
    public function __construct()
    {

    }

    /**
     * @param $key
     * @param $var
     * @param int $expiration
     * @return mixed
     */
    public abstract function set($key, $var, $expiration = 0);

    /**
     * @param $key
     * @return mixed
     */
    public abstract function get($key);

    /**
     * @return mixed
     */
    public abstract function flush();

    /**
     * @param $key
     * @return mixed
     */
    public abstract function delete($key);


    /**
     * @param $items
     */
    public function setParams($items)
    {
        foreach ($items as $key => $item)
        {
            $this->set($key, $item);
        }
    }


    /**
     * @param $key
     * @param $var
     * @return mixed
     */
    public function add($key, $var)
    {
        return $this->set($key, $var);
    }


    /**
     * @param $key
     * @param $var
     * @param int $expiration
     * @return mixed
     */
    public function replace($key, $var, $expiration = 0)
    {
        return $this->set($key, $var, $expiration);
    }

    /**
     * @param $key
     * @return bool
     */
    public function has($key)
    {
        $data = $this->get($key);

        return !empty($data);
    }


    /**
     * @param $key
     * @param int $offset
     * @param int $initial_value
     * @param int $expiry
     * @return int|mixed
     */
    public function increment($key, $offset = 1, $initial_value = 0, $expiry = 0)
    {
        if (!$this->has($key))
        {
            $value = $initial_value + $offset;
            $this->set($key, $value, $expiry);
            return $value;
        }

        return $this->set($key, $this->get($key)+$offset);
    }


    /**
     * @param $key
     * @param int $offset
     * @param int $initial_value
     * @param int $expiry
     * @return int|mixed
     */
    public function decrement($key, $offset = 1, $initial_value = 0, $expiry = 0)
    {
        if (!$this->has($key))
        {
            $value = $initial_value - $offset;
            $this->set($key, $value, $expiry);
            return $value;
        }

        return $this->set($key, $this->get($key)-$offset);
    }


    /**
     * @param $key
     * @param int $expiration
     * @return bool
     */
    public function setExpiration ($key, $expiration = 0)
    {
        if ($expiration > 0)
        {
            if (!$this->has($key))
                return false;

            $value = $this->get($key);

            $this->set($key, $value, $expiration);
        }
    }

}