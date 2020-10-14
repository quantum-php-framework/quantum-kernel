<?php

namespace Quantum\Serialize\Serializer;

/**
 * Class Serializer
 * @package Quantum\Serialize
 */
class IgBinary implements SerializerInterface
{

    /**
     * {@inheritDoc}
     */
    public static function serialize($data)
    {
        $result = igbinary_serialize($data);
        if ($result === false)
        {
            $error = error_get_last();
            throw new \InvalidArgumentException($error['message']);
        }
        return $result;
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

        $result = igbinary_unserialize($string);

        if (null === $result)
        {
            $error = error_get_last();
            throw new \InvalidArgumentException($error['message']);
        }

        return $result;
    }


}
    
    
    
   
    
    
    
