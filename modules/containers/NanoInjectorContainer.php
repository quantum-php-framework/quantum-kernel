<?php

namespace Quantum\Containers;

/**
 * Class NanoInjectorContainer
 * @package Quantum\Containers
 */
class NanoInjectorContainer
{
    /**
     * @var array
     */
    private $s=array();

    /**
     * @param $k
     * @param $c
     */
    function __set($k, $c) { $this->s[$k]=$c; }

    /**
     * @param $k
     * @return mixed
     */
    function __get($k) { return $this->s[$k]($this); }
}