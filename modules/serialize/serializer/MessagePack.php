<?php

namespace Quantum\Serialize\Serializer;

/**
 * Class Serializer
 * @package Quantum\Serialize
 */
class MessagePack implements SerializerInterface
{

    /**
     * {@inheritDoc}
     */
    public static function serialize($data)
    {
        $result = msgpack_pack($data);
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

        $result = msgpack_unpack($string);

        if (null === $result)
        {
            $error = error_get_last();
            throw new \InvalidArgumentException($error['message']);
        }

        return $result;
    }


}
    
    
    
   
    
    
    
