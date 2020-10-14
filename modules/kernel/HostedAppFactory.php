<?php



namespace Quantum;

/**
 * Class HostedAppFactory
 * @package Quantum
 */
class HostedAppFactory
{

    /**
     * HostedAppFactory constructor.
     */
    private function __construct()
    {
   
    }

    /**
     * @return mixed
     */
    public static function create()
    {
        $config = Config::getInstance();

        $privateAppConfig = $config->getActiveAppConfig();

        $appName = $privateAppConfig->get('class_name', "App");

        $a = new $appName();
        $a->_environment_config = $config->getHostedAppConfig();
        $a->_private_config = $privateAppConfig;

        return $a;
    }

}