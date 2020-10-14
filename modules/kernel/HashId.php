<?php

namespace Quantum;

/**
 * Class HashId
 * @package Quantum
 */
class HashId
{
    /**
     * @param $data
     * @return bool|string
     */
    public static function encode($data)
    {
        $provider = self::getHashidProvider();
        $id = $provider->encode($data);
        return $id;
    }

    /**
     * @param $id
     * @return array
     */
    public static function decode($id)
    {
        $provider = self::getHashidProvider();
        $data = $provider->decode($id);
        return $data;
    }

    /**
     * @return \Hashids\Hashids
     * @throws \Exception
     */
    private static function getHashidProvider()
    {
        $salt = qs(Config::getInstance()->getKernelConfig()->get('hashids_salt'))->sha1();
        $provider = new \Hashids\Hashids($salt);
        return $provider;
    }
}