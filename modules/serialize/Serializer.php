<?php

namespace Quantum\Serialize;
use Opis\Closure\SerializableClosure;

use Quantum\Serialize\Serializer\Native;
use Quantum\Serialize\Serializer\Json;
use Quantum\Serialize\Serializer\Closure;

/**
 * Class Serializer
 * @package Quantum\Serialize
 */
class Serializer
{
    /**
     * @param $data
     * @return false|string
     */
    public static function serialize($data, $asJson = true)
    {
        if ($asJson)
            $serialized = Json::serialize($data);
        else
            $serialized = Native::serialize($data);

        return $serialized;
    }


    /**
     * @param $data
     * @return mixed
     */
    public static function unserialize($data, $asJson = true)
    {
        if ($asJson)
            $unserialized = Json::unserialize($data);
        else
            $unserialized = Native::unserialize($data);

        return $unserialized;
    }

    /**
     * @param $closure
     * @return string
     */
    public static function serializeClosure($closure)
    {
        $wrapper = new SerializableClosure($closure);

        $s = Native::serialize($wrapper);

        return $s;
    }

    /**
     * @param $closure
     * @return mixed
     */
    public static function unserializeClosure($closure)
    {
        $wrapper = new SerializableClosure($closure);

        $s = Native::unserialize($wrapper);

        return $s;
    }


}