<?php

namespace Quantum\Cache;

use Quantum\Cache\Backend\APC;
use Quantum\Cache\Backend\Database;
use Quantum\Cache\Backend\Eaccelerator;
use Quantum\Cache\Backend\EncryptedFileBasedCacheStorage;
use Quantum\Cache\Backend\FilesBasedCacheStorage;
use Quantum\Cache\Backend\Memcache;
use Quantum\Cache\Backend\MongoDB;
use Quantum\Cache\Backend\Redis;
use Quantum\Cache\Backend\Xcache;
use Quantum\Config;
use Quantum\Events\EventsManager;
use Quantum\Singleton;

/**
 * Class CacheBackendInitException
 * @package Quantum\Cache
 */
class CacheBackendInitException extends \Exception {};

/**
 * Class ServiceProvider
 * @package Quantum\Cache
 */
class ServiceProvider extends Singleton
{
    /**
     *
     */
    const EventPrefix = 'Quantum/Events/Cache/ServiceProvider/Backend/';
    /**
     *
     */
    const BackendInitEvent   = self::EventPrefix.'Init';
    /**
     *
     */
    const BackendChangeEvent = self::EventPrefix.'Change';
    /**
     *
     */
    const BackendFlushEvent  = self::EventPrefix.'Flush';

    /**
     * @var mixed
     */
    public $storage;

    /**
     * ServiceProvider constructor.
     */
    public function __construct()
    {
        $this->initFromEnvironmentConfig();
    }

    /**
     * @throws CacheBackendInitException
     */
    public function initFromEnvironmentConfig()
    {
        $config = new_vt(Config::getInstance()->getEnvironment());

        if (empty($config))
            throw new CacheBackendInitException('no active environment config');

        if ($config->has('cache_backend'))
        {
            $backend = $config->get('cache_backend');
            $this->setDriver($backend, false);
            EventsManager::getInstance()->dispatch(self::BackendInitEvent, $backend);
        }
        else
        {
            $this->initFileBased();
        }

    }


    /**
     * @param $driver
     * @param bool $sendEvent
     * @return mixed
     * @throws CacheBackendInitException
     */
    public function setDriver ($driver, $sendEvent = true)
    {
        switch ($driver)
        {
            case 'memcache':
                $this->initMemcache();
                break;

            case 'redis':
                $this->initRedis();
                break;

            case 'files':
                $this->initFileBased();
                break;

            case 'encrypted':
                $this->initEncryptedFileBased();
                break;

            case 'apc':
                $this->initApc();
                break;

            case 'db':
                $this->initDatabaseStorage();
                break;

            case 'eaccelerator':
                $this->initEaccelerator();
                break;

            case 'mongodb':
                $this->initMongoDB();
                break;

            case 'xcache':
                $this->initXcache();
                break;

            default:
                throw new CacheBackendInitException('Invalid Cache Backend: '.$driver);
                break;
        }

        if ($sendEvent)
            EventsManager::getInstance()->dispatch(self::BackendChangeEvent, $driver);

        return $this->getStorage();
    }

    /**
     * @param $key
     * @param $value
     * @param int $expiration
     * @return mixed
     */
    public function set($key, $value, $expiration = 0)
    {
        return $this->storage->set($key, $value, $expiration);
    }

    /**
     * @param $key
     * @return mixed
     */
    public function get($key)
    {
        return $this->storage->get($key);
    }

    /**
     * @param $key
     * @return mixed
     */
    public function has($key)
    {
        return $this->storage->has($key);
    }

    /**
     * @param $key
     * @param int $offset
     * @param int $initial_value
     * @param int $expiry
     * @return mixed
     */
    public function increment($key, $offset = 1, $initial_value = 0, $expiry = 0)
    {
        return $this->storage->increment($key, $offset, $initial_value, $expiry);
    }

    /**
     * @param $key
     * @param int $offset
     * @param int $initial_value
     * @param int $expiry
     * @return mixed
     */
    public function decrement($key, $offset = 1, $initial_value = 0, $expiry = 0)
    {
        return $this->storage->decrement($key, $offset, $initial_value, $expiry);
    }

    /**
     * @param $items
     * @return mixed
     */
    public function setParams($items)
    {
        return $this->storage->setParams($items);
    }

    /**
     * @param $key
     * @param int $expiration
     * @return mixed
     */
    public function setExpiration ($key, $expiration = 0)
    {
        return $this->storage->setExpiration($key, $expiration);
    }

    /**
     * @param $key
     * @return mixed
     */
    public function delete($key)
    {
        return $this->storage->delete($key);
    }

    /**
     * @param $key
     * @param $var
     * @return mixed
     */
    public function replace($key, $var)
    {
        return $this->storage->replace($key, $var);
    }


    /**
     * @throws \Exception
     */
    public function initMemcache()
    {
        $this->storage = Memcache::getInstance();
        return $this->storage;
    }

    /**
     * @throws \Exception
     */
    public function initRedis()
    {
        $this->storage = Redis::getInstance();
        return $this->storage;
    }

    /**
     * @throws \Exception
     */
    public function initFileBased()
    {
        $this->storage = FilesBasedCacheStorage::getInstance();
        return $this->storage;
    }

    /**
     * @throws \Exception
     */
    public function initDatabaseStorage()
    {
        $this->storage = Database::getInstance();
        return $this->storage;
    }


    /**
     * @throws \Exception
     */
    public function initEncryptedFileBased()
    {
        $this->storage = EncryptedFileBasedCacheStorage::getInstance();
        return $this->storage;
    }

    /**
     * @return mixed
     */
    public function initApc()
    {
        $this->storage = APC::getInstance();
        return $this->storage;
    }

    /**
     * @return mixed
     */
    public function initEaccelerator()
    {
        $this->storage = Eaccelerator::getInstance();
        return $this->storage;
    }

    /**
     * @return mixed
     */
    public function initMongoDB()
    {
        $this->storage = MongoDB::getInstance();
        return $this->storage;
    }

    /**
     * @return mixed
     */
    public function initXcache()
    {
        $this->storage = Xcache::getInstance();
        return $this->storage;
    }

    /**
     * @return mixed
     */
    public function flush()
    {
        $result = $this->storage->flush();

        EventsManager::getInstance()->dispatch(self::BackendFlushEvent, true);

        return $result;
    }

    /**
     * @return mixed
     */
    public function getStorage()
    {
        return $this->storage;
    }

    /**
     * @param $driver
     * @return mixed
     */
    public function storage($driver)
    {
        switch ($driver)
        {
            case 'memcache':
                return Memcache::getInstance();
                break;

            case 'redis':
                return Redis::getInstance();
                break;

            case 'files':
                return FilesBasedCacheStorage::getInstance();
                break;

            case 'encrypted':
                return EncryptedFileBasedCacheStorage::getInstance();
                break;

            case 'apc':
                return APC::getInstance();
                break;

            case 'db':
                return Database::getInstance();
                break;

            case 'eaccelerator':
                return Eaccelerator::getInstance();
                break;

            case 'mongodb':
                return MongoDB::getInstance();
                break;

            case 'xcache':
                return Xcache::getInstance();
                break;
        }
    }

}