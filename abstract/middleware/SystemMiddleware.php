<?php

namespace Quantum\Middleware\Foundation;

use Closure;

/**
 * Class SystemMiddleware
 * @package Quantum\Middleware\Foundation
 */
abstract class SystemMiddleware
{
    /**
     * SystemMiddleware constructor.
     */
    function __construct()
    {
        //dd($this->getRequest());
    }


    /**
     * @param \Quantum\Request $request
     * @param Closure $closure
     * @return mixed
     */
    abstract protected function handle(\Quantum\Request $request, Closure $closure);


    /**
     * @return \Quantum\Request
     */
    function getRequest()
    {
        return \Quantum\Request::getInstance();
    }

    /**
     * @return \Quantum\Output
     */
    function getOutput()
    {
        return \Quantum\Output::getInstance();
    }


    /**
     * @param $type
     * @param string $sample
     */
    function logException($type, $sample = "")
    {
        \WafSecurityException::logException($type, $sample);
    }


}