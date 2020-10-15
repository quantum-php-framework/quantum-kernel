<?php

namespace Quantum\Middleware\Request;

use Closure;

/**
 * Class Provider
 * @package Quantum\Middleware\Request
 */
class RunHandler extends \Quantum\Singleton
{
    /**
     * @var \Quantum\ValueTree
     */
    private $providers_registry;

    /**
     * Provider constructor.
     * @param array $providers
     */
    public function __construct($providers = array())
    {
        $this->providers_registry = new \Quantum\ValueTree();

        if (!empty($providers))
            $this->registerProviders($providers);
    }

    /**
     * @param $providers
     */
    public function registerProviders($providers)
    {
        foreach ($providers as $provider)
        {
            $this->providers_registry->add($provider);
        }


    }

    /**
     * @param $request
     * @param Closure $next
     * @return mixed
     */
    public function runRequestProviders($request, Closure $next)
    {
        foreach ($this->providers_registry->all() as $key => $provider_name)
        {
            qm_profiler_start('Middleware::'.$provider_name);
            if (!class_exists($provider_name))
                throw new \InvalidArgumentException("Middleware Provider class not found: ".$provider_name);

            $provider = new $provider_name();

            $provider->handle($request, function ($request)
            {


            });
            qm_profiler_stop('Middleware::'.$provider_name);
        }

        return $next($request);
    }


}