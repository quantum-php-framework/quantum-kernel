<?php

namespace Quantum;

/**
 * Class StringValidator
 * @package Quantum
 */
class StringValidator
{
    /**
     * @param $string
     * @param $type
     * @return bool
     */
    public static function validate($string, $type)
    {
        $param = qs($string)->crc32b()->getText();

        $validator = new RequestParamValidator();
        $validator->add($type, $param);

        $data[$param] = $string;

        return $validator->processValidations($data);

    }
}