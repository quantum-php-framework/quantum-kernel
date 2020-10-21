<?php

namespace Quantum;

use Phinx\Config\Config;
use Phinx\Migration\Manager;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\ConsoleOutput;


class PhinxMigrationRunner
{
    public function __construct($root_path)
    {
        $this->base = $root_path;

        $input =  new ArrayInput([]);
        $output = new ConsoleOutput(); //This is a symfony component

        $this->phinx = new Manager(
            $this->getConfig(),
            $input,
            $output
        );
    }

    public function executeMigrations()
    {
        $this->phinx->migrate('production');
    }

    public function executeSeeds()
    {
        $this->phinx->seed('production');
    }

    public function rollbackMigrations()
    {
        $this->phinx->rollback('production');
    }


    private function getConfig()
    {
        return new Config($this->getConfigArray());
    }

    private function getConfigArray()
    {
        $environment = \Quantum\Config::getInstance()->getEnvironment();

        if ($environment) {
            $environment = new_vt( (array) $environment);
        }
        else {
            throw_exception('no environment set');
        }

        return [
            'paths' => [
                'migrations' => "$this->base",
                'seeds'      => "$this->base/seeds",
            ],
            'environments' => [
                'default_migration_table' => 'phinxlog',
                'default_database'        => 'production',
                'production'              => [
                    'adapter'   => 'mysql',
                    'host'      => $environment->get('db_host'),
                    'user'      => $environment->get('db_user'),
                    'pass'      => $environment->get('db_password'),
                    'port'      => $environment->get('db_port', 3306),
                    'name'      => $environment->get('db_name'),
                    'suffix'    => $environment->get('db_suffix', ''),
                ]
            ]
        ];
    }
}