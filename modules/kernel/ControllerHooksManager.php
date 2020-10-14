<?php


namespace Quantum;


/**
 * Class ControllerHooksManager
 * @package Quantum
 */
class ControllerHooksManager
{

    /**
     * ControllerHooksManager constructor.
     * @param $parent
     */
    function __construct(&$parent)
    {
        $this->_parent = $parent;
    }

    /**
     * @param $classMethod
     * @param array $required_params
     */
    function post($classMethod, $required_params = array())
    {
        $this->callIfNeeded("post", $classMethod, $required_params);
    }

    /**
     * @param $classMethod
     * @param array $required_params
     */
    function get($classMethod, $required_params = array())
    {
        $this->callIfNeeded("get", $classMethod, $required_params);
    }

    /**
     * @param $classMethod
     * @param array $required_params
     */
    function put($classMethod, $required_params = array())
    {
        $this->callIfNeeded("put", $classMethod, $required_params);
    }

    /**
     * @param $classMethod
     * @param array $required_params
     */
    function delete($classMethod, $required_params = array())
    {
        $this->callIfNeeded("delete", $classMethod, $required_params);
    }

    /**
     * @param $httpMethod
     * @param $classMethod
     * @param array $required_params
     */
    function callIfNeeded($httpMethod, $classMethod, $required_params = array())
    {
        $this->register($httpMethod, $classMethod, $required_params);
        $this->callHooks();
    }

    /**
     * @param $httpMethod
     * @param $classMethod
     * @param array $required_params
     */
    function register($httpMethod, $classMethod, $required_params = array())
    {
        $hook = new ControllerHook($httpMethod, $this->_parent, $classMethod, $required_params);

        $this->getHooks()->set($hook->getIndexableName(), $hook);
    }

    /**
     *
     */
    function callHooks()
    {
        $request = Request::getInstance();

        $method = strtolower($request->getMethod());

        foreach ($this->getHooks()->getProperties() as $key => $hook)
        {
            if ($hook->method == $method)
            {
                if (!empty($hook->required_params) && !$request->hasParams($hook->required_params))
                    continue;

                $hook->call($this->_parent);
                $this->_hooks->remove($hook->getIndexableName());
            }
        }

        $this->clearHooks();
    }


    /**
     *
     */
    function clearHooks()
    {
        $this->getHooks()->clear();
    }

    /**
     * @return ValueTree
     */
    function getHooks()
    {
        if (!isset($this->_hooks))
            $this->_hooks = new ValueTree();

        return $this->_hooks;
    }


}