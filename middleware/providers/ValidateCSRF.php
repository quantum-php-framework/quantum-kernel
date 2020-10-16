<?php

namespace Quantum\Middleware;

use Quantum\Middleware\Foundation;
use Quantum\Request;

use Closure;

/**
 * Class ValidateCSRF
 * @package Quantum\Middleware
 */
class ValidateCSRF extends Foundation\SystemMiddleware
{
    /**
     * ValidateCSRF constructor.
     */
    function __construct()
    {

    }

    /**
     * @param Request $request
     * @param Closure $next
     * @return mixed|void
     */
    public function handle(Request $request, Closure $next)
    {
        $this->validate($request);

        $this->setCSRF();
    }


    /**
     * @param $request
     * @throws \Defuse\Crypto\Exception\BadFormatException
     * @throws \Defuse\Crypto\Exception\EnvironmentIsBrokenException
     */
    private function validate($request)
    {
        if (!$request->isPost())
            return;

        if (!current_route_has('csrf_check_enabled'))
            return;

        if (!$request->hasPostParam("csrf"))
        {
            \ExternalErrorLoggerService::error("missing_csrf", "Missing CSRF Token from POST Request, URI :".$request->getUri());

            \Quantum\ApiException::custom("missing_csrf", "400 Bad Request", "Missing CSRF Token from POST Request, URI :".$request->getUri());
        }

        $session_csrf = \Quantum\Session::get("csrf");

        if (!is_string($session_csrf) || empty($session_csrf))
            return;

        $posted_csrf  = \Quantum\Crypto::decryptWithLocalKey($request->getPostParam("csrf"));

        if (!is_string($posted_csrf))
            \Quantum\ApiException::invalidCSRF();

        if (!hash_equals($session_csrf, $posted_csrf))
        {
            \Quantum\ApiException::invalidCSRF();
        }

    }

    /**
     * @throws \Defuse\Crypto\Exception\BadFormatException
     * @throws \Defuse\Crypto\Exception\EnvironmentIsBrokenException
     */
    private function setCSRF()
    {
        qm_profiler_start("setCSRF");
        if (\QM::session()->isMissingParam("csrf"))
        {
            $new_csrf = \Quantum\CSRF::create();

            \QM::session()->set("csrf", $new_csrf);
        }

        $csrf = \Quantum\Crypto::encryptWithLocalKey(\QM::session()->get('csrf'));

        $html = "<input type='hidden' name='csrf' value='".$csrf."'>";

        $this->getOutput()->set("csrf", $html);
        qm_profiler_stop("setCSRF");
    }
}