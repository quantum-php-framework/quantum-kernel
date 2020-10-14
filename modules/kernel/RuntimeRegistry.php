<?php

namespace Quantum;
//require_once ("ValueTree.php");

/**
 * Class RuntimeRegistry
 * @package Quantum
 */
class RuntimeRegistry extends Singleton
{

    /**
     * @var ValueTree
     */
    static private $tree;

    /**
     * RuntimeRegistry constructor.
     */
    function __construct()
    {
        self::$tree = new \Quantum\ValueTree();
    }

    /**
     * @return ValueTree
     */
    public static function getValueTree()
    {
        return self::$tree;
    }

    /**
     * @param $propertyName
     * @param $value
     */
    public static function set($propertyName, $value)
    {
        RuntimeRegistry::getInstance()->getValueTree()->set($propertyName, $value);
    }

    /**
     * @param $propertyName
     * @param bool $fallbackValue
     * @return mixed
     */
    public static function get($propertyName, $fallbackValue = false)
    {
        return RuntimeRegistry::getInstance()->getValueTree()->get($propertyName, $fallbackValue);
    }

    /**
     * @param $propertyName
     * @return mixed
     */
    public static function has($propertyName)
    {
        return RuntimeRegistry::getInstance()->getValueTree()->has($propertyName);
    }

    /**
     * @param $key
     */
    public static function remove($key)
    {
        RuntimeRegistry::getInstance()->getValueTree()->remove($key);
    }

}



