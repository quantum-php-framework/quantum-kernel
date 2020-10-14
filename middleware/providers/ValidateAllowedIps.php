<?php

namespace Quantum\Middleware;

use Quantum\Middleware\Foundation;
use Quantum\Request;

use Closure;

use Sunspikes\Ratelimit\Cache\Adapter\DesarrollaCacheAdapter;
use Sunspikes\Ratelimit\Throttle\Settings\ElasticWindowSettings;
use Sunspikes\Ratelimit\RateLimiter;
use Sunspikes\Ratelimit\Throttle\Factory\ThrottlerFactory;
use Sunspikes\Ratelimit\Cache\Factory\DesarrollaCacheFactory;
use Sunspikes\Ratelimit\Throttle\Hydrator\HydratorFactory;

/**
 * Class ValidateAllowedIps
 * @package Quantum\Middleware
 */
class ValidateAllowedIps  extends Foundation\SystemMiddleware
{

    /* O
     *
     * https://github.com/sunspikes/php-ratelimiter
     */
    /**
     * @param Request $request
     * @param Closure $closure
     * @return mixed|void
     */
    public function handle(Request $request, Closure $closure)
    {
        $kernelConfig = \Quantum\Config::getInstance()->getKernelConfig();

        if ($kernelConfig->has('allowed_ips'))
        {
            $ips = $kernelConfig->getAllowedIps();

            if (!empty($ips) && $request->isFromIp($ips))
                return;

            $this->logException('ip_not_allowed');
            $this->getOutput()->displaySystemError('access_denied');
        }

        if ($kernelConfig->has('blocked_ips'))
        {
            $ips = $kernelConfig->getBlockedIps();

            if (!empty($ips) && $request->isFromIp($ips))
            {
                $this->logException('ip_blocked');
                $this->getOutput()->displaySystemError('access_denied');
            }

        }
    }

}