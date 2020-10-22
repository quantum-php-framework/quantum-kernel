<?php
namespace Quantum;

use InvalidArgumentException;
use Quantum\Plugins\PluginsRuntime;
use Quantum\Psr7\ResponseFactory;
use ReflectionMethod;

require_once (__DIR__ . "/../kernel/Kernel.php");

/**
 * @property  query_id
 */
class Runtime
{
    /**
     * @var \Kernel
     */
    public $kernel;
    /**
     * @var \Config
     */
    public $config;

    /**
     * @var QueuedResponse
     */
    public $queued_response;
    /**
     * @var
     */
    public $controller;
    /**
     * @var
     */
    public $task;
    /**
     * @var
     */
    public $object_id;
    /**
     * @var
     */
    public $database;
    /**
     * @var
     */
    public $state;
    /**
     * @var
     */
    public $csrf;
    /**
     * @var
     */
    public $activeController;
    /**
     * @var
     */
    public $root_folder;
    /**
     * @var
     */
    public $quantum_root;
    /**
     * @var
     */
    public $app_root;
    /**
     * @var
     */
    public $views_root;
    /**
     * @var
     */
    public $controllers_root;
    /**
     * @var
     */
    public $models_root;
    /**
     * @var
     */
    public $filters_root;
    /**
     * @var
     */
    public $helpers_root;
    /**
     * @var
     */
    public $templates_root;
    /**
     * @var
     */
    public $system_root;
    /**
     * @var
     */
    public $system_kernel_root;
    /**
     * @var
     */
    public $system_helpers_root;
    /**
     * @var
     */
    public $config_root;
    /**
     * @var
     */
    public $lib_root;
    /**
     * @var
     */
    public $tmp_root;
    /**
     * @var
     */
    public $public_root;
    /**
     * @var
     */
    public $environment;
    /**
     * @var
     */
    public $app_config;
    /**
     * @var Smarty
     */
    public $smarty;
    /**
     * @var
     */
    public $mainView;
    /**
     * @var
     */
    public $template;
    /**
     * @var
     */
    public $views;
    /**
     * @var
     */
    public $autoRender;
    /**
     * @var
     */
    public $requestData;
    /**
     * @var
     */
    public $postData;
    /**
     * @var
     */
    public $getData;
    /**
     * @var
     */
    public $version;
    /**
     * @var
     */
    public $requestUrl;

    /**
     * @var
     */
    public $extractors_root;
    /**
     * @var
     */
    public $query_id;
    /**
     * @var \HostedApp
     */
    public $app;
    /**
     * @var \Request
     */
    public $request;
    /**
     * @var \InternalPathResolver
     */
    public $ipt;
    /**
     * @var \Session
     */
    public $session;
    /**
     * @var \Output
     */
    public $output;


    /**
     * Quantum constructor.
     */
    public function __construct()
    {
        Profiler::enableIfProfilerFileExists();
        Profiler::start("Quantum\Runtime::__construct");

        $this->initKernel();

        $this->importRequiredLibraries();

        $this->autoConfig();
        $this->initActiveRecord();

        $this->kernel->runRequestMiddlewares();

        $this->initSmarty();

        $this->initPluginsRuntime();

        $this->registerAdditionalRoutes();

        $this->kernel->runAppMiddlewares();

        $this->createApp();

        Profiler::start("Quantum\Runtime::runControllerDispatch");
        $this->runControllerDispatch();
        Profiler::stop("Quantum\Runtime::runControllerDispatch");

        $this->output();

        Profiler::stop("Quantum\Runtime::__construct");

        $this->shutdown();
    }


    /**
     *
     */
    private function initKernel()
    {
        $this->kernel = Kernel::getInstance();
        $this->kernel->runCriticalMiddlewares();

        $this->request = $this->kernel->request;
        $this->ipt = $this->kernel->ipt;
        $this->session = Session::getInstance();

        $this->setRootFolders();
    }

    private function initPluginsRuntime()
    {
        if (!is_cli()) {
            $this->plugins_runtime = PluginsRuntime::getInstance();
        }
    }


    /**
     *
     */
    private function importRequiredLibraries()
    {
        Import::library('pagination/paginator_qsa.class.php');
    }

    /**
     *
     */
    private function autoConfig()
    {
        $this->config = $this->kernel->config;

        $this->environment = $this->config->getEnvironment();
        $this->app_config = $this->config->getHostedAppConfig();

        //QM::register("environment", $this->environment);
        //QM::register("app_config", $this->app_config);
    }

    /**
     *
     */
    private function initActiveRecord()
    {
        Import::library('activerecord/ActiveRecord.php');

        $cfg = \ActiveRecord\Config::instance();
        $cfg->set_model_directory(array($this->models_root, $this->ipt->shared_app_activerecord_models_root));

        //dd($this->environment);

        $conn = array(
            $this->environment->instance => 'mysql://' . $this->environment->db_user . ':' .
                $this->environment->db_password . '@' .
                $this->environment->db_host . '/' .
                $this->environment->db_name . ''

        );

        $cfg->set_connections($conn, $this->environment->instance);
    }

    /**
     *
     */
    private function initSmarty()
    {
        $this->output = $this->kernel->output;
        $this->smarty = $this->output->smarty;

        if ($this->config->isProductionEnvironment()) {
            $this->output->setCompressOutput(true);
        }
    }


    /**
     *
     */
    private function registerAdditionalRoutes()
    {
        RoutesRegistry::addRoutes($this->config->getGlobalRoutes());
        RoutesRegistry::addRoutes($this->config->getActiveAppRoutes());

        if (!is_cli()) {
            RoutesRegistry::addRoutes($this->plugins_runtime->getRoutes());
        }
    }


    /**
     *
     */
    private function createApp()
    {
        Profiler::start("Quantum\Runtime::createApp");

        $this->app = HostedAppFactory::create();

        $this->plugins_runtime->setActiveApp($this->app);

        $this->callAppMethod("init");

        dispatch_event('app_init');

        Profiler::stop("Quantum\Runtime::createApp");
    }

    /**
     *
     */
    private function runControllerDispatch()
    {
        $this->callAppMethod("pre_controller_construct");

        $this->routeBasedDispatch();
    }

    /**
     *
     */
    private function shutdown()
    {
        $this->callAppMethod("shutdown");

        qm_profiler_html();

        Kernel::shutdown();
    }


    /**
     *
     */
    private function output()
    {
        Profiler::start("Quantum\Runtime::output");

        if (isset($this->activeController)) {
            $this->output->addProperties($this->activeController->registry->getProperties());
        }

        $this->callAppMethod("pre_render");

        if (isset($this->activeController)) {
            $this->callInternalApiControllerFunction($this->activeController, "__pre_render");
        }

        $this->processQueuedResponse();

        if (isset($this->activeController)) {
            $this->callInternalApiControllerFunction($this->activeController, "__post_render");
        }

        $this->callAppMethod("post_render");

        Profiler::stop("Quantum\Runtime::output");
    }

    /**
     *
     */
    private function processQueuedResponse()
    {
        if ($this->queued_response->isView())
        {
            $this->output->render();
        }
        else
        {
            $response = ResponseFactory::fromVariableData($this->queued_response->getResponse());
            $response->emit();
        }
    }


    /**
     *
     */
    private function setRootFolders()
    {
        $this->root_folder = $this->ipt->root_folder;
        $this->quantum_root = $this->ipt->quantum_root;
        $this->app_root = $this->ipt->app_root;
        $this->controllers_root = $this->ipt->controllers_root;
        $this->local_root = $this->ipt->local_root;
        $this->models_root = $this->ipt->models_root;
        $this->views_root = $this->ipt->views_root;
        $this->filters_root = $this->ipt->filters_root;
        $this->helpers_root = $this->ipt->helpers_root;
        $this->templates_root = $this->ipt->templates_root;

        $this->kernel_root = $this->ipt->kernel_root;
        $this->system_root = $this->ipt->system_root;
        $this->system_kernel_root = $this->ipt->system_kernel_root;
        $this->system_helpers_root = $this->ipt->system_helpers_root;

        $this->config_root = $this->ipt->config_root;
        $this->lib_root = $this->ipt->lib_root;
        $this->var_root = $this->ipt->var_root;
        $this->tmp_root = $this->ipt->tmp_root;
    }



    /**
     *
     */
    private function routeBasedDispatch()
    {
        $route = $this->config->getCurrentRoute();

        if (empty($route)) {
            ApiException::resourceNotFound();
        }

        $this->controller = $route->get('controller');

        if ($this->config->isCurrentRouteWildcard())
        {
            $segments = $this->request->segments();

            if (isset($segments[1])) {
                $possible_task = $segments[1];
            }
            else {
                $possible_task = $route->get('method');
            }

            $this->validateWildcardRequest($possible_task);

            $this->task = Security::sanitize_html_string($possible_task);
        }
        else
        {
            $this->task = $route->get('method');
        }

        $name = ucfirst($this->controller);

        $name = qs($name)->ensureRight("Controller")->toStdString();

        $this->createControllerAndCallFunction($name, $this->task);
    }

    /**
     * @param $task
     */
    private function validateWildcardRequest($task)
    {
        if (qs($task)->startsWith('__'))
            Output::getInstance()->displayAppError('404');

        if (!is_string($task))
            Output::getInstance()->displayAppError('404');

    }


    /**
     * @param $response
     * @param $controllerName
     * @param $task
     * @param $controller
     */
    private function handleControllerDispatch($response, $controllerName, $task, $controller)
    {
        $this->queued_response = new QueuedResponse();
        $this->queued_response->setResponse($response);

        if (is_null($response))
        {
            $controller->mainView = "$controllerName/$task.tpl";
            $this->output->setView($this->controller, $task);

            $this->queued_response->setIsView(true);
        }
        else
        {
            $this->queued_response->setIsView(false);
        }
    }


    /**
     * @param $controllerName
     * @param $controller
     * @param $task
     * @throws ReflectionException
     */
    private function callControllerFunction($controllerName, $controller, $task)
    {
        $reflection = new ReflectionMethod($controller, $task);

        if ($reflection->isProtected() || $reflection->isPrivate())
        {
            Output::getInstance()->displayAppError('404');
        }

        if (!$reflection->isPublic())
        {
            $task = 'index';
        }

        $response = call_user_func(array($controller, $task));

        $this->handleControllerDispatch($response, $controllerName, $task, $controller);
    }

    /**
     * @param $controllerName
     * @param $task
     * @throws ReflectionException
     */
    private function createControllerAndCallFunction($controllerName, $task)
    {
        $c = $this->createController($controllerName);

        $this->activeController = $c;

        $this->output->setActiveController($this->activeController);

        $this->callAppMethod("pre_controller_dispatch");
        $this->callInternalApiControllerFunction($c, "__pre_dispatch");

        if (method_exists($c, $task))
        {
            $this->callControllerFunction($controllerName, $c, $task);
        }
        else
        {
            if ($this->config->isCurrentRouteWildcard())
            {
                $task = $this->activeController->task;

                if (!method_exists($c, $task))
                    $task = 'index';

                $this->callControllerFunction($controllerName, $c, $task);
            }
            else
            {
                throw new InvalidArgumentException('Class method not found:'.$this->task);
            }

        }

        $this->callInternalApiControllerFunction($c, "__post_dispatch");
        $this->callAppMethod("post_controller_dispatch");

        //$this->output->setActiveController($this->activeController);
    }


    /**
     * @param $c
     * @param $method
     */
    private function callInternalApiControllerFunction($c, $method)
    {
        if (method_exists($c, $method))
        {
            try
            {
                $reflection = new ReflectionMethod($c, $method);

                if ($reflection->isProtected())
                {
                    $reflection->setAccessible(true);
                    $reflection->invoke($c, $method);
                    $reflection->setAccessible(false);
                }
            }
            catch (Exception $e)
            {

            }


        }
    }


    /**
     * @param $controllerName
     * @return mixed
     */
    private function createController($controllerName)
    {
        Profiler::start("Quantum\Runtime::createController");

        $c = ControllerFactory::create($controllerName, $this->app);

        $this->callInternalApiControllerFunction($c, "__post_construct");

        $this->app->setActiveController($c);

        Profiler::stop("Quantum\Runtime::createController");

        return $c;
    }


    /**
     * @param $method_name
     */
    private function callAppMethod($method_name)
    {
        dispatch_event($method_name);

        if (method_exists($this->app, $method_name))
            call_user_func(array($this->app, $method_name));
    }

}