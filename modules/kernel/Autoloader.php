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


    public function initDirectories()
    {
        $ipt = InternalPathResolver::getInstance();

        $this->namespaces = array();
        $this->directories = array();
        $this->system_directories = array();

        $this->system_directories = File::newFile($ipt->system_root)->getAllSubDirectories();
        //dd($this->system_directories);

        $this->modules_directories = File::newFile($ipt->shared_app_modules_root)->getAllSubDirectories();;

        $this->addDirectory($ipt->lib_root, false);

        $this->addAppDirectories();
    }

    public function addAppDirectories()
    {
        $ipt = InternalPathResolver::getInstance();

        if (isset($ipt->app_root))
            $this->addDirectory($ipt->app_root, false);

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
        if (!qs($className)->contains('\\'))
            return Result::fail();

        $original_class = $className;

        $namespace = QString::create($className)->upToFirstOccurrenceOf("\\")->toStdString();

        if (!$this->moduleLocator->hasModuleForNamespace($namespace))
            return Result::fail();

        qm_profiler_start('ModuleClassLoad::'.$className);

        $className = QString::create($className)->fromLastOccurrenceOf("\\")->toStdString();

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

        if (isset($ipt->shared_app_modules_root) && qs($module_directory)->isNotEmpty())
        {
            $modules_path->add($ipt->shared_app_modules_root.$module_directory);
        }

        if ($modules_path->isEmpty())
        {
            return Result::fail();
        }

        $module_directories = deepscan_dirs($modules_path->getArray());

        $located_file = "";

        foreach($module_directories as $directory)
        {
            $path = $directory.$className.".php";

            if(\file_exists($path))
            {
                if (ClassReader::getClassFullNameFromFile($path) === $original_class)
                {
                    $located_file = $path;
                    break;
                }
            }
        };

        if (empty($located_file))
        {
            trigger_error("Module Class not found: ".$original_class);
            return Result::fail();
        }

        $this->loadClass($original_class, $located_file);

        qm_profiler_stop('ModuleClassLoad::'.$className);

        return Result::ok();

    }

    private function loadClass($original_class, $path)
    {
        if(\file_exists($path))
        {
            if (ClassReader::getClassFullNameFromFile($path) === $original_class)
            {
                require_once $path;
            }
        }
    }

    /**
     * Thee autoloader...,  you can add more fileNameFormats, for ex: %s.class.php
     */
    private function handle($className)
    {
        $original_class = $className;

        //qm_profiler_start('AutoLoad::'.$className);

        $r = $this->handleProbableModuleClass($className);

        if ($r->wasOk())
            return;

        $fileNameFormats = array(
            '%s.php',
        );

        if (qs($className)->contains('Quantum\\'))
            $system_class = true;
        else
            $system_class = false;

        $path = str_ireplace('_', '/', $className);


        if (qs($className)->contains('\\'))
            $className = QString::create($className)->fromLastOccurrenceOf("\\")->toStdString();

        if(@include $path.'.php'){
            //qm_profiler_stop('AutoLoad::'.$className);
            return;
        }

        if ($system_class)
            $directories = $this->system_directories;
        else
            $directories = $this->directories;

        //dd($directories);

        foreach($directories as $directory){
            foreach($fileNameFormats as $fileNameFormat){

                $path = $directory.sprintf($fileNameFormat, $className);
                if(\file_exists($path)){
                    //echo ('loading: '.$path."<br/>");
                    //echo ('className: '.$className."<br/><br/>");
                    //qm_profiler_start('Required::'.$path);

                    require_once $path;
                    //qm_profiler_stop('Required::'.$path);

                    //qm_profiler_stop('AutoLoad::'.$className);
                    return;
                }
            }
        }

        //qm_profiler_stop('AutoLoad::'.$className);


    }

    function x()
    {
        session_start();

        if (!isset($_SESSION['load_more_iteration'])) {
            $_SESSION['load_more_iteration'] = 0;
        }

        $iteration = $_SESSION['load_more_iteration'];

        $col_number = 0;

        if ($iteration  >= 20 && $iteration < 40)
        {
            $col_number = 1;
        }

        if ($iteration  >= 40 && $iteration < 60)
        {
            $col_number = 2;
        }

        if ($iteration  >= 60)
        {
            $col_number = 0;
            $iteration = 0;
            $_SESSION['load_more_iteration'] = 0;
        }


        printf(
            '<div id="column-%s" class="link-col"><div class="widget-box"><ul class="posts-list%s">',
            $col_number,
            wpd_get_key('wpd_display_postedlink_border') == 'yes' ? ' border' : ''
        );

        wpdrudge_display_posted_link(get_the_ID());

        echo '</ul></div></div>';

        $iteration++;
        $_SESSION['load_more_iteration'] = $iteration;


    }

}