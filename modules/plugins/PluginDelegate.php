<?php

namespace Quantum\Plugins;

use Quantum\HMVC\Module;

abstract class PluginDelegate
{
    function activate() {}
    function deactivate() {}
    function install() {}
    function uninstall() {}
    function update() {}

}