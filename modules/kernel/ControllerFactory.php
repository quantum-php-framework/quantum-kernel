<?php



namespace Quantum;

/**
 * Class ControllerFactory
 * @package Quantum
 */
class ControllerFactory
{

    /**
     * ControllerFactory constructor.
     */
    private function __construct()
    {
   
    }

    /**
     * @param $controllerName
     * @param null $app
     * @return mixed
     */
    public static function create($controllerName, $app = null)
    {
        $kernel  = Kernel::getInstance();
        $ipt     = InternalPathResolver::getInstance();
        $config  = Config::getInstance();
        $request = Request::getInstance();

        if (!class_exists($controllerName))
            \throw_exception($controllerName.' does not exist');

        $c = new $controllerName($app);
        $c->name = $controllerName;
        $c->smarty = $kernel->output->smarty;
        $c->parentApp = $app;

        $c->environment = $config->getEnvironment();

        $c->app_config = $config->getHostedAppConfig();

        $c->root_folder = $ipt->root_folder;

        $route = Config::getInstance()->getCurrentRoute();

        //dd($route);

        if (!$request->isCommandLine())
        {
            $c->controller = $route->get('controller');

            if (\QM::config()->isCurrentRouteWildcard() && $request->hasNonEmptyParam('task'))
            {
                $possible_task = $request->getParam('task');

                if (!qs($possible_task)->isAlphaNumericWithSpaceAndDash())
                    \QM::output()->displayAppError('500');

                $c->task = qs($possible_task)->sanitizeHtml()->toStdString();
            }
            else
            {
                $c->task = $route->get('method');
            }

            if ($request->hasNonEmptyParam('object_id'))
            {
                $possible_oid = $request->getParam('object_id');

                if (!qs($possible_oid)->isAlphaNumericWithSpaceAndDash())
                    \QM::output()->displayAppError('500');

                $c->object_id = qs($possible_oid)->sanitizeHtml()->toStdString();
            }
            else
            {
                $c->object_id = "";
            }
        }





        $c->database = $c->environment->db_name;
        $c->autoRender = true;

        $c->requestData = $request->_REQUEST;
        $c->postData    = $request->_POST;
        $c->getData     = $request->_GET;

        if (isset($request->requestUrl))
            $c->requestUrl  = $request->requestUrl;

        $c->activeController = $controllerName;

        $c->app_root = $ipt->app_root;
        $c->quantum_root = $ipt->quantum_root;
        $c->controllers_root = $ipt->controllers_root;
        $c->models_root = $ipt->models_root;
        $c->views_root = $ipt->views_root;
        $c->helpers_root = $ipt->helpers_root;
        $c->filters_root = $ipt->filters_root;
        $c->lib_root = $ipt->lib_root;
        $c->templates_root = $ipt->templates_root;
        $c->system_root = $ipt->system_root;
        $c->system_kernel_root = $ipt->system_kernel_root;
        $c->system_helpers_root = $ipt->system_helpers_root;
        $c->config_root = $ipt->config_root;
        $c->tmp_root = $ipt->tmp_root;

        $c->version = $kernel->getVersion();
        $c->renderFullTemplate = true;

        $c->kernel = $kernel;
        $c->output = $kernel->output;
        $c->request = $request;
        $c->session = $kernel->session;

        $c->registry = new ValueTree();

        if ($c->environment->instance == 'staging')
        {
            $c->is_production = true;
        }
        else
        {
            $c->is_production = false;
        }

        static::setUserIfNeeded($c);

        return $c;
    }

    /**
     * @param $c
     * @throws \ActiveRecord\ActiveRecordException
     */
    private static function setUserIfNeeded(&$c)
    {
        $user = \Auth::getUserFromSession();

        if (!empty($user))
        {
            \Auth::setAccessLevel("user");

            $c->user = $user;

            if (is_a($user, 'User') && isset($c->user->account)) {
                $c->user_account = $c->user->account;
            }


            \QM::output()->set('user', $c->user);

            \QM::register("user", $c->user);
        }
    }
    
    
    
   
    
    
    
}