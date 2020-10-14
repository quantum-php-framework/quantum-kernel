<?php

namespace Quantum;

use GeoIp2\Database\Reader;

/**
 * Class MaxmindGeoIp
 * @package Quantum
 */
class MaxmindGeoIp
{
    /**
     * @var
     */
    public $reader;
    /**
     * @var
     */
    public $record;

    /**
     * MaxmindGeoIp constructor.
     * @param $ip
     * @param bool $fetch
     */
    function __construct($ip)
    {
        $this->db_loaded = false;

        $qs_ip = qs($ip);

        if ($qs_ip->isIpV4())
        {
            $this->ip = $ip;
            $this->readDb();
        }
        elseif ($qs_ip->contains(','))
        {
            $ips = $qs_ip->explode(',');

            if (!empty($ips))
            {
                $possible_ip = $ips[0];

                if (qs($possible_ip)->isIpV4()) {
                    $this->ip = $possible_ip;
                    $this->readDb();
                }
            }
        }


    }

    public function databaseLoaded()
    {
        return $this->db_loaded;
    }

    /**
     * @throws \GeoIp2\Exception\AddressNotFoundException
     * @throws \MaxMind\Db\Reader\InvalidDatabaseException
     */
    public function readDb()
    {
        $this->reader = new Reader(self::getDbFileIfNeeded());

        try {
            $this->record = $this->reader->country($this->ip);
            $this->db_loaded = true;
        }
        catch (\Exception $exception)
        {
            \ExternalErrorLoggerService::error('maxmind_geoip_error', ['ip' => $this->ip, 'exception' => $exception->getMessage()]);
        }

    }

    /**
     * @return mixed
     * @throws \Exception
     */
    public static function downloadDb()
    {
        $filename = "GeoLite2-Country.tar.gz";
        $db_filename = "GeoLite2-Country.mmdb";
        //$license_key = Config::getKernelSetting('maxmind_license_key');

        $url = "https://download.maxmind.com/app/geoip_download?edition_id=GeoLite2-Country&license_key=GOji8Rgl1VGn9zef&suffix=tar.gz";

        $request = new CurlRequest($url);
        $request->execute();

        $data = $request->getResponse();

        $etc = new File(InternalPathResolver::getInstance()->etc_root);

        $geoip_location = $etc->getChildFile('geoip');

        if ($geoip_location->exists())
            $geoip_location->delete();

        $db_location = $geoip_location->getChildFile('db');

        $compressed_file_location = $db_location->getChildFile($filename);
        $compressed_file_location->replaceContent($data);

        $phar = new \PharData(($compressed_file_location->getPath()));

        $phar->extractTo($db_location->getParentDirPath());

        if ($geoip_location->containsSubDirectories())
        {
            $dbFile = $geoip_location->findFileInChildDirectories($db_filename);

            if ($dbFile->exists())
            {
                $target = $db_location->getChildFile($db_filename)->getPath();
                $dbFile->move($target);
                $geoip_location->deleteAllChildFilesExcept($db_filename);
                return $target;
            }

        }


    }

    /**
     * @return File
     */
    public static function getDbFile()
    {
        $etc = new File(InternalPathResolver::getInstance()->etc_root);

        $geoip_location = $etc->getChildFile('geoip');

        $db_location = $geoip_location->getChildFile('db');

        return $db_location->getChildFile("GeoLite2-Country.mmdb");
    }

    /**
     * @return bool|QString
     * @throws \Exception
     */
    public function getDbFileIfNeeded()
    {
        $file = self::getDbFile();

        if (!$file->exists())
            self::downloadDb();

        return $file->getRealPath();
    }


    /**
     * @param $ip
     * @return string
     */
    public static function getCountry($ip)
    {
        if (in_array($ip, Request::getLocalHostIps()))
            return "localhost";

        $country = 'unkown';

        $geoip = new MaxmindGeoIp($ip);

        if (!$geoip->databaseLoaded())
            return $country;

        try {
            $country = $geoip->record->country->name;
        }
        catch (\Exception $exception)
        {
            \ExternalErrorLoggerService::error('maxmind_geoip_error', ['ip' => $ip, 'exception' => $exception->getMessage()]);
        }

        return $country;

    }


    /**
     * @param $ip
     * @return string
     */
    public static function getCountryCode($ip)
    {
        if (in_array($ip, Request::getLocalHostIps()))
            return "localhost";

        $country = 'unkown';

        $geoip = new MaxmindGeoIp($ip);

        if (!$geoip->databaseLoaded())
            return $country;

        try {
            $country = $geoip->record->country->isoCode;
        }
        catch (\Exception $exception)
        {
            \ExternalErrorLoggerService::error('maxmind_geoip_error', ['ip' => $ip, 'exception' => $exception->getMessage()]);
        }

        return $country;
    }





}