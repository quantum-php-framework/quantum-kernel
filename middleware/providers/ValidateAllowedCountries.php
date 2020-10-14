<?php

namespace Quantum\Middleware;

use Quantum\Middleware\Foundation;
use Quantum\Request;

use Closure;

/**
 * Class ValidateAllowedCountries
 * @package Quantum\Middleware
 */
class ValidateAllowedCountries  extends Foundation\SystemMiddleware
{

    /**
     * @param Request $request
     * @param Closure $closure
     * @return mixed|void
     */
    public function handle(Request $request, Closure $closure)
    {
        $kernelConfig = \Quantum\Config::getInstance()->getKernelConfig();

        if ($kernelConfig->has('allowed_countries'))
        {
            $visitor_country_code = $request->getVisitorCountryCode();

            $countries = $kernelConfig->getAllowedCountries();

            if (!empty($countries))
            {
                foreach ($countries as $country)
                {
                    if ($country === $visitor_country_code)
                        return;
                }
            }

            $this->logException('country_not_allowed');
            $this->getOutput()->displaySystemError('country_access_denied');
        }

        if ($kernelConfig->has('blocked_countries'))
        {
            $visitor_country_code = $request->getVisitorCountryCode();

            $countries = $kernelConfig->getBlockedCountries();

            if (!empty($countries))
            {
                foreach ($countries as $country)
                {
                    if ($country === $visitor_country_code)
                    {
                        $this->logException('country_blocked');
                        $this->getOutput()->displaySystemError('country_access_denied');
                    }

                }
            }

        }
    }

}