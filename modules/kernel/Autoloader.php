<?php

namespace Quantum;

use Quantum\HMVC\ModuleLocator;
use Quantum\Inspections\ClassReader;

require_once ("Singleton.php");
require_once ("QString.php");
require_once ("File.php");
require_once ("ValueTree.php");
require_once ("Result.php");
require_once (__DIR__.'/../hmvc/ModuleLocator.php');


/**
 * Class Autoloader
 * @package Quantum
 */
class Autoloader extends Singleton
{
    /**
     * @var array
     */
    private $namespaces;
    /**
     * @var array
     */
    private $directories;
    /**
     * @var array
     */
    private $system_directories;
    /**
     * @var array
     */
    private $modules_directories;



    /**
     * Autoloader constructor.
     */
    function __construct()
    {
        $this->moduleLocator = new ModuleLocator();

        $this->initDirectories();

        $this->registerAutoLoader();

    }


    /**
     *
     */
    public function initDirectories()
    {
        $ipt = InternalPathResolver::getInstance();

        $this->namespaces = array();
        $this->directories = array();
        $this->system_directories = array();

        $this->system_directories = File::newFile($ipt->system_root)->getAllSubDirectories();

        $this->modules_directories = File::newFile($ipt->shared_app_modules_root)->getAllSubDirectories();

        $this->addDirectory($ipt->lib_root, false);

        $this->addDirectories(array(
            $ipt->shared_app_models_root,
            $ipt->shared_app_controllers_root,
            $ipt->shared_app_resources_root,
            $ipt->shared_app_services_root
        ));

        $this->addDirectories(array(
            $ipt->system_plugins_root,
            $ipt->getSharedAppPluginsRoot()
        ));

    }

    /**
     *
     */
    public function addAppDirectories()
    {
        $ipt = InternalPathResolver::getInstance();

        if (isset($ipt->app_root))
            $this->addDirectory($ipt->app_root, false);

        if (isset($ipt->app_plugins_root)) {
            $this->addDirectory($ipt->app_plugins_root);
        }

        $this->removeDuplicatedDirectories();
    }

    /**
     * @param $namespaces
     * @param $remove_duplicates
     */
    public function addNamespaces($namespaces, $remove_duplicates = false)
    {
        $this->namespaces = array_merge($this->namespaces, (array)$namespaces);

        if ($remove_duplicates)
            $this->removeDuplicatedNamespaces();
    }

    /**
     * @param $directories
     * @param $remove_duplicates
     */
    public function addDirectories($directories, $remove_duplicates = true)
    {
        foreach ($directories as $directory)
        {
            $this->addDirectory($directory);
        }

        if  ($remove_duplicates)
            $this->removeDuplicatedDirectories();


    }

    /**
     * @param $directory
     */
    public function addDirectory($directory, $deepscan = true)
    {
        if (!is_dir($directory))
            return;

        array_push($this->directories, $directory);

        $child_directories = recursive_read($directory);

        foreach ($child_directories as $child_directory)
        {
            $child_directory = QString::create(realpath($child_directory))->ensureRight('/')->toStdString();

            if ($deepscan)
                $this->addDirectory($child_directory);
            else
                array_push($this->directories, $child_directory);
        }
    }

    /**
     *
     */
    public function removeDuplicatedDirectories()
    {
        $this->directories = array_unique($this->directories);
    }

    /**
     *
     */
    public function removeDuplicatedNamespaces()
    {
        $this->namespaces = array_unique($this->namespaces);
    }


    /**
     * @return array
     */
    public function getDirectories()
    {
        return $this->directories;
    }


    /**
     * @param $classname
     * @return mixed
     */
    public function smartLoad($classname)
    {
        if (str_contains($classname, '\\'))
        {
            $namespace = QString::create($classname)->upToLastOccurrenceOf('\\', true)->toStdString();

            $this->addNamespaces($namespace);

            return new $classname;
        }

        return new $classname;
    }
    /**
     * Registers the Quantum Autoloader
     */
    private function registerAutoLoader()
    {
        spl_autoload_register(array('self', 'handle'));
    }

    /**
     * Handles a probably Module load class
     * @return Result
     */
    private function handleProbableModuleClass($className)
    {
        $class_name_qs = qs($className);

        if (!$class_name_qs->contains('\\')) {
            return Result::fail();
        }

        $original_class = $className;

        $namespace = $class_name_qs->upToFirstOccurrenceOf("\\")->toStdString();

        if (!$this->moduleLocator->hasModuleForNamespace($namespace)) {
            return Result::fail();
        }

        $className = $class_name_qs->fromLastOccurrenceOf("\\")->toStdString();

        $module = $this->moduleLocator->getModule($namespace);

        $module_directory = $module->get('directory');

        $ipt = InternalPathResolver::getInstance();

        $modules_path = new_vt();

        if (isset($ipt->app_modules_root) && qs($module_directory)->isNotEmpty())
        {
            $app_module_dir = qf($ipt->app_modules_root.$module_directory.'/');

            if ($app_module_dir->isDirectory())
            {
                $modules_path->add($app_module_dir->getPath());
            }
        }

        if (isset($ipt->shared_app_modules_root) && qs($module_directory)->isNotEmpty()) {
            $modules_path->add($ipt->shared_app_modules_root.$module_directory);
        }

        if ($modules_path->isEmpty()) {
            return Result::fail();
        }

        $module_directories = deepscan_dirs($modules_path->getArray());

        foreach($module_directories as $directory)
        {
            $path = $directory.$className.".php";

            if(\file_exists($path))
            {
                require_once $path;

                if (class_exists($original_class)) {
                    return Result::ok();
                }
            }

        };

        return Result::fail();
    }


    /**
     * @param $className
     */
    private function handle($className)
    {
        $className = qs($className);

        if ($className->contains('Quantum\\'))
        {
            $directories = $this->system_directories;
        }
        else
        {
            $r = $this->handleProbableModuleClass($className->toStdString());

            if ($r->wasOk()) {
                return;
            }

            $directories = $this->directories;
        }

        $path = str_ireplace('_', '/', $className->toStdString());

        if ($className->contains('\\')) {
            $classFileName = $className->fromLastOccurrenceOf("\\")->toStdString();
        }
        else {
            $classFileName = $className->toStdString();
        }

        $file_in_current_dir = $path.'.php';
        if(file_exists($file_in_current_dir) && @include $file_in_current_dir)
        {
            require_once $path;

            if (class_exists($className->toStdString())) {
                return;
            }
        }

        foreach($directories as $directory)
        {
            $path = $directory.sprintf('%s.php', $classFileName);

            if(\file_exists($path))
            {
                require_once $path;

                if (class_exists($className->toStdString())) {
                    return;
                }
            }
        }

    }

}