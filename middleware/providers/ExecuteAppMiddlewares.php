<?php

namespace Quantum\Middleware;

use Quantum\Middleware\Request\RunHandler;

class ExecuteAppMiddlewares extends \Quantum\Middleware\Foundation\SystemMiddleware
{

    public function handle(\Quantum\Request $request, \Closure $closure)
    {
        $app_config = \QM::config()->getActiveAppConfig();

        if ($app_config === false)
            return;

        $middlewares = $app_config->get('middlewares', []);

        if (empty($middlewares))
            return;

        $handler = new RunHandler();
        $handler->registerProviders($middlewares);
        $handler->runRequestProviders($request, $closure);

    }

}