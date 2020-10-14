<?php

namespace Quantum\HMVC;

/**
 * Class Helper
 * @package Quantum\HMVC
 */
class Helper
{

    /**
     * Helper constructor.
     */
    public function __construct()
    {
        $this->config = new_vt(include qf(child_class_dir($this))->getParentDirectory().'/etc/config.php');
    }

    function getConfig()
    {
        return $this->config;
    }





}