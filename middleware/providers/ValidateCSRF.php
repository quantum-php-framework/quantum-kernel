<?php

namespace Quantum\Middleware;

use Quantum\Middleware\Foundation;
use Quantum\Request;

use Closure;

/**
 * Class ValidateCSRFException
 * @package Quantum\Middleware
 */
class ValidateCSRFException extends \Quantum\HttpException
{
    /**
     * PostTooLargeException constructor.
     *
     * @param  string|null  $message
     * @param  \Exception|null  $previous
     * @param  array  $headers
     * @param  int  $code
     * @return void
     */
    public function __construct($message = null, Exception $previous = null, array $headers = array())
    {
        parent::__construct(413, $message, $headers);
    }
}

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

        if (!$request->hasPostParam("csrf"))
        {
            $route = \QM::config()->getCurrentRoute();

            if (!empty($route))
            {
                if ($route['csrf_check_enabled'] == false)
                    return;
            }

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