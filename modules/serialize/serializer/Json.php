<?php

namespace Quantum\Serialize\Serializer;

/**
 * Class Serializer
 * @package Quantum\Serialize
 */
class Json implements SerializerInterface
{

    /**
     * @inheritDoc
     * @since 101.0.0
     */
    public static function serialize($data)
    {
        $result = json_encode($data);
        if (false === $result) {
            throw new \InvalidArgumentException('Unable to serialize value.');
        }
        return $result;
    }


    /**
     * @inheritDoc
     * @since 101.0.0
     */
    public static function unserialize($data)
    {
        $result = json_decode($data, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \InvalidArgumentException('Unable to unserialize value.');
        }
        return $result;
    }


}
    
    
    
   
    
    
    
