<?php

namespace Quantum\Middleware;

use Quantum\Middleware\Foundation;
use Quantum\Request;

use Closure;

/**
 * Class ValidateAppStatus
 * @package Quantum\Middleware
 */
class ValidateAppStatus extends Foundation\SystemMiddleware
{
    /**
     * ValidateAppStatus constructor.
     */
    public function __construct()
    {

    }

    /**
     * @param Request $request
     * @param Closure $next
     * @return mixed|void
     */
    public function handle(Request $request, Closure $next)
    {
        $app = \QM::config()->getHostedAppConfig();

        if ($app['enabled'] === false)
        {
            $this->logException('app_disabled');
            \Quantum\Output::getInstance()->displaySystemError("disabled_app");
        }

    }



}