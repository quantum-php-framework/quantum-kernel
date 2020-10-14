<?php

namespace Quantum\Serialize\Serializer;

class Base64Json implements SerializerInterface
{
    /**
     * @inheritdoc
     */
    public static function serialize($data)
    {
        return base64_encode(json_encode($data));
    }

    /**
     * Unserialize the given string with base64 and json.
     * Falls back to the json-only decoding on failure.
     *
     * @param string $string
     * @return string|int|float|bool|array|null
     */
    public static function unserialize($string)
    {
        $result = json_decode(base64_decode($string), true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            return json_decode($string, true);
        }
        return $result;
    }
}