<?php

/**
 * Class QM
 */
class QM
{
    /**
     * @param $key
     * @param $value
     * @return mixed
     */
    static function register($key, $value)
    {
        return \Quantum\RuntimeRegistry::set($key, $value);
    }

    /**
     * @param $key
     * @param bool $fallback
     * @return mixed
     */
    static function registry($key, $fallback = false)
    {
        return \Quantum\RuntimeRegistry::get($key, $fallback);
    }

    /**
     * @return Quantum\HostedApp
     */
    static function app()
    {
        return \Quantum\Kernel::getInstance()->app;
    }

    /**
     * @return Quantum\Kernel
     */
    static function kernel()
    {
        return \Quantum\Kernel::getInstance();
    }

    /**
     * @return Quantum\InternalPathResolver
     */
    static function ipt()
    {
        return \Quantum\InternalPathResolver::getInstance();
    }

    /**
     * @return Quantum\Client
     */
    static function client()
    {
        return \Quantum\Client::getInstance();
    }

    /**
     * @return Quantum\Output
     */
    static function output()
    {
        return \Quantum\Output::getInstance();
    }

    /**
     * @return Smarty
     */
    static function smarty()
    {
        return \Quantum\Output::getInstance()->smarty;
    }

    /**
     * @return Quantum\Cookies
     */
    static function cookies()
    {
        return \Quantum\Cookies::getInstance();
    }

    /**
     * @return Quantum\Config
     */
    static function environment()
    {
        return self::config()->getEnvironment();
    }

    /**
     * @return Quantum\Config
     */
    static function config()
    {
        return \Quantum\Config::getInstance();
    }

    /**
     * @return Quantum\Request
     */
    static function request()
    {
        return \Quantum\Request::getInstance();
    }

    /**
     * @return Quantum\Logger
     */
    static function logger()
    {
        return \Quantum\Logger::getInstance();
    }

    /**
     * @return Quantum\Session
     */
    static function session()
    {
        return \Quantum\Session::getInstance();
    }

    /**
     * @param $var
     */
    static function throwInvalidParametersIfEmpty($var)
    {
        Quantum\Exception::throwInvalidParametersIfEmpty($var);
    }

    /**
     * @param $var
     */
    static function throwResourceNotFoundIfEmpty($var)
    {
        Quantum\Exception::throwResourceNotFoundIfEmpty($var);
    }

    /**
     * @param int $statusCode
     * @param null $statusPhrase
     * @param array $headers
     * @throws \Quantum\HttpException
     */
    static function throwHttpException($statusCode = 500, $statusPhrase = null, array $headers = array())
    {
        Quantum\Exception::throwHttpException($statusCode, $statusPhrase, $headers);
    }

    /**
     * @param string $error_name
     * @param string $error_description
     * @param string $http_code
     */
    static function throwCustomApiException($error_name = "", $error_description = "", $http_code = "400")
    {
        Quantum\Exception::throwCustomApiException($http_code, $error_name, $error_description);
    }

    /**
     * @param $var
     * @param string $error_name
     * @param string $error_description
     * @param string $http_code
     */
    static function throwCustomApiExceptionIfEmpty($var, $error_name = "", $error_description = "", $http_code = "400")
    {
        if (empty($var)) { self::throwCustomApiException($error_name, $error_description, $http_code); };
    }

    /**
     * @param int $status
     */
    static function shutdown($status = 0)
    {
        self::kernel()->shutdown($status);
    }

    /**
     * @param $system_url
     * @param $items_count
     * @return \Quantum\Paginator
     */
    static function getPages($system_url, $items_count)
    {
        return new Quantum\Paginator($system_url, $items_count);
    }

    /**
     * @param $uri
     * @param bool $addLastSlash
     * @return mixed
     */
    static function buildURL($uri, $addLastSlash = true)
    {
        return self::request()->genFullURLFromURI($uri, $addLastSlash);
    }

    /**
     * @return false|string
     */
    static function date()
    {
        return date("Y-m-d h:i:sa");
    }

    /**
     * @param $event_key
     * @param $listener
     * @param bool $callOnlyOnce
     * @return mixed
     */
    static function observe($event_key, $listener, $callOnlyOnce = false, $shouldPassEvent = true, $shouldPassData = true)
    {
        return Quantum\Events\EventsManager::getInstance()->addObserver($event_key, $listener, $callOnlyOnce, $shouldPassEvent, $shouldPassData);
    }

    /**
     * @param $event_key
     * @param $listener
     * @return mixed
     */
    static function observeOnce($event_key, $listener)
    {
        return Quantum\Events\EventsManager::getInstance()->addSingleCallObserver($event_key, $listener);
    }


    /**
     * @param $event_key
     * @param null $caller
     * @param bool $failedIfNotFound
     * @return mixed
     */
    static function dispatchEvent($event_key, $caller = null, $failedIfNotFound = false)
    {
        return Quantum\Events\EventsManager::getInstance()->dispatch($event_key, $caller, $failedIfNotFound);
    }

    /**
     * @return Quantum\Events\EventsManager
     */
    static function events()
    {
        return Quantum\Events\EventsManager::getInstance();
    }

    /**
     * @return Quantum\Redis\Storage
     */
    static function redis()
    {
        return Quantum\Redis\Storage::getInstance();
    }


    /**
     * @return Quantum\Cache\Memcache
     */
    static function memcache()
    {
        return Quantum\Cache\Memcache::getInstance();
    }

    /**
     * @return Quantum\AppResourcesManager
     */
    static function appResourcesManager()
    {
        return \Quantum\AppResourcesManager::getInstance();
    }


}


