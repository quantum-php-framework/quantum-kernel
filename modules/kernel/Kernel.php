<?php
namespace Quantum;

use Quantum\Middleware\MaintenanceMode;
use Quantum\Middleware\PrettyErrors;
use Quantum\Middleware\ValidateAllowedCountries;
use Quantum\Middleware\ValidateAllowedIps;
use Quantum\Middleware\ValidateAppRateLimit;
use Quantum\Middleware\ValidateAppStatus;
use Quantum\Middleware\ValidateCSRF;
use Quantum\Middleware\ValidateKernelRateLimit;
use Quantum\Middleware\ValidatePostSize;
use Quantum\Middleware\ExecuteAppMiddlewares;
use Shared\Middlewares\ExecuteRouteMiddlewares;

/** \mainpage Quantum Framework Api Docs
 *
 * \section intro_sec Introduction
 *
 * The Quantum Framework is an HMVC Framework
 *
 * Please see user guide at: http://docs.ccstores.host/books/quantum-framework-docs
 */

include(__DIR__ . "/../../helpers/functions.php");


include ("ValueTree.php");
include ("Profiler.php");
include ("Request.php");
include ("Config.php");
include ("InternalPathResolver.php");
include ("Session.php");
include ("Autoloader.php");
include ("Output.php");
include ("QM.php");



/**
 * The Quantum Kernel
 * Format: Maj.Min.Min.Rev
 */
define("QM_KERNEL_VERSION", "2.7.1.0");


/**
 * Class Kernel
 * @package Quantum
 */
class Kernel extends Singleton
{
    /**
     * @var Request
     */
    public $request;
    /**
     * @var Config
     */
    public $config;
    /**
     * @var InternalPathResolver
     */
    public $ipt;
    /**
     * @var Autoloader
     */
    public $autoloader;
    /**
     * @var Session
     */
    public $session;
    /**
     * @var Output
     */
    public $output;

    /**
     * Kernel constructor.
     */
    function __construct()
    {
        Profiler::start("Kernel::__construct");

        $this->request = Request::getInstance();

        $this->config = Config::getInstance();

        $this->ipt = InternalPathResolver::getInstance();

        $this->autoloader = Autoloader::getInstance();

        $this->session = Session::getInstance();

        $this->output = Output::getInstance();

        Profiler::stop("Kernel::__construct");

        //$this->runRequestMiddlewares();

        //Logger::dev($this->public_url);
    }

    public function runCriticalMiddlewares()
    {
        $middlewares = $this->config->getKernelSetting('critical_middlewares');

        if (empty($middlewares) || !is_array($middlewares))
            return;

        $this->runMiddlewares($middlewares);
    }

    /**
     *
     */
    public function runRequestMiddlewares()
    {
        $middlewares = array(PrettyErrors::class,
            ValidateAllowedIps::class,
            ValidateKernelRateLimit::class,
            MaintenanceMode::class,
            ValidatePostSize::class,
            ValidateCSRF::class,
            ValidateAppStatus::class,
            ValidateAppRateLimit::class,
            ValidateAllowedCountries::class);

        $custom_middlewares = $this->config->getKernelSetting('middlewares');

        if (!empty($custom_middlewares)) {
            $middlewares = array_merge($middlewares, $custom_middlewares);
        }

        $this->runMiddlewares($middlewares);
    }

    /**
     *
     */
    public function runAppMiddlewares()
    {
        $middlewares = array(ExecuteAppMiddlewares::class,
            ExecuteRouteMiddlewares::class);

        $this->runMiddlewares($middlewares);
    }

    public function runMiddlewares($middlewares)
    {
        $handler = new Middleware\Request\RunHandler($middlewares);
        $handler->runRequestProviders($this->request, function($request)
        {
            //var_dump($request);
        });
    }



    /**
     * @return string
     */
    public static function getVersion()
    {
        return QM_KERNEL_VERSION;
    }

    /**
     * @return Request
     */
    public function getRequest()
    {
        return $this->request;
    }

    /**
     * @return Config
     */
    public function getConfig()
    {
        return $this->config;
    }

    /**
     * @return InternalPathResolver
     */
    public function getIpt()
    {
        return $this->ipt;
    }

    /**
     * @return Autoloader
     */
    public function getAutoloader()
    {
        return $this->autoloader;
    }

    /**
     * @return Session
     */
    public function getSession()
    {
        return $this->session;
    }

    /**
     * @return Output
     */
    public function getOutput()
    {
        return $this->output;
    }

    /**
     * @param string $exit_code
     */
    static function shutdown($exit_code = "")
    {
        //pre(get_included_files());
        exit($exit_code);
    }
}