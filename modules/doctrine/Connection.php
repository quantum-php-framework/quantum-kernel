<?php

namespace Quantum\Doctrine;

use Quantum\Config;
use Quantum\Doctrine;
use Quantum\InternalPathResolver;
use Quantum\Events\EventsManager;

use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Doctrine\ORM\Tools\Setup;
use Doctrine\DBAL\DriverManager;
use Doctrine\ORM\EntityManager;

/**
 * Class Connection
 * @package Quantum\Doctrine
 */
class Connection extends \Quantum\Singleton
{
    /**
     * @var \Doctrine\DBAL\Connection
     */
    private $connection;


    /**
     * @var EntityManager
     */
    private $entity_manager;

    /**
     * @var boolean
     */
    private $is_shutdown_being_observed;

    /**
     * Connection constructor.
     */
    protected function __construct()
    {

    }

    /**
     * @return \Doctrine\DBAL\Connection
     * @throws \Doctrine\DBAL\Exception
     */
    public function getConnection()
    {
        if (isset($this->connection)) {
            return $this->connection;
        }

        $connection_parameters = self::getConnectionParametersFromEnvironment();

        $this->connection = DriverManager::getConnection($connection_parameters);

        $this->observeShutdown();

        return $this->connection;
    }


    /**
     * @return EntityManager
     * @throws ORMException
     */
    public function getEntityManager()
    {
        if (isset($this->entity_manager)) {
            return $this->entity_manager;
        }

        $config = Config::getInstance();
        $ipt = InternalPathResolver::getInstance();

        $driver = Doctrine::getCacheDriver();

        $configuration = Setup::createAnnotationMetadataConfiguration(
            $paths = $ipt->getAllEntitiesPaths(),
            $isDevMode = $config->isDevelopmentEnvironment()
        );

        $configuration->setResultCacheImpl($driver);
        $configuration->setQueryCacheImpl($driver);
        $configuration->setMetadataCacheImpl($driver);

        $connection_parameters = self::getConnectionParametersFromEnvironment();

        $this->entity_manager = EntityManager::create($connection_parameters, $configuration);

        $this->observeShutdown();

        return $this->entity_manager;

    }


    /**
     * @return array|bool
     */
    public static function getConnectionParametersFromEnvironment()
    {
        $enviroment = Config::getInstance()->getEnvironment();

        if (!$enviroment) {
            return false;
        }

        $username = $enviroment->db_user;
        $password = $enviroment->db_password;
        $host = $enviroment->db_host;
        $database = $enviroment->db_name;

        $params = array(
            'url' => "mysql://$username:$password@$host/$database",
        );

        return $params;
    }

    /**
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function shutdown()
    {
        if (isset($this->entity_manager)) {
            try {
                $this->entity_manager->flush();
            }
            catch (ORMException $exception) {
                return;
            }
        }

        if (isset($this->connection)) {
            $this->connection->close();
        }
    }

    /**
     *
     */
    private function observeShutdown()
    {
        if (!$this->is_shutdown_being_observed)
        {
            EventsManager::getInstance()->addObserver('shutdown', [$this, 'shutdown'], 100, true);
            $this->is_shutdown_being_observed = true;
        }
    }
}