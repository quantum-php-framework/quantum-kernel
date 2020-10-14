<?php

namespace Quantum;

/**
 * Class ControllerRoute
 * @package Quantum
 */
class ControllerRoute
{
    /**
     * @var
     */
    public $controllerName;
    /**
     * @var
     */
    public $functionToBeCalled;

    /**
     * ControllerRoute constructor.
     * @param $controllerName
     * @param $functionToBeCalled
     */
    function __construct($controllerName, $functionToBeCalled)
    {
        $this->controllerName = $controllerName;
        $this->functionToBeCalled = $functionToBeCalled;
    }
}

/**
 * Class Router
 * @package Quantum
 */
class Router
{

    /**
     * @var ValueTree
     */
    public $registry;

    /**
     * Router constructor.
     */
    function __construct()
    {
        $this->registry = new ValueTree();
    }


    /**
     * @param $uri
     * @param $controllerName
     * @param $controllerAction
     */
    function addControllerRoute($uri, $controllerName, $controllerAction)
    {
        $this->registry->set($uri, new ControllerRoute($controllerName, $controllerAction));
    }

    /**
     * @param $uri
     * @return bool|mixed
     */
    function getControllerRoute($uri)
    {
        $route = $this->registry->get($uri);

        return $route;
    }

    /**
     * @param $var
     * @param $location
     */
    static function redirectIfEmpty($var, $location)
    {
        if (empty($var))
            redirect_to($location);
    }

    /**
     * @return array
     */
    function getRoutes()
    {
        return $this->registry->all();
    }

}