<?php
/**
 * @property  query_id
 */

namespace Quantum;

/**
 * Class Controller
 * @package Quantum
 */
class Controller
{

    /**
     * @var Config
     */
    public $config;
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
     * @var \Smarty
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
     * @var array
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
     * @var ControllerHooksManager
     */
    public $hooksManager;
    /**
     * @var HostedApp
     */
    public $parentApp;
    /**
     * @var Request
     */
    public $request;
    /**
     * @var Output
     */
    public $output;
    /**
     * @var \User
     */
    public $user;


    /**
     * Controller constructor.
     * @param $parentApp
     */
    public function __construct($parentApp)
    {
        $this->parentApp = $parentApp;
    }


    /**
     * Protected Stuff inherited by Controllers
     */


    protected function requireUserAccess()
    {
        \Auth::setAccessLevel("user");

        $this->user = \Auth::getUser();
        $this->user_account = $this->user->account;

        $this->set('user', $this->user);

        \QM::register("user", $this->user);
    }

    /**
     * @param $template_name
     */
    protected function setTemplate($template_name)
    {
        $this->template = $template_name;
    }

    /**
     * @param $var_name
     * @param $var_contents
     */
    protected function set($var_name, $var_contents)
    {
        $this->smarty->assign($var_name, $var_contents);
    }

    /**
     * @param $var_name
     */
    protected function removeVar($var_name)
    {
        $this->smarty->clearAssign($var_name);
    }

    /**
     * @param $var_name
     * @param $var_contents
     */
    protected function setIfNotEmpty($var_name, $var_contents)
    {
        if (!empty($var_contents))
            $this->set($var_name, $var_contents);
    }

    /**
     * @param $view
     */
    protected function renderView($view)
    {
        $this->smarty->display($this->views_root . $view);
    }

    /**
     * @param $viewName
     * @param bool $shouldDisplayFullTemplate
     */
    protected function overrideMainView($viewName, $shouldDisplayFullTemplate = true)
    {
        $this->renderFullTemplate = $shouldDisplayFullTemplate;
        $this->output->overrideMainView($viewName . '.tpl');
    }

    /**
     * @param $pages
     */
    protected function setPages($pages)
    {
        $this->set('pages', $pages->getPages());

        $this->set('items_count', $pages->getItemsPerPage());

        $this->set('total_records', $pages->total_items_count);
    }

    /**
     * @param $shouldAutoRender
     */
    protected function setAutoRender($shouldAutoRender)
    {
        $this->autoRender = $shouldAutoRender;
    }

    /**
     * @param $shouldRender
     */
    protected function setRenderFullTemplate($shouldRender)
    {
        $this->renderFullTemplate = $shouldRender;
        Output::getInstance()->setShouldRenderFullTemplate($shouldRender);
    }

    /**
     * @param $page_title
     * @param null $create_link
     */
    protected function setGenericTableView($page_title, $create_link = null, $create_link_title = null)
    {
        $this->output->setGenericTableView($page_title, $create_link, $create_link_title);
    }

    /**
     * @param $page_title
     */
    protected function setGenericFormView($page_title)
    {
        $this->output->setGenericFormView($page_title);
    }

    /**
     * @param $key
     * @return mixed
     */
    protected function getRegistryProperty($key)
    {
        if ($this->registry)
            return $this->registry->get($key);
    }

    /**
     * @param $key
     * @param $value
     */
    protected function setRegistryProperty($key, $value)
    {
        $this->registry->set($key, $value);
    }

    /**
     * @param $key
     * @param QString $fallback
     * @return mixed
     */
    protected function getRequestParam($key, $fallback = '')
    {
        return $this->request->getParam($key, $fallback);
    }

    /**
     * @return ControllerHooksManager
     */
    protected function hooks()
    {
        if (empty($this->hooksManager))
            $this->hooksManager = new ControllerHooksManager($this);

        return $this->hooksManager;
    }

    /**
     * @return HostedApp
     */
    protected function getParentApp()
    {
        return $this->parentApp;
    }


    /**
     *
     */
    protected function callHooks()
    {
        $this->hooks()->callHooks();
    }

    /**
     * @param $classMethod
     * @param array $required_params
     */
    protected function setPOSTHook($classMethod, $required_params = array())
    {
        $this->hooks()->post($classMethod, $required_params);
    }

    /**
     * @return QString
     *
     */
    protected function getCSRF()
    {
        return \Quantum\Crypto::encryptWithLocalKey(\QM::session()->get('csrf'));
    }

    public function responseFactory()
    {
        return new \Quantum\Psr7\ResponseFactory();
    }

    public function getQubitQueue()
    {
        return $this->getParentApp()->getQubitQueue();
    }

    public function qubitCleanCache($key = null)
    {
        $opts = array();

        if ($key)
        {
            $opts['task'] = 'flush_key';
            $opts['key'] = $key;
        }
        else
        {
            $opts['task'] = 'flush_all';
        }

        return $this->qubitAddTask('CacheCleanupWorker', $opts);
    }

    public function qubitAddTask($worker, $opts)
    {
        return $this->getQubitQueue()->addTask($worker, $opts);
    }






}