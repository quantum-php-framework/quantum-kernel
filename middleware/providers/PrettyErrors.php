<?php

namespace Quantum\Middleware;

/**
 * Class PrettyErrors
 * @package Quantum\Middleware
 */
class PrettyErrors extends Foundation\SystemMiddleware
{

    /**
     * @param \Quantum\Request $request
     * @param \Closure $closure
     * @return \Closure|mixed
     */
    public function handle(\Quantum\Request $request, \Closure $closure)
    {
        return null;

        $config = \QM::config();

        if ($config->isDevelopmentEnvironment() || $request->hasParam('qm_show_pretty_errors') || $request->getIp() == get_overridable_route_setting('allowed_debug_ip'))
        {
            //halt();

            $handler = new \Whoops\Handler\PrettyPageHandler;
            //$handler->setEditor('phpstorm');
            $handler->setEditor("phpstorm");

            $whoops = new \Whoops\Run;
            $whoops->pushHandler($handler);
            /*
            $whoops->pushHandler(function($exception, $inspector, $run) {
                $this->handleProductionException($exception, $inspector, $run);
            });
            */
            $whoops->register();
        }
        elseif ($config->isProductionEnvironment())
        {
            $whoops = new \Whoops\Run;
            $whoops->allowQuit(true);
            $whoops->writeToOutput(false);
            $whoops->pushHandler(function($exception, $inspector, $run) {
                $this->handleProductionException($exception, $inspector, $run);
            });
            $whoops->register();
        }

        return $closure;
    }

    /**
     * @param $exception
     * @param $inspector
     * @param $run
     */
    public function handleProductionException($exception, $inspector, $run)
    {
        \Quantum\Logger::custom($exception->getMessage(), 'production_errors');

        $e = new \Exception;
        $error_msg = $e->getTraceAsString();

        $response = \ExternalErrorLoggerService::error($exception->getMessage(), ['request' => json_encode($_REQUEST), 'backtrace' => json_encode(debug_backtrace()), 'inspector' => $inspector->getExceptionName()]);

        if ($response->wasSuccessful())
            $this->getOutput()->set('error_code', $response->getUuid());


        \Quantum\Logger::custom($error_msg, 'all_production_errors');

        $this->getOutput()->displayAppError('500');
    }

}