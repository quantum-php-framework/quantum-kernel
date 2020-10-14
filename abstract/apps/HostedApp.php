<?php

namespace Quantum;

/**
 * Class HostedApp
 * @package Quantum
 */
abstract class HostedApp
{
    /**
     * @var
     */
    public $_activeController;
    /**
     * @var
     */
    public $_middlewareRunHandler;
    /**
     * @var
     */
    public $_environment_config;
    /**
     * @var
     */
    public $_private_config;


    /**
     * HostedApp constructor.
     */
    function __construct()
    {
        //$this->_system = Kernel::getInstance();
        //$this->_qm = $this->_system;

    }


    /**
     * @return mixed
     */
    abstract protected function init();

    /**
     * @return mixed
     */
    abstract protected function pre_controller_dispatch();

    /**
     * @return mixed
     */
    abstract protected function post_controller_dispatch();

    /**
     * @return mixed
     */
    abstract protected function pre_render();

    /**
     * @return mixed
     */
    abstract protected function post_render();

    /**
     * @return mixed
     */
    abstract protected function shutdown();


    /**
     * @param $controller
     */
    function setActiveController($controller)
    {
        $this->_activeController = $controller;
    }

    /**
     * @param $key
     * @param $value
     */
    function setActiveControllerProperty($key, $value)
    {
        if ($this->_activeController)
            $this->_activeController->registry->set($key, $value);
    }

    /**
     * @return mixed
     */
    function getEnvironmentConfig()
    {
        return $this->_environment_config;
    }

    /**
     * @return ValueTree
     */
    function getConfig()
    {
        return $this->_private_config;
    }

    /**
     * @return Output
     */
    function output()
    {
        return Output::getInstance();
    }

    /**
     * @return \Quantum\Request
     */
    function request()
    {
        return Request::getInstance();
    }


    /**
     * @param $middlewares
     */
    function registerMiddlewares($middlewares)
    {
        if (!isset($this->_middlewareRunHandler))
            $this->_middlewareRunHandler = new Middleware\Request\RunHandler();

        $this->_middlewareRunHandler->registerProviders($middlewares);

    }

    /**
     * @param $template
     */
    function setTemplate($template)
    {
        $this->output()->setTemplate($template);
    }

    /**
     * @return mixed
     */
    function getMiddlewareProvider()
    {
        return $this->_middlewareRunHandler;
    }

    /**
     *
     */
    function executeMiddlewares()
    {
        if (!isset($this->_middlewareRunHandler))
            return;

        $request = Request::getInstance();

        if ($request->isCommandLine())
            return;

        $this->_middlewareRunHandler->runRequestProviders($request, function()
        {

        });
    }

    /**
     * @param $middlewares
     */
    function runMiddlewares($middlewares)
    {
        qm_profiler_start('HostedApp::runMiddlewares');
        $this->registerMiddlewares($middlewares);
        $this->executeMiddlewares();
        qm_profiler_stop('HostedApp::runMiddlewares');
    }

}