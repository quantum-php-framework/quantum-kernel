<?php

namespace Quantum;

final class Multiton
{
    /**
     * @var Multiton[]
     */
    private static $instances = [];
    /**
     * this is private to prevent from creating arbitrary instances
     */
    private function __construct()
    {
    }

    public static function getInstance($instanceName)
    {
        if (!isset(self::$instances[$instanceName])) {
            self::$instances[$instanceName] = new $instanceName();
        }
        return self::$instances[$instanceName];
    }

    /**
     * prevent instance from being cloned
     */

    private function __clone()
    {
    }
    /**
     * prevent instance from being unserialized
     */
    private function __wakeup()
    {
    }
}