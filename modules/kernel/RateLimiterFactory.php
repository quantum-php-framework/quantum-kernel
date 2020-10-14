<?php

namespace  Quantum;

use Sunspikes\Ratelimit\Cache\Adapter\DesarrollaCacheAdapter;
use Sunspikes\Ratelimit\Throttle\Settings\ElasticWindowSettings;
use Sunspikes\Ratelimit\RateLimiter;
use Sunspikes\Ratelimit\Throttle\Factory\ThrottlerFactory;
use Sunspikes\Ratelimit\Cache\Factory\DesarrollaCacheFactory;
use Sunspikes\Ratelimit\Throttle\Hydrator\HydratorFactory;

/**
 * Class RateLimiterFactory
 * @package Quantum
 */
class RateLimiterFactory
{

    /* O
  *
  * https://github.com/sunspikes/php-ratelimiter
  */
    /**
     * @param $rate_limit
     * @param $rate_limit_time
     * @return RateLimiter
     */
    static function create($rate_limit, $rate_limit_time)
    {
        $cacheAdapter = new DesarrollaCacheAdapter((new DesarrollaCacheFactory())->make());
        $settings = new ElasticWindowSettings($rate_limit, $rate_limit_time);
        $ratelimiter = new RateLimiter(new ThrottlerFactory($cacheAdapter), new HydratorFactory(), $settings);

        return $ratelimiter;
    }

}