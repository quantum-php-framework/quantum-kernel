<?php

namespace Quantum\Middleware;

use Quantum\Middleware\Request\RunHandler;

class ExecuteAppMiddlewares extends \Quantum\Middleware\Foundation\SystemMiddleware
{

    public function handle(\Quantum\Request $request, \Closure $closure)
    {
        $middlewares = get_active_app_setting('middlewares', []);

        if (empty($middlewares))
            return;

        $handler = new RunHandler();
        $handler->registerProviders($middlewares);
        $handler->runRequestProviders($request, $closure);

    }

}