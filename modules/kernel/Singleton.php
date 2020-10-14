<?php

namespace Quantum;

/**
 * Class Singleton
 * @package Quantum
 */
abstract class Singleton
{
    /**
     * Singleton constructor.
     */
    protected function __construct()
    {
    }

    /**
     * @return mixed
     */
    final public static function getInstance()
    {
        static $instances = array();

        $calledClass = \get_called_class();

        if (!isset($instances[$calledClass]))
        {
            $instances[$calledClass] = new $calledClass();
        }

        return $instances[$calledClass];
    }

    /**
     *
     */
    final private function __clone()
    {
    }

}
