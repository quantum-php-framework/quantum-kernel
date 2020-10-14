<?php

namespace Quantum;

class ActiveAppKeyPairFileDb extends Singleton
{
    public static $runtime_cache = null;

    public static function set($key, $var)
    {
        $provider = self::getProvider();

        if (is_closure($var))
        {
            $var = call_user_func($var);
            $provider->set($key, $var, 0);
        }
        else
        {
            $provider->set($key, $var, 0);
        }

        self::runtime_cache()->set($key, $var);

        return $var;
    }

    public static function get($key, $fallback = null)
    {
        if (self::runtime_cache()->has($key)) {
            return self::runtime_cache()->get($key);
        }

        $provider = self::getProvider();

        if (!$provider->has($key) && $fallback != null) {
            return self::set($key, $fallback);
        }

        $value = $provider->get($key);

        self::runtime_cache()->set($key, $value);

        return $value;
    }


    private static function getProvider()
    {
        return ActiveAppKeyPairFileDbStorage::getInstance();
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