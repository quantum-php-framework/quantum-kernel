<?php

namespace Quantum;


/**
 * Class NanoId
 * @package Quantum
 */
class NanoId
{

    /**
     * @param int $size
     * @param int $mode
     * @return string
     */
    public static function generate($size = 21, $mode = \Hidehalo\Nanoid\Client::MODE_DYNAMIC)
    {
        $client = new \Hidehalo\Nanoid\Client();

        return $client->generateId($size, $mode);
    }

}