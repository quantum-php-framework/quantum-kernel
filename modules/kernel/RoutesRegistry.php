<?php


namespace Quantum;


/**
 * Class RoutesRegistry
 * @package Quantum
 */
class RoutesRegistry extends Singleton
{
    /**
     * @var ValueTree
     */
    public static $registry = null;

    /**
     * RoutesRegistry constructor.
    */

    function __construct()
    {

    }

    /**
     * @param ValueTree $routes
     */
    public static function addRoutes(ValueTree $routes)
    {
        if ($routes->isEmpty())
            return;

        foreach ($routes->all() as $route)
        {
            self::registry()->add($route);
        }
    }


    public static function getRoutes()
    {
        return self::registry()->toStdArray();
    }

    public static function registry()
    {
        if (self::$registry == null) {
            self::$registry = new ValueTree();
        }

        return self::$registry;
    }


}