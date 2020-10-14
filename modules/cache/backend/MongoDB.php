<?php

namespace Quantum\Cache\Backend;

use Quantum\Cache\Backend;
use Quantum\Cache\StorageSetupException;

/**
 * Class Storage
 * @package Quantum\Redis
 */
class MongoDB extends Backend
{
    /**
     * Infinite expiration time
     */
    const EXPIRATION_TIME_INFINITE = 0;

    /**#@-*/
    protected $_collection = null;

    /**
     * List of available options
     *
     * @var array
     */
    protected $_options = [
        'connection_string' => 'mongodb://localhost:27017', // MongoDB connection string
        'mongo_options' => [], // MongoDB connection options
        'db' => '', // Name of a database to be used for cache storage
        'collection' => 'cache', // Name of a collection to be used for cache storage
    ];


    /**
     * @var
     */
    private $_notTestCacheValidity;


    /**
     * Storage constructor.
     * @throws \Exception
     */
    public function __construct($initFromEnv = true)
    {
        if (!extension_loaded('mongo'))
            throw new StorageSetupException("'mongo' extension not loaded");

        if (!version_compare(\Mongo::VERSION, '1.2.11', '>='))
            throw new StorageSetupException("'mongo' extension min_version: 1.2.11");

        if  ($initFromEnv)
            $this->initFromEnvironmentConfig();
    }


    /**
     * @throws StorageSetupException
     */
    private function initFromEnvironmentConfig()
    {
        $config = new_vt(Config::getInstance()->getEnvironment());

        if (empty($config))
            throw new StorageSetupException('no active environment config');

        if (!$config->has('mongo_db'))
            throw new StorageSetupException('mongo_db not defined in environment config');

        $this->_options['db'] = $config->get('mongo_db');

        if ($config->has('mongo_connection_string'))
            $this->_options['connection_string'] = $config->get('mongo_connection_string');

        if ($config->has('mongo_options'))
            $this->_options['mongo_options'] = $config->get('mongo_optionss');

    }

    /**
     * @return \MongoCollection|null
     * @throws \MongoConnectionException
     */
    protected function _getCollection()
    {
        if (null === $this->_collection) {
            $connection = new \Mongo($this->_options['connection_string'], $this->_options['mongo_options']);
            $database = $connection->selectDB($this->_options['db']);
            $this->_collection = $database->selectCollection($this->_options['collection']);
        }
        return $this->_collection;
    }


    /**
     * @param $key
     * @param $var
     * @param int $expiration
     * @return mixed
     */
    public function set($key, $var, $expiration = 0)
    {
        $lifetime = $expiration;

        $time = time();
        $expire = $lifetime === null ? self::EXPIRATION_TIME_INFINITE : $time + $lifetime;
        $tags = array_map([$this, '_quoteString'], [$var]);
        $document = [
            '_id' => $this->_quoteString($key),
            'data' => new \MongoBinData($this->_quoteString($var), \MongoBinData::BYTE_ARRAY),
            'tags' => $tags,
            'mtime' => $time,
            'expire' => $expire,
        ];

        return $this->_getCollection()->save($document);
    }

    /**
     * @param $key
     * @return string
     */
    public function get($key)
    {
        $query = ['_id' => $this->_quoteString($key)];
        if (!$this->_notTestCacheValidity) {
            $query['$or'] = [
                ['expire' => self::EXPIRATION_TIME_INFINITE],
                ['expire' => ['$gt' => time()]],
            ];
        }
        $result = $this->_getCollection()->findOne($query, ['data']);
        return $result ? $result['data']->bin : false;
    }

    /**
     * @param $key
     * @return int
     */
    public function has($key)
    {
        $result = $this->_getCollection()->findOne(
            [
                '_id' => $this->_quoteString($key),
                '$or' => [
                    ['expire' => self::EXPIRATION_TIME_INFINITE],
                    ['expire' => ['$gt' => time()]],
                ],
            ],
            ['mtime']
        );
        return $result ? $result['mtime'] : false;
    }


    /**
     * @return bool
     */
    public function flush()
    {
        return (bool) $this->_getCollection()->drop();
    }

    /**
     * @param $key
     * @param int $time
     * @return bool
     */
    public function delete($key)
    {
        return $this->_getCollection()->remove(['_id' => $this->_quoteString($key)]);
    }


    /**
     * @return mixed
     */
    public function getNotTestCacheValidity()
    {
        return $this->_notTestCacheValidity;
    }

    /**
     * @param mixed $notTestCacheValidity
     */
    public function setNotTestCacheValidity($notTestCacheValidity)
    {
        $this->_notTestCacheValidity = $notTestCacheValidity;
    }

    /**
     * Quote specified value to be used in query as string
     *
     * @param string $value
     * @return string
     */
    protected function _quoteString($value)
    {
        return (string)$value;
    }


}