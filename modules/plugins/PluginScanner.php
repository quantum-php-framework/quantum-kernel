<?php


namespace Quantum\Plugins;


use Quantum\InternalPathResolver;

class PluginScanner
{
    public function __construct($directories = null)
    {
        $this->plugins = new_vt();

        $ipt = InternalPathResolver::getInstance();
        if (!$directories) {
            $directories = [$ipt->system_plugins_root, $ipt->app_plugins_root, $ipt->getSharedAppPluginsRoot()];
        }

        $this->scan($directories);
    }

    public function scan($directories)
    {
        foreach ($directories as $directory)
        {
            $this->scanPluginRoot($directory);
        }
    }


    private function scanPluginRoot($directory)
    {
        $subdirs = qf($directory)->getSubdirectories();

        if (!is_array($subdirs))
            return;

        foreach ($subdirs as $subdir)
        {
            $folder = new PluginFolder($subdir);

            if ($folder->isValid())
            {
                $factory = new \Quantum\Plugins\PluginFactory($folder);
                $plugin = $factory->build();

                if ($plugin)
                    $this->plugins->add($plugin);
            }
        }
    }

    public function getPlugins()
    {
        return $this->plugins;
    }
}