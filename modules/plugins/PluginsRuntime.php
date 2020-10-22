<?php

namespace Quantum\Plugins;

use Quantum\Events\Event;
use Quantum\Singleton;

class PluginsRuntime extends Singleton
{
    /**
     * @var PluginScanner
     */
    private $scanner;

    public function __construct()
    {
        $this->plugins_routes = new_vt();
        $this->plugins_registry = new_vt();

        observe_multiple_events([
            'app_init',
            'pre_controller_construct',
            'pre_controller_dispatch',
            'post_controller_dispatch',
            'pre_render',
            'post_render',
            'shutdown'
        ], [$this, 'propagate_event']);

        $this->scanPlugins();
    }

    private function scanPlugins()
    {
        qm_profiler_start('PluginsRuntime::scanPlugins');
        $this->scanner = new PluginScanner();

        $plugins = $this->scanner->getPlugins();

        foreach ($plugins as $plugin) {
            $this->initPlugin($plugin);
        }
        qm_profiler_stop('PluginsRuntime::scanPlugins');
    }

    private function initPlugin(Plugin $plugin)
    {
        qm_profiler_start('PluginsRuntime::initPlugin');

        $plugin->init();

        $routes = $plugin->getRoutes();

        $plugin_name = $plugin->getEntryClassName();
        $plugin_dir = $plugin->getFolder()->getPath();

        if (!empty($routes))
        {
            foreach ($routes as $route)
            {
                $route['from_plugin'] = $plugin_name;
                $route['from_plugin_dir'] = $plugin_dir;
                $route = new_locked_vt($route);
                $this->plugins_routes->add ($route);

            }
        };

        $meta = new_vt();
        $meta->set('folder', $plugin_dir);

        $headers = $plugin->getFolder()->getPluginEntryHeaders();
        foreach ($headers as $key => $value) {
            $meta->set($key, $value);
        }

        $meta->makeUnmutable();
        $meta->lock();

        $this->plugins_registry->set($plugin_name, $meta);

        qm_profiler_stop('PluginsRuntime::initPlugin');
    }

    public function getRoutes()
    {
        return $this->plugins_routes;
    }

    public function setActiveApp($app)
    {
        $plugins = $this->scanner->getPlugins();

        foreach ($plugins as $plugin) {
            $plugin->_setActiveApp($app);
        }
    }


    public function propagate_event(Event $event)
    {
        $plugins = $this->scanner->getPlugins();

        foreach ($plugins as $plugin) {
            $this->callPluginMethodIfAvailable($plugin, $event->getName());
        }
    }

    public function getPluginRuntimeMeta($plugin_key, $data_key)
    {
        if ($this->plugins_registry->has($plugin_key))
        {
            $meta = $this->plugins_registry->get($plugin_key);

            if ($meta->has($data_key)) {
                return $meta->get($data_key);
            }
        }

        return false;
    }

    /**
     * @param $method_name
     */
    private function callPluginMethodIfAvailable($plugin, $method_name)
    {
        if (method_exists($plugin, $method_name)) {
            call_user_func(array($plugin, $method_name));
        }
    }
}