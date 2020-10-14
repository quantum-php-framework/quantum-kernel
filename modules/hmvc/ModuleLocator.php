<?php

namespace Quantum\HMVC;


use Quantum\InternalPathResolver;

/**
 * Class ModuleController
 * @package Quantum\HMVC
 */
class ModuleLocator
{

    public function __construct()
    {
        $ipt = InternalPathResolver::getInstance();

        $this->modules = new_vt(include qf($ipt->config_root)->getChildFile('modules.php')->getRealPath());

        if (isset($ipt->app_config_root))
        {
            $app_modules_config_file = qf($ipt->app_config_root)->getChildFile('modules.php');

            if ($app_modules_config_file->existsAsFile())
            {
                $app_modules = new_vt(include $app_modules_config_file->getRealPath());

                if (!$app_modules->isEmpty())
                {
                    $this->modules->mergeValueTree($app_modules);
                }
            }
        }
        //dd($this->modules);
    }

    public function hasModuleForNamespace($namespace)
    {
        if (!is_string($namespace))
            return false;

        return $this->modules->has($namespace);
    }

    public function getModule($namespace)
    {
        return new_vt($this->modules->get($namespace));
    }


}