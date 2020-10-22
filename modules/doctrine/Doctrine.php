<?php

namespace Quantum;

use Doctrine\DBAL\Statement;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\NativeQuery;
use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Common\Cache\ArrayCache;
use Doctrine\Common\Cache\ApcuCache;
use Doctrine\Common\Cache\RedisCache;
use Doctrine\Common\Cache\MemcachedCache;
use Doctrine\Common\Cache\PhpFileCache;
use Doctrine\Common\Cache\ChainCache;

use Quantum\Doctrine\Connection;
use Quantum\Cache\Backend\Memcache;


/**
 * Class Doctrine
 * @package DoctrinePlugin
 */
class Doctrine
{
    /**
     * @param $string
     * @return mixed
     */
    public static function executeQuery($string)
    {
        $query = self::createQuery($string);

        return $query->getResult();
    }

    /**
     * @param $string
     * @return mixed
     */
    public static function executeRegularSqlQuery($string)
    {
        $query = self::createRegularSqlQuery($string);

        return $query->getResult();
    }

    /**
     * @param $string
     * @return Query
     */
    public static function createQuery($string)
    {
        $manager = self::getEntityManager();

        $query = $manager->createQuery($string);

        return $query;
    }

    /**
     * @param $string
     * @return NativeQuery
     */
    public static function createRegularSqlQuery($string)
    {
        $manager = self::getEntityManager();

        $query = $manager->createNativeQuery($string);

        return $query;
    }

    /**
     * @param $string
     * @return QueryBuilder
     */
    public static function createQueryBuilder()
    {
        $manager = self::getEntityManager();

        return $manager->createQueryBuilder();
    }

    /**
     * @param $string
     * @return Statement
     */
    public static function getQueryStatement($string)
    {
        $connection = Connection::getInstance()->connection;
        return $connection->query($string);
    }

    /**
     * @param $string
     * @return Statement
     */
    public static function prepareStatement($string)
    {
        $connection = Connection::getInstance()->connection;
        return $connection->prepare($string);
    }

    /**
     * @return \Doctrine\DBAL\Connection
     */
    public static function getConnection()
    {
        $connection = Connection::getInstance()->getConnection();
        return $connection;
    }

    /**
     * @return EntityManager
     */
    public static function getEntityManager()
    {
        $connection = Connection::getInstance()->getEntityManager();
        return $connection;
    }


    /**
     * @param null $backend
     * @return ApcuCache|ArrayCache|ChainCache|MemcachedCache|PhpFileCache|RedisCache
     */
    public static function getCacheDriver($backend = 'chain')
    {
        if (Config::getInstance()->isDevelopmentEnvironment()) {
            return new ArrayCache();
        }

        switch ($backend)
        {
            default:        return new ArrayCache(); break;
            case 'apcu':    return new ApcuCache(); break;
            case 'chain':   return self::getChainCacheDriver(['array', 'redis', 'files']); break;

            case 'redis':

                $redis = new \Redis();
                $redis->connect('127.0.0.1', 6379);

                $cacheDriver = new RedisCache();
                $cacheDriver->setRedis($redis);
                return $cacheDriver;

                break;

            case 'memcached':

                $memcached = Memcache::getInstance()->getMemcache();

                $cacheDriver = new MemcachedCache();
                $cacheDriver->setMemcached($memcached);
                return $cacheDriver;

                break;

            case 'files':

                $cache_dir = qf(InternalPathResolver::getInstance()->getCacheRoot())->getChildFile('doctrine-cache');

                if (!$cache_dir->isDirectory()) {
                    $cache_dir->create();
                }
                return new PhpFileCache($cache_dir->getRealPath());

                break;
        }
    }


    /**
     * @param string[] $backends
     * @return \Doctrine\Common\Cache\ChainCache
     */
    public static function getChainCacheDriver($backends = ['array', 'redis', 'files'])
    {
        $drivers = new_vt();

        foreach ($backends as $backend)
        {
            $driver = self::getCacheDriver($backend);
            $drivers->add($driver);
        }

        return new ChainCache($drivers->toStdArray());
    }

    /**
     * @param $class_name
     * @throws \Doctrine\DBAL\ConnectionException
     * @throws \Doctrine\DBAL\Exception
     */
    public static function truncate($class_name)
    {
        if (Config::getInstance()->isProductionEnvironment()) {
            throw_exception('Truncate is not allowed in production');
        }

        $em = Doctrine::getEntityManager();
        $cmd = $em->getClassMetadata($class_name);
        $connection = $em->getConnection();
        $dbPlatform = $connection->getDatabasePlatform();
        $connection->beginTransaction();
        try {
            $connection->query('SET FOREIGN_KEY_CHECKS=0');
            $q = $dbPlatform->getTruncateTableSql($cmd->getTableName());
            $connection->executeUpdate($q);
            $connection->query('SET FOREIGN_KEY_CHECKS=1');
            $connection->commit();
        }
        catch (\Exception $e) {
            $connection->rollback();
        }
    }
}