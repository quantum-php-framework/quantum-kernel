<?php

namespace Quantum\Cache\Backend;

use Exception;
use Quantum\Cache;
use Quantum\Cache\Backend;
use Quantum\Config;
use Predis\Client;
use Quantum\Cache\StorageSetupException;
use Quantum\ValueTree;

/**
 * Class Storage
 * @package Quantum\Redis
 */
class Redis extends Backend
{

    /**
     * @var Client
     */
    public $redis;

    /**
     *
     */
    const CRYPTO_SCHEME = 'shared-crypto://';

    /**
     * @var ValueTree
     */
    private $internal_cache;

    /**
     * Holds the diagnostics values.
     *
     * @var array
     */
    public $diagnostics = null;

    /**
     * Holds the error messages.
     *
     * @var array
     */
    public $errors = [];

    /**
     * bool
     */
    private $fail_gracefully;

    /**
     * Track how many requests were found in cache.
     *
     * @var int
     */
    public $cache_hits = 0;

    /**
     * Track how may requests were not cached.
     *
     * @var int
     */
    public $cache_misses = 0;

    /**
     * Track how long request took.
     *
     * @var int
     */
    public $cache_time = 0;

    /**
     * Track how may calls were made.
     *
     * @var int
     */
    public $cache_calls = 0;




    /**
     * Storage constructor.
     * @throws \Exception
     */
    public function __construct($initFromEnv = true)
    {
        Cache::enableRuntimeCache(false);

        $this->internal_cache = new ValueTree();

        $this->fail_gracefully = true;

        if ($initFromEnv)
            $this->initFromEnvironmentConfig();
    }

    /**
     * @throws \Exception
     */
    public function initFromEnvironmentConfig()
    {
        $config = new_vt(Config::getInstance()->getEnvironment());

        if (empty($config))
            throw new StorageSetupException('no active app config');

        if (!$config->has('redis_scheme'))
            throw new StorageSetupException('redis_scheme not defined in app config');

        if (!$config->has('redis_host'))
            throw new StorageSetupException('redis_host not defined in app config');

        if (!$config->has('redis_port'))
            throw new StorageSetupException('redis_port not defined in app config');

        if (!$config->has('redis_persistent'))
            throw new StorageSetupException('redis_persistent not defined in app config');

        $this->init($config->get('redis_scheme'),
            $config->get('redis_host'),
            $config->get('redis_port'),
            $config->get('redis_persistent'),
            $config->get('redis_password'));
    }

    /**
     * @param $scheme
     * @param $host
     * @param $port
     * @param $persistent
     * @param bool $password
     */
    public function init($scheme, $host, $port, $persistent, $password = false)
    {
        $client = 'Predis';

        $redis_config = array(
            'scheme' => $scheme,
            'host' => $host,
            'port' => $port,
            'database' => 0,
            'timeout' => 1,
            'read_write_timeout' => 1,
            'retry_interval' => null,
            'persistent' => $persistent);

        if (!empty($password)) {
            $redis_config['redis_password'] = $password;
        };

        try
        {
            $this->redis = new Client($redis_config);
            $this->redis->connect();

            $this->diagnostics = array_merge(
                [ 'client' => sprintf( '%s (v%s)', $client, Client::VERSION ) ],
                $redis_config,
                $redis_config);

            $this->diagnostics[ 'ping' ] = $this->redis->ping();

            $this->fetchInfo();

            $this->redis_connected = true;

            $this->internal_cache->clear();
        }
        catch (\Exception $exception)
        {
            $this->handleException($exception);
        }

    }


    /**
     * @param Exception $exception
     * @throws Exception
     */
    private function handleException(\Exception $exception)
    {
        $this->redis_connected = false;

        \ExternalErrorLoggerService::error('redis_exception', $exception->getMessage());

        if (!$this->fail_gracefully) {
            throw $exception;
        }

        $this->errors[] = $exception->getMessage();
    }


    /**
     * Convert Redis responses into something meaningful
     *
     * @param mixed $response Response sent from the redis instance.
     * @return mixed
     */
    protected function parseRedisResponse( $response ) {
        if ( is_bool( $response ) ) {
            return $response;
        }

        if ( is_numeric( $response ) ) {
            return $response;
        }

        if ( is_object( $response ) && method_exists( $response, 'getPayload' ) ) {
            return $response->getPayload() === 'OK';
        }

        return false;
    }

    /**
     * @param $key
     * @param $value
     */
    private function addToInternalCache($key, $value)
    {
        if ( is_object( $value ) ) {
            $value = clone $value;
        }

        $this->internal_cache->set($key, $value);
    }

    /**
     * @param $key
     * @return bool|mixed
     */
    private function getFromInternalCache($key, $fallback = false)
    {
        if (!$this->internal_cache->has($key)) {
            return $fallback;
        }

        $value = $this->internal_cache->get($key);

        if (is_object( $value )) {
            return clone $value;
        }

        return $value;
    }

    /**
     * @return mixed
     */
    public function isConnected()
    {
        return $this->redis_connected;
    }

    /**
     * @param $key
     * @param $var
     * @param int $expiration
     * @return mixed
     */
    public function set($key, $value, $expiration = 0)
    {
        $result = true;

        $start_time = microtime( true );

        if ($this->isConnected())
        {
            try
            {
                if ($expiration)
                {
                    $result = $this->redis->setex($key, $expiration, maybe_serialize($value));
                }
                else
                {
                    $result = $this->redis->set($key, maybe_serialize($value));
                }
            }
            catch (Exception $exception)
            {
                $this->handleException($exception);
                return false;
            }

            $this->cache_calls++;
            $this->cache_time += ( microtime( true ) - $start_time );
        }


        // If the set was successful, or we didn't go to redis.
        if ( $result ) {
            $this->addToInternalCache($key, $value);
        }

        return $value;
    }



    /**
     * @param $items
     * @param int $expiration
     * @return bool
     */
    public function setParams($items)
    {
        $result = true;

        $start_time = microtime( true );

        if ($this->isConnected())
        {
            try {
                $result = $this->parseRedisResponse($this->redis->mset($items));
            } catch ( Exception $exception ) {
                $this->handleException( $exception );
                return false;
            }

            $this->cache_calls++;
            $this->cache_time += ( microtime( true ) - $start_time );
        }

        if ($result)
        {
            foreach ($items as $key => $value){
                $this->addToInternalCache($key, $value);
            }
        };

        return $result;
    }


    /**
     * @param $key
     * @return string
     */
    public function get($key)
    {
        if ($this->internal_cache->has($key))
        {
            $this->cache_hits++;
            return $this->getFromInternalCache($key);
        }
        elseif (!$this->isConnected())
        {
            $this->cache_misses++;
            return false;
        }

        $start_time = microtime( true );

        try {
            $result = $this->redis->get($key);
        } catch (Exception $exception) {
            $this->handleException( $exception );
            return false;
        }

        $execute_time = microtime( true ) - $start_time;

        $this->cache_calls++;
        $this->cache_time += $execute_time;

        if ($result === null || $result === false)
        {
            $this->cache_misses++;
            return false;
        }
        else {
            $this->cache_hits++;
            $value = maybe_unserialize($result);
        }

        $this->addToInternalCache($key, $value);

        return $value;
    }

    /**
     * @param $key
     * @return int
     */
    public function has($key)
    {
        if ($this->internal_cache->has($key)) {
            return true;
        }
        elseif (!$this->isConnected()) {
            return false;
        }

        $start_time = microtime( true );

        try {
            $result = $this->parseRedisResponse( $this->redis->exists($key) );
        } catch ( Exception $exception ) {
            $this->handleException( $exception );
            return false;
        }

        $execute_time = microtime( true ) - $start_time;

        $this->cache_calls++;
        $this->cache_time += $execute_time;

        return $result;
    }


    /**
     * @return Client
     */
    public function getRedis()
    {
        return $this->redis;
    }

    /**
     * @return bool
     */
    public function flush($delay = 0)
    {
        $delay = abs(intval( $delay ));

        if ($delay) {
            sleep( $delay );
        };

        $result = true;

        $this->internal_cache->clear();

        if ($this->isConnected())
        {
            $start_time = microtime( true );

            try {
                $result = $this->parseRedisResponse($this->redis->flushdb());
            } catch ( Exception $exception ) {
                $this->handleException( $exception );
                return false;
            }

            $execute_time = microtime( true ) - $start_time;

            $this->cache_calls++;
            $this->cache_time += $execute_time;
        }

        return (bool) $result;
    }

    /**
     * @param $key
     * @param int $time
     * @return bool
     */
    public function delete($key)
    {
        $result = false;

        if ($this->internal_cache->has($key))
        {
            $this->internal_cache->remove($key);
            $result = true;
        }

        $start_time = microtime( true );

        if ($this->isConnected())
        {
            try {
                $result = $this->parseRedisResponse( $this->redis->del( (array)$key ) );
            } catch ( Exception $exception ) {
                $this->handleException( $exception );
                return false;
            }
        }

        $execute_time = microtime( true ) - $start_time;

        $this->cache_calls++;
        $this->cache_time += $execute_time;

        return (bool) $result;
    }


    /**
     * @param $key
     * @param int $offset
     * @param int $initial_value
     * @param int $expiry
     * @return int
     */
    public function increment($key, $offset = 1, $initial_value = 0, $expiry = 0)
    {
        $offset = (int) $offset;

        if (!$this->isConnected())
        {
            $value = $this->getFromInternalCache($key, $initial_value);
            $value += $offset;
            $this->addToInternalCache($key, $value);
            return $value;
        }

        $start_time = microtime( true );

        try {

            if ($this->has($key)) {
                $result = $this->parseRedisResponse( $this->redis->incrby($key, $offset) );
            } else {
                $result = $this->parseRedisResponse( $this->redis->set($key, ($initial_value + $offset)) );
            }

            if ($expiry) {
                $this->setExpiration($key, $expiry);
            };

            $this->addToInternalCache($key, (int) $this->redis->get($key));

        } catch ( Exception $exception ) {
            $this->handleException( $exception );
            return false;
        }

        $execute_time = microtime( true ) - $start_time;

        $this->cache_calls += 2;
        $this->cache_time += $execute_time;

        return $result;
    }


    /**
     * @param $key
     * @param int $offset
     * @param int $initial_value
     * @param int $expiry
     * @return int
     */
    public function decrement($key, $offset = 1, $initial_value = 0, $expiry = 0)
    {
        $offset = (int) $offset;

        if (!$this->isConnected())
        {
            $value = $this->getFromInternalCache($key, $initial_value);
            $value -= $offset;
            $this->addToInternalCache($key, $value);
            return $value;
        }

        $start_time = microtime( true );

        try {

            if ($this->has($key)) {
                $result = $this->parseRedisResponse( $this->redis->decrby($key, $offset) );
            } else {
                $result = $this->parseRedisResponse( $this->redis->set($key, ($initial_value - $offset)) );
            }

            if ($expiry) {
                $this->setExpiration($key, $expiry);
            };

            $this->addToInternalCache($key, (int) $this->redis->get($key));

        } catch ( Exception $exception ) {
            $this->handleException( $exception );
            return false;
        }

        $execute_time = microtime( true ) - $start_time;

        $this->cache_calls += 2;
        $this->cache_time += $execute_time;

        return $result;
    }


    /**
     * @param $key
     * @param int $expiration
     */
    public function setExpiration ($key, $expiration = 0)
    {
        if ($expiration > 0) {
            $this->redis->expire($key, $expiration);
        }
    }

    /**
     * @param $queue
     * @param $item
     * @return bool
     * @throws \Defuse\Crypto\Exception\BadFormatException
     * @throws \Defuse\Crypto\Exception\EnvironmentIsBrokenException
     */
    public function pushToListEncrypted($queue, $item)
    {
        if (empty($queue) || empty($item))
            return false;

        $scheme = self::CRYPTO_SCHEME;

        $data = serialize($item);

        $key = $this->getSharedEncryptionKey();

        $data = \Quantum\Crypto::encrypt($data, $key);

        $string = $scheme.$data;

        return $this->pushToList($queue, $string, false);
    }

    /**
     * @param $queue
     * @param $item
     * @param bool $serialize
     * @return bool
     */
    public function pushToList($queue, $item, $serialize = true)
    {
        $start_time = microtime( true );

        $length = 0;
        if ( $this->isConnected() ) {
            try {
                $length = $this->redis->rpush($queue, $item);
            } catch ( Exception $exception ) {
                $this->handleException( $exception );
                return false;
            }
        }

        $execute_time = microtime( true ) - $start_time;

        $this->cache_calls++;
        $this->cache_time += $execute_time;

        if ($length < 1) {
            return false;
        }
        return true;
    }


    /**
     * @return |null
     */
    private function getSharedEncryptionKey()
    {
        $conf =  \Quantum\Config::getInstance();

        if ($conf)
        {
            $env = $conf->getEnvironment();

            if (!empty($env))
            {
                if (isset($env->shared_encryption_key))
                    return $env->shared_encryption_key;
            }
        }

        $conf = \Quantum\Qubit::getConfig();

        if (isset($conf->shared_encryption_key))
            return $conf->shared_encryption_key;

        return null;
    }

    /**
     * @param $listname
     * @return mixed|null
     * @throws \Defuse\Crypto\Exception\BadFormatException
     * @throws \Defuse\Crypto\Exception\EnvironmentIsBrokenException
     */
    public function popFromList($listname)
    {
        $this->internal_cache->pop();

        $item = $this->redis->lpop($listname);

        if(!$item) {
            return null;
        }

        if (qs($item)->startsWith(self::CRYPTO_SCHEME))
        {
            $key = $this->getSharedEncryptionKey();

            if ($key)
                return unserialize(\Quantum\Crypto::decrypt(qs($item)->fromLastOccurrenceOf('/')->toStdString(), $key));
        }
        else
        {
            return unserialize($item);
        }
    }

    /**
     * @param $source
     * @param $dest
     * @return mixed|null
     */
    public function popAndPush($source, $dest)
    {
        $item = $this->redis->rpoplpush($source, $dest);

        if(!$item) {
            return null;
        }

        return unserialize($item);
    }

    /**
     * @param $listname
     * @return int
     */
    public function getListLength($listname)
    {
        return $this->redis->llen($listname);
    }

    /**
     * @param $listname
     * @return array
     */
    public function getList($listname)
    {
        return $this->redis->lrange($listname, 0, $this->getListLength($listname));
    }

    /**
     * @param $listname
     * @return int
     */
    public function flushList($listname)
    {
        $this->internal_cache->clear();

        return $this->redis->del($listname);
    }

    /**
     * @return mixed
     */
    public function isAvailable()
    {
        return $this->redis->ping();
    }


    /**
     *
     */
    public function fetchInfo()
    {
        $options = method_exists( $this->redis, 'getOptions' )
            ? $this->redis->getOptions()
            : new \stdClass();

        if ( isset( $options->replication ) && $options->replication ) {
            return;
        }

        $info = $this->redis->info();

        if ( isset( $info['redis_version'] ) ) {
            $this->redis_version = $info['redis_version'];
        } elseif ( isset( $info['Server']['redis_version'] ) ) {
            $this->redis_version = $info['Server']['redis_version'];
        }
    }


    /**
     * @return object
     */
    public function info()
    {
        $total = $this->cache_hits + $this->cache_misses;

        $bytes = array_map(
            function ( $keys ) {
                // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.serialize_serialize
                return strlen( serialize( $keys ) );
            },
            $this->internal_cache->_properties
        );

        return (object) [
            // Connected, Disabled, Unknown, Not connected
            'status' => '...',
            'hits' => $this->cache_hits,
            'misses' => $this->cache_misses,
            'ratio' => $total > 0 ? round( $this->cache_hits / ( $total / 100 ), 1 ) : 100,
            'bytes' => array_sum( $bytes ),
            'time' => $this->cache_time,
            'calls' => $this->cache_calls,
            'errors' => empty( $this->errors ) ? null : $this->errors,
            'meta' => [
                'Client' => $this->diagnostics['client'] ?: 'Unknown',
                'Redis Version' => $this->redis_version,
            ],
        ];
    }

    /**
     *
     */
    public function stats()
    {
        ?>
        <p>
            <strong>Redis Status:</strong>
            <?php echo $this->isConnected() ? 'Connected' : 'Not connected'; ?>
            <br />
            <strong>Redis Client:</strong>
            <?php echo $this->diagnostics['client'] ?: 'Unknown'; ?>
            <br />
            <strong>Cache Calls:</strong>
            <?php echo intval( $this->cache_calls ); ?>
            <br />
            <strong>Cache Hits:</strong>
            <?php echo intval( $this->cache_hits ); ?>
            <br />
            <strong>Cache Misses:</strong>
            <?php echo intval( $this->cache_misses ); ?>
            <br />
            <strong>Cache Time:</strong>
            <?php echo $this->cache_time; ?>
            <br />
            <strong>Cache Size:</strong>
            <?php echo number_format( strlen( serialize( $this->internal_cache ) ) / 1024, 2 ); ?> kB
        </p>
        <?php
    }


}