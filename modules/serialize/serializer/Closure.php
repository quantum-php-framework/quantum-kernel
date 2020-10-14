<?php

namespace Quantum\Serialize\Serializer;

use Opis\Closure\SerializableClosure;

/**
 * Class Serializer
 * @package Quantum\Serialize
 */
class Closure implements SerializerInterface
{

    /**
     * @inheritDoc
     * @since 101.0.0
     */
    public static function serialize($closure)
    {
        $wrapper = new SerializableClosure($closure);

        $s = Native::serialize($wrapper);

        return $s;
    }


    /**
     * @inheritDoc
     * @since 101.0.0
     */
    public static function unserialize($closure)
    {
        $wrapper = new SerializableClosure($closure);

        $s = Native::unserialize($wrapper);

        return $s;
    }


}
    
    
    
   
    
    
    
