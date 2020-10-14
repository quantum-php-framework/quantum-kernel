<?php

namespace Quantum\Serialize\Serializer;

interface SerializerInterface
{
    /**
     * Serialize data into string
     *
     * @param string|int|float|bool|array|null $data
     * @return string|bool
     * @throws \InvalidArgumentException
     * @since 101.0.0
     */
    public static function serialize($data);
    /**
     * Unserialize the given string
     *
     * @param string $string
     * @return string|int|float|bool|array|null
     * @throws \InvalidArgumentException
     * @since 101.0.0
     */
    public static function unserialize($string);
}