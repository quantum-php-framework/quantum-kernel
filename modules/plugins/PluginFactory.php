<?php

namespace Quantum\Plugins;

class PluginFactory
{
    public function __construct( $folder)
    {
        $this->folder = $folder;
    }


    public function build()
    {
        $entry_file = qf($this->folder->getPluginEntryFile());

        $entry_class = $this->folder->getPluginEntryHeader('entry_class');

        if (empty($entry_class)) {
            throw_exception('Entry Class not found for: '.$entry_file->getRealPath());
        }

        $enabled_plugins = new_vt(\Quantum\Config::getInstance()->getKernelAndActiveAppPlugins());

        if ($enabled_plugins->hasEqualParam($entry_class, 1))
        {
            include $entry_file->getRealPath();

            $plugin = new $entry_class;
            $plugin->_setFolder($this->folder);
            $plugin->_setEntryClassName($entry_class);

            return $plugin;
        }

        return null;

    }



}