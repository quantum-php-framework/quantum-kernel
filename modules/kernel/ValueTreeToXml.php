<?php

namespace Quantum;

/**
 * Class ArrayToXml
 * @package Quantum
 */
class ValueTreeToXml
{
    #ifndef DOXYGEN_SHOULD_SKIP_THIS
    /**
     * @param $obj
     * @param string $node_name
     * @param null $wrapper
     * @param array $replacements
     * @param bool $add_header
     * @param array $header_params
     * @return string
     */
    public static function toXml($valueTree, $rootName = 'root', $addOpenTag = false, $addCdata = true)
    {
        return ArrayToXml::convert($valueTree->toStdArray(), $rootName);
    }
    #endif DOXYGEN_SHOULD_SKIP_THIS

}