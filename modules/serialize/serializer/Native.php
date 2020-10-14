<?php

namespace Quantum\Serialize\Serializer;

/**
 * Class Serializer
 * @package Quantum\Serialize
 */
class Native implements SerializerInterface
{

    /**
     * {@inheritDoc}
     */
    public static function serialize($data)
    {
        if (is_resource($data))
        {
            throw new \InvalidArgumentException('Unable to serialize value.');
        }

        return serialize($data);
    }


    /**
     * {@inheritDoc}
     */
    public static function unserialize($string)
    {
        if (false === $string || null === $string || '' === $string)
        {
            throw new \InvalidArgumentException('Unable to unserialize value.');
        }

        set_error_handler(
            function () {
                restore_error_handler();
                throw new \InvalidArgumentException('Unable to unserialize value, string is corrupted.');
            },
            E_NOTICE
        );

        $result = unserialize($string, ['allowed_classes' => false]);

        restore_error_handler();

        return $result;
    }


}
    
    
    
   
    
    
    
