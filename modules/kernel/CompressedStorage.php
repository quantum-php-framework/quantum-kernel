<?php

namespace Quantum;

/**
 * Class CompressedStorage
 * @package Quantum
 */
class CompressedStorage
{

    /**
     * @param $string
     * @return string
     */
    public static function compress($string)
    {
        return base64_encode(gzcompress($string, 9));
    }

    /**
     * @param $string
     * @return string
     */
    public static function extract($string)
    {
        return gzuncompress(base64_decode($string));
    }

    /**
     * @param $data
     * @return string
     */
    public static function serializeAndCompress($data)
    {
        return self::compress(serialize($data));
    }

    /**
     * @param $string
     * @return mixed
     */
    public static function extractAndUnserialize($string)
    {
        return unserialize(self::extract($string));
    }


}