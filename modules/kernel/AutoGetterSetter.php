<?php

namespace Quantum;

/**
 * Class AutoGetterSetter
 * @package Quantum
 */
class AutoGetterSetter
{
    /**
     * AutoGetterSetter constructor.
     */
    function __construct()
    {
        $args = func_get_args();
        if (empty($args[0])) {
            $args[0] = array();
        }

        $this->_properties = new ValueTree($args[0]);
    }


    /**
     * @param $method
     * @param $args
     * @return bool|mixed|void
     */
    public function __call($method, $args)
    {
        return $this->_properties->__call($method, $args);
    }

}


/**
 * Class AutoGetterSetterTestChild
 * @package Quantum
 */
class AutoGetterSetterTestChild extends AutoGetterSetter {};