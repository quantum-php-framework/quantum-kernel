<?php

namespace Quantum;

/**
 * Class FormParam
 * @package Quantum
 */

class FormParam
{

    public $type;
    public $visibleName;
    public $paramName;
    public $value;
    public $options;
    public $required;

    /**
     * FormParam constructor.
     * @param $type
     * @param $visibleName
     * @param $paramName
     * @param $defaultValue
     * @param array $options
     */
    public function __construct($type, $visibleName, $paramName, $defaultValue, $required, $selectFieldOptions = array())
    {
        $this->type = $type;
        $this->visibleName = $visibleName;
        $this->paramName = $paramName;
        $this->value = $defaultValue;
        $this->required = $required;
        $this->options = $selectFieldOptions;
    }
}