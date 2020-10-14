<?php

namespace Quantum\Middleware;

use Closure;
use Quantum\Request;
use Quantum;


/**
 * Class MaintenanceMode
 * @package Quantum\Middleware
 */
class MaintenanceMode extends Foundation\SystemMiddleware
{
    /**
     * @var
     */
    protected $exclusions;

    /**
     * MaintenanceMode constructor.
     */
    public function __construct()
    {
        $this->addExclusion("/testroute");
    }

    /**
     * @param Request $request
     * @param Closure $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        $file = Quantum\InternalPathResolver::getInstance()->getMaintenanceLockFile();

        if (file_exists($file))
        {
            if ($this->shouldHandle($request))
            {
                $this->logException('maintenance_mode_enabled');
                Quantum\Output::getInstance()->displaySystemError("maintenance");
            }

        }

        return $next($request);

    }

    /**
     * @param $key
     */
    public function addExclusion($key)
    {
        $this->getExclusions()->set($key, $key);
    }

    /**
     * @return Quantum\ValueTree
     */
    public function getExclusions()
    {
        if (!isset($this->exclusions))
            $this->exclusions = new_vt();

        return $this->exclusions;
    }


    /**
     * @param $request
     * @return bool
     */
    public function shouldNotHandle($request)
    {
        $uri = $request->getUri();

        return $this->getExclusions()->has($uri);
    }


    /**
     * @param $request
     * @return bool
     */
    public function shouldHandle($request)
    {
        return !$this->shouldNotHandle($request);
    }


}