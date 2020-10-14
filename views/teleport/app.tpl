<?php

/**
 * A Quantum Hosted App
*/

class App extends Quantum\HostedApp
{
    function __construct()
    {
        parent::__construct();
    }

    public function init()
    {

    }

    public function pre_controller_construct()
    {

    }

    public function setActiveController($controller)
    {
         parent::setActiveController($controller);
    }

    public function pre_controller_dispatch()
    {

    }

    public function post_controller_dispatch()
    {

    }

    public function pre_render()
    {

    }

    public function post_render()
    {

    }

    public function shutdown()
    {

    }
}