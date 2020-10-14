<?php

namespace Quantum\HMVC;

/**
 * Class Module
 * @package Quantum\HMVC
 */
class Module
{

    /**
     * Module constructor.
     */
    public function __construct()
    {

    }

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