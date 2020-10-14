<?php

namespace Quantum\Middleware;

use Quantum\Middleware\Foundation;
use Quantum\Request;

use Closure;


/**
 * Class ValidateKernelRateLimit
 * @package Quantum\Middleware
 */
class ValidateKernelRateLimit  extends Foundation\SystemMiddleware
{


    /**
     * @param Request $request
     * @param Closure $closure
     * @return mixed|void
     */
    public function handle(Request $request, Closure $closure)
    {
        $kernel_config = \QM::config()->getKernelConfig();

        if ($kernel_config->has('rate_limit') && $kernel_config->has('rate_limit_time'))
        {
            $ratelimiter = \Quantum\RateLimiterFactory::create($kernel_config->get('rate_limit'), $kernel_config->get('rate_limit_time'));

            $current_uri = "_QM_KERNEL_RATE_LIMIT_";
            $current_uri = \Quantum\QString::create($current_uri)->crc32b()->toStdString();
            $loginThrottler = $ratelimiter->get($current_uri);

            if (!$loginThrottler->access())
            {
                $this->logException('kernel_rate_limit_reached');
                \Quantum\ApiException::rateLimitReached();
            }

        }

        if ($kernel_config->has('session_rate_limit') && $kernel_config->has('session_rate_limit_time'))
        {
            $ratelimiter = \Quantum\RateLimiterFactory::create($kernel_config->get('session_rate_limit'), $kernel_config->get('session_rate_limit_time'));

            $current_uri = "_QM_KERNEL_RATE_LIMIT_".\Quantum\Session::getId();
            $current_uri = \Quantum\QString::create($current_uri)->crc32b()->toStdString();;
            $loginThrottler = $ratelimiter->get($current_uri);

            if (!$loginThrottler->access())
            {
                $this->logException('kernel_session_rate_limit_reached');
                \Quantum\ApiException::sessionRateLimitReached();
            }

        }

    }

}