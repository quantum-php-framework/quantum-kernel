<?php

namespace Quantum;

/**
 * Class GeoIp
 * @package Quantum
 */
class GeoIp
{
    /**
     * GeoIp constructor.
     * @param $ip
     * @param bool $fetch
     */
    function __construct($ip, $fetch = true)
    {
        $this->ip = $ip;

        if ($fetch)
            $this->fetchData();
    }

    /**
     * @return mixed
     */
    public function getData()
    {
        if (empty($this->data))
        {
            $this->fetchData();
        }

        return $this->data;
    }

    /**
     *
     */
    public function fetchData()
    {
        $data = json_decode(file_get_contents("http://ip-api.com/json/".$this->ip));

        if ($data)
        {
            $data = new_vt($data);
            $data->changeKeysCase();

            if ($data->get('status') === "success")
                $this->data = $data;
            else
                $this->data = false;


        }
        else
        {
            //$data = json_decode(file_get_contents("http://ip-api.com/json/".$this->ip));
            //http://api.ipstack.com/189.216.122.54?access_key=bde6f28a7d2c6a4cd89882818215bbb4
        }

    }


    /**
     * @param $ip
     * @return string
     */
    public static function getCountry($ip)
    {
        if (in_array($ip, Request::getLocalHostIps()))
            return "localhost";

        $geoip = new GeoIp($ip);
        $data = $geoip->getData();

        if (!empty($data))
            return $data->get('country');

        return "unknown";
    }

    /**
     * @param $ip
     * @return string
     */
    public static function getCountryCode($ip)
    {
        if (in_array($ip, Request::getLocalHostIps()))
            return "localhost";

        $geoip = new GeoIp($ip);
        $data = $geoip->getData();

        if (!empty($data))
            return $data->get('countrycode');

        return "unknown";
    }





}