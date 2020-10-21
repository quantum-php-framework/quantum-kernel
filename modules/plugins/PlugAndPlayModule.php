<?php

namespace Quantum\Plugins;

use Quantum\HMVC\Module;
use Quantum\PhinxMigrationRunner;
use Quantum\Request;

/**
 * Class PlugAndPlayModule
 * @package Quantum\Plugins
 */
class PlugAndPlayModule extends Module
{
    /**
     * @var PluginFolder
     */
    private $_folder;

    /**
     * @var
     */
    private $_entry_class_name;
    /**
     * @var
     */
    private $_settings;

    /**
     * @var
     */
    private $_active_app;

    /**
     * @param $folder
     */
    public function _setFolder($folder)
    {
        $this->_folder = $folder;
    }

    /**
     * @param $folder
     */
    public function _setEntryClassName($name)
    {
        $this->_entry_class_name = $name;
    }

    /**
     * @return mixed
     */
    public function getEntryClassName()
    {
        return $this->_entry_class_name;
    }

    /**
     * @return mixed
     */
    public function getFolder()
    {
        return $this->_folder;
    }

    public function _setActiveApp($app)
    {
        $this->_active_app = $app;
    }

    public function getActiveApp()
    {
        return $this->_active_app;
    }

    /**
     * @param $key
     * @param bool $fallback
     * @return bool|mixed
     */
    public function getConfigSetting($key, $fallback = false)
    {
        $settings = $this->getConfigFileSettings();

        if (is_vt($settings) && $settings->has($key)) {
            return $settings->get($key);
        }

        return $fallback;
    }

    /**
     * @param $middlewares
     */
    public function runMiddlewares($middlewares)
    {
        qm_profiler_start(get_class($this).'::runMiddlewares');

        $request = Request::getInstance();
        if ($request->isCommandLine()) {
            return;
        }

        $middlewareRunHandler = new \Quantum\Middleware\Request\RunHandler($middlewares);
        $middlewareRunHandler->runRequestProviders($request, function()
        {

        });

        qm_profiler_stop(get_class($this).'::runMiddlewares');
    }

    /**
     * @return bool|PluginDelegate
     */
    public function getDelegate()
    {
        $file = $this->includePluginFolderFile('Delegate.php');

        if (!$file)
            return false;

        $classes = get_declared_classes();
        $last_class = end($classes);
        $plugin = new $last_class;

        if ($plugin)
            return $plugin;

        return false;
    }

    /**
     * @return bool|mixed
     */
    public function getRoutes()
    {
        return $this->includePluginFolderFile('/etc/config/routes.php');
    }

    /**
     * @return bool|mixed
     */
    private function includeConfigFile()
    {
        return $this->includePluginFolderFile('/etc/config/config.php');
    }


    /**
     * @return array|bool|mixed|\Quantum\ValueTree
     */
    private function getConfigFileSettings()
    {
        if (!isset($this->_settings))
        {
            $this->_settings = $this->includeConfigFile();

            if (is_array($this->_settings) && !empty($this->_settings)) {
                $this->_settings = new_vt($this->_settings);
            }
        }

        return $this->_settings;
    }


    /**
     * @param $local_path
     * @return bool|mixed
     */
    public function includePluginFolderFile($local_path)
    {
        $file = $this->_folder->getChildFile($local_path);

        if ($file->existsAsFile()) {
            return include $file->getPath();
        }

        return false;
    }

    public function getEntryClassFromEntryFileHeaders()
    {
        return $this->_folder->getEntryClassFromEntryFileHeaders();
    }

    public function canBeLoadedByKernel()
    {
        return $this->_folder->canPluginBeLoadedByKernel();
    }

    public function canBeLoadedByActiveApp()
    {
        return $this->_folder->canPluginBeLoadedByActiveApp();
    }

    public function getMigrationsFolder()
    {
        return $this->_folder->getChildFile('migrations');
    }

    public function executeMigrations()
    {
        $migrations_folder = $this->getMigrationsFolder();

        $m = new PhinxMigrationRunner($migrations_folder->getPath());
        $m->executeMigrations();
    }

    public function rollbackMigrations()
    {
        $migrations_folder = $this->getMigrationsFolder();

        $m = new PhinxMigrationRunner($migrations_folder->getPath());
        $m->rollbackMigrations();
    }

    public function executeSeeds()
    {
        $migrations_folder = $this->getMigrationsFolder();

        $m = new PhinxMigrationRunner($migrations_folder->getPath());
        $m->executeSeeds();
    }


}