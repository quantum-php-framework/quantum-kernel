<?php


namespace Quantum;

/**
 * Class ControllerHook
 * @package Quantum
 */
class ControllerHook
{

    /**
     * ControllerHook constructor.
     * @param $httpMethod
     * @param $parent
     * @param $classMethod
     * @param array $required_params
     */
    function __construct($httpMethod, $parent, $classMethod, $required_params = array())
    {
        $this->method = strtolower($httpMethod);
        $this->parent = $parent;
        $this->className = get_class($parent);
        $this->classMethod = $classMethod;
        $this->required_params = $required_params;
    }

    /**
     * @throws \ReflectionException
     */
    function call()
    {
        if (method_exists($this->className, $this->classMethod))
        {
            $reflection = new \ReflectionMethod($this->className, $this->classMethod);

            if ($reflection->isProtected() || $reflection->isPrivate())
            {
                $reflection->setAccessible(true);
                $reflection->invoke($this->parent, $this->classMethod);
                $reflection->setAccessible(false);
            }
        }

    }

    /**
     * @return string
     */
    function getIndexableName()
    {
        return $this->className."\\".$this->classMethod;
    }





}