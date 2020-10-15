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
 * Class ValidateAppRateLimit
 * @package Quantum\Middleware
 */
class ValidateAppRateLimit  extends Foundation\SystemMiddleware
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
        $app_config = \QM::config()->getHostedAppConfig();

        if (!is_vt($app_config)) {
            $this->getOutput()->display404();
        }

        if ($app_config->has('rate_limit') && $app_config->has('rate_limit_time'))
        {
            $ratelimiter = \Quantum\RateLimiterFactory::create($app_config->get('rate_limit'), $app_config->get('rate_limit_time'));

            $current_uri = strtoupper($app_config->get('uri'))."_APP_RATE_LIMIT";
            $current_uri = \Quantum\QString::create($current_uri)->crc32b()->toStdString();

            $loginThrottler = $ratelimiter->get($current_uri);

            if (!$loginThrottler->access())
            {
                $this->logException('app_rate_limit_reached');
                \Quantum\ApiException::rateLimitReached();
            }

        }

        if ($app_config->has('session_rate_limit') && $app_config->has('session_rate_limit_time'))
        {
            $ratelimiter = \Quantum\RateLimiterFactory::create($app_config->get('session_rate_limit'), $app_config->get('session_rate_limit_time'));

            $current_uri = strtoupper($app_config->get('uri'))."_APP_RATE_LIMIT_SESSION_ID_".\Quantum\Session::getId();
            $current_uri = \Quantum\QString::create($current_uri)->crc32b()->toStdString();

            $loginThrottler = $ratelimiter->get($current_uri);

            if (!$loginThrottler->access())
            {
                $this->logException('session_rate_limited_reached');
                \Quantum\ApiException::sessionRateLimitReached();
            }

        }



    }

}