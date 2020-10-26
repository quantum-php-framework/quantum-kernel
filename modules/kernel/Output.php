<?php

namespace Quantum;

/**
 * Class Output
 * @package Quantum
 */
class Output extends Singleton
{

    /**
     * @var
     */
    public $ipt;
    /**
     * @var
     */
    public $smarty;
    /**
     * @var
     */
    public $views;
    /**
     * @var
     */
    public $mainView;

    /**
     * @var bool
     */
    private $shouldRenderHeaderAndFooter;
    /**
     * @var bool
     */
    private $shouldCompressOutput;
    /**
     * @var bool
     */
    private $shouldTrimWhiteSpace;

    /**
     * Output constructor.
     */
    function __construct()
    {
        $this->shouldRenderHeaderAndFooter = true;
        $this->shouldCompressOutput = false;
        $this->shouldTrimWhiteSpace = false;
        $this->initSmarty();

    }

    /**
     * @param $smarty
     */
    public function init($smarty)
    {
        $this->smarty = $smarty;
        $this->views = array();

    }

    /**
     *
     */
    public function initSmarty()
    {
        $this->ipt = InternalPathResolver::getInstance();

        assert(!empty($this->ipt));

        define('SMARTY_DIR', $this->ipt->lib_root.'smarty/');
        define('SMARTY_SYSPLUGINS_DIR', $this->ipt->lib_root.'smarty/sysplugins/');
        define('SMARTY_PLUGINS_DIR', $this->ipt->lib_root.'smarty/plugins/');
        require_once ($this->ipt->lib_root.'smarty/bootstrap.php');

        $this->smarty = new \Smarty();
        $this->smarty ->template_dir = $this->ipt->views_root;
        $this->smarty->compile_dir =   $this->ipt->tmp_root;
        //$this->smarty->allow_php_tag = true;
        //$this->smarty->plugins_dir[] = $this->ipt->lib_root.'smarty/plugins';

        //$this->set('QM_Client', Client::getInstance());
        //$this->set('QM_Environment', Config::getInstance()->getEnvironment());

        //\Quantum::setSmarty($this->smarty);
        //var_dump($this->smarty);
        //var_dump($this);
    }


    /**
     * @param $view
     */
    public function renderView($view) {

       $this->display($this->ipt->views_root.$view);
    }

    /**
     * @param $shouldCompress
     */
    public function setCompressOutput($shouldCompress)
    {
        $this->shouldCompressOutput = $shouldCompress;
    }

    /**
     * @param $shouldTrim
     */
    public function setTrimWhiteSpace($shouldTrim)
    {
        $this->shouldTrimWhiteSpace = $shouldTrim;
    }

    /**
     * @param $controller
     * @param $task
     */
    public function setMainView($controller, $task) {

        if (empty($controller)) {

            $controller = 'index';
        }

        $this->mainView = "$controller/$task.tpl";

    }

    /**
     * @param $view
     */
    public function resetMainView($view) {
        $this->mainView = $view;
    }


    /**
     * @param $var_name
     * @param $var_content
     */
    public function set($var_name, $var_content)
    {
        $this->smarty->assign($var_name, $var_content);
    }

    /**
     * @param $var_name
     */
    public function unassign($var_name)
    {
        $this->smarty->clearAssign($var_name);
    }

    /**
     * @param $key
     * @param bool $fallback
     * @return bool
     */
    public function getAssignedParam($key, $fallback = false)
    {
        $vars = $this->smarty->getTemplateVars();

        if (array_key_exists($key, $vars))
            return $vars[$key];

        return $fallback;
    }


    /**
     * @param $layout_directory_name
     * @return bool
     */
    public function setTemplate($layout_directory_name) {

        if ($layout_directory_name) {
            $this->template = $layout_directory_name;
            return true;
        }

            return false;

    }

    /**
     * @param $controller
     * @param $task
     * @return bool
     */
    public function setView($controller, $task) {

        if (empty($controller)) {
            $controller = 'index';
        }

        if (!isset($this->views)) {
            $this->views = array();
        }

        $view = "$controller/$task.tpl";


        array_push($this->views, $view);


        return true;

    }

    /**
     *
     */
    public function clearViews()
    {
        $this->views = array();
    }


    /**
     *
     */
    public function renderTemplateNow() {



    }

    /**
     * @param $controller
     */
    public function setActiveController($controller)
    {
        $this->activeController = $controller;
    }

    /**
     * @return mixed
     */
    public function getActiveController()
    {
        return $this->activeController;
    }


    /**
     * @param $shouldRender
     */
    public function setShouldRenderFullTemplate($shouldRender)
    {
        $this->shouldRenderHeaderAndFooter = $shouldRender;
    }


    /**
     *
     */
    public function renderFullTemplate() {
        //var_dump($this);

        if (!isset($this->activeController->smarty))
            return;

        if (empty($this->activeController->template) && !empty($this->template))
        {
            $this->activeController->template = $this->template;
        }

        if (!empty($this->activeController->template))
        {
            $this->smarty->assign('current_views_dir', $this->getViewDirInCurrentTemplate()."/".$this->activeController->controller."/");
            $this->smarty->assign('cvd', $this->getViewDirInCurrentTemplate()."/".$this->activeController->controller."/");

            $header = $this->activeController->template."/layout/header.tpl";
            $footer = $this->activeController->template."/layout/footer.tpl";

            if ($this->activeController->renderFullTemplate)
            {
                if ($this->shouldRenderHeaderAndFooter)
                {
                    if (qf($this->ipt->templates_root.$header)->existsAsFile())
                        $this->display($this->ipt->templates_root.$header);
                }

            }

            if (!empty($this->mainView))
            {
                $main_view_file = qs($this->mainView)->toLowerCase()->toStdString();
                $view_in_template = $this->getViewInCurrentTemplate($main_view_file);

                if (!$view_in_template)
                {
                    $route = get_current_route();

                    if ($route->has('from_plugin'))
                    {
                        $plugin_dir = $route->get('from_plugin_dir');

                        $file = qf($plugin_dir)->getChildFile('views/'.$main_view_file);

                        if ($file->existsAsFile()) {
                            $this->display($file->getRealPath());
                        } else
                        {
                            throw_exception('no file:'.$file->getPath());
                        }
                    }
                }
                else
                {
                    $this->display($view_in_template);
                }
            }
            elseif (!empty($this->views))
            {
                $this->renderViews();
            }

            if ($this->activeController->renderFullTemplate)
            {
                if ($this->shouldRenderHeaderAndFooter)
                {
                    if (qf($this->ipt->templates_root.$footer)->existsAsFile())
                        $this->display($this->ipt->templates_root . $footer);
                }

            }

        }

        elseif (!empty($this->mainView))
        {
            $this->display($this->getViewInCurrentTemplate($this->mainView));
        }

        elseif (!empty($this->views))
        {
            foreach($this->views as $view)
            {
                //var_dump($this);
                $this->display($this->getViewInCurrentTemplate($view));
            }
        }

    }

    /**
     *
     */
    private function renderViews() {
        foreach($this->views as $view)
        {
            $view_file = qs($view)->toLowerCase()->toStdString();
            $view_in_template = $this->getViewInCurrentTemplate($view_file);

            if ($view_in_template && qf($view_in_template)->existsAsFile())
            {
                $this->display($view_in_template);
            }
            else
            {
                $route = get_current_route();

                if ($route->has('from_plugin'))
                {
                    $plugin_dir = $route->get('from_plugin_dir');

                    $file = qf($plugin_dir)->getChildFile('views/'.$view_file);

                    if ($file->existsAsFile()) {
                        $this->display($file->getRealPath());
                    }
                    else
                    {
                        throw_exception('no file:'.$file->getPath());
                    }
                }
            }
        }
    }

    /**
     *
     */
    public function render() {

       if ($this->activeController->autoRender == true) {
           $this->renderFullTemplate();
        }


    }

    /**
     * @param $view
     */
    public function overrideMainView($view) {

        $this->mainView = $view;

    }

    /**
     * @param $template
     */
    public function overrideTemplate($template)
    {
        $this->override_template = $template;
    }

    /**
     * @return string
     */
    public function getViewDirInCurrentTemplate()
    {
        $template = $this->activeController->template;

        if (isset($this->override_template))
            $template = $this->override_template;

        $dir = $this->ipt->templates_root."/".$template."/views/";

        return $dir;
    }


    /**
     * @param $path
     * @return mixed
     * @throws \Exception
     */
    public function fetch($path)
    {
        $f = qf(realpath($path));

        if (!$f->existsAsFile())
            throw new \Exception('Template file not found:'.$f->getPath());

        return $this->smarty->fetch($f->getPath());
    }

    /**
     * @param $template
     * @param $view
     * @return mixed
     * @throws \Exception
     */
    public function fetchFromTemplate($template, $view)
    {
        $dir = $this->ipt->templates_root."/".$template."/views/";

        $file = $dir.$view;

        $f = qf(realpath($file));

        if (!$f->existsAsFile())
            throw new \Exception('Template file not found:'.$f->getPath());

        return $this->smarty->fetch($f->getPath());
    }

    /**
     * @param $template
     * @param $view
     * @return mixed
     * @throws \Exception
     */
    public function fetchFromTemplateLayout($template, $view)
    {
        $dir = $this->ipt->templates_root."/".$template."/layout/";

        $file = $dir.$view;

        $f = qf(realpath($file));

        if (!$f->existsAsFile())
            throw new \Exception('Template file not found:'.$f->getPath());

        return $this->smarty->fetch($f->getPath());
    }

    /**
     * @param $page_title
     * @param null $create_link
     */
    public function setGenericTableView($page_title, $create_link = null, $create_link_title = null)
    {
        $this->set('page_title', $page_title);
        $this->set('create_link', $create_link);
        $this->set('create_link_title', $create_link_title);
        $this->mainView = '../generic_views/table.tpl';
    }

    /**
     * @param $page_title
     */
    public function setGenericFormView($page_title)
    {
        $this->set('page_title', $page_title);
        $this->mainView = '../generic_views/form.tpl';
    }


    /**
     * @param $view
     * @return bool|string
     */
    public function getViewInCurrentTemplate($view)
    {
        $view = $this->getViewDirInCurrentTemplate().$view;

        return realpath($view);
    }

    /**
     * @param $view
     * @return string
     */
    public function getMailViewInCurrentTemplate($view)
    {
        $view = $this->getViewDirInCurrentTemplate()."mails/".$view;

        return $view;
    }

    /**
     * @param $view
     * @return string
     */
    public function getErrorViewInCurrentTemplate($view)
    {
        $view = $this->getViewDirInCurrentTemplate()."errors/".$view.".tpl";

        return $view;
    }

    /**
     * @param $view
     * @return string
     */
    public function getGenericViewInCurrentTemplate($view)
    {
        $view = $this->getViewDirInCurrentTemplate()."generic_views/".$view.".tpl";

        return $view;
    }

    /**
     * @param $html
     */
    public static function outputHTML($html)
    {
        header("Content-Type: text/html");
        header("Cache-Control: no-store");
        header("Pragma: no-cache");

        echo $html;
        exit();
    }

    /**
     * @param $xml
     */
    public static function outputXML($xml)
    {
        header("Content-Type: application/xml");
        header("Cache-Control: no-store");
        header("Pragma: no-cache");

        echo $xml;
        exit();
    }

    /**
     * @param $xml
     */
    public static function outputJson($json)
    {
        if (is_array($json))
            $json = json_encode($json);

        if (is_vt($json))
            $json = $json->toJson();

        header("Content-Type: application/json");
        header("Cache-Control: no-store");
        header("Pragma: no-cache");

        echo $json;
        exit();
    }

    /**
     * @param $json
     */
    public function json($json)
    {
        self::outputJson($json);
    }

    /**
     * @param $xml
     */
    public function xml($xml)
    {
        self::outputXML($xml);
    }

    /**
     * @param $xml
     */
    public function html($html_contents)
    {
        self::outputHTML($html_contents);
    }

    /**
     * @param $valuetree
     */
    public function valueTreeAsJson($valuetree)
    {
        self::outputJson($valuetree->toJson());
    }

    /**
     * @param $properties
     */
    public function addProperties($properties)
    {
        foreach ($properties as $key => $property)
        {
            $this->set($key, $property);
        }
    }

    /**
     * @param $file
     */
    public static function pushFile($file)
    {
        if (!file_exists($file))
            ApiException::resourceNotFound();

        header('Content-Description: File Transfer');
        header('Content-Type: ' . mime_content_type($file));
        header('Content-Disposition: inline; filename="'.basename($file).'"');
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        header('Content-Length: ' . filesize($file));
        readfile($file);
        exit;

    }

    /**
     * @param $string
     * @param $filename
     * @param $mime_type
     */
    public function pushFileFromString($string, $filename, $mime_type)
    {
        header('Content-Description: File Transfer');
        header('Content-Type: ' . $mime_type);
        header('Content-Disposition: attachment; filename="'.basename($filename).'"');
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        header('Content-Length: ' . strlen($string));
        echo $string;
        exit;

    }

    /**
     * @param $type
     */
    public function displaySystemError($type, $additional_message = '')
    {
        if ($type == '404')
        {
            http_response_code(404);
            header('HTTP/1.0 404 Not Found');
        }

        $e = new \Exception;
        $error_msg = $e->getTraceAsString();

        if (Config::getInstance()->isProductionEnvironment()) {
            \ExternalErrorLoggerService::info($type, [
                'url' => Request::getPublicURL(),
                'request' => json_encode($_REQUEST),
                'post' => json_encode($_POST),
                'msg' => $error_msg,
                'data' => $additional_message]);
        }

        $e = new \Exception;
        $error_msg = $e->getTraceAsString();

        Logger::custom($error_msg, 'all_production_errors');

        $location = $this->ipt->system_error_templates_root;

        $file = $location.$type.".tpl";

        $this->display($file);

        Kernel::shutdown();
    }

    /**
     * @param $type
     * @return mixed
     */
    public function fetchSystemMailView($type)
    {
        $location = $this->ipt->system_mails_root;

        $file = $location.$type.".tpl";

        return $this->smarty->fetch($file);
    }

    /**
     * @param $type
     */
    public function displayAppError($type)
    {
        if ($type == '404')
        {
            http_response_code(404);
            header('HTTP/1.0 404 Not Found');
        }

        $config = Config::getInstance();

        $appConfig = $config->getActiveAppConfig();


        if ($appConfig->has('default_template'))
        {
            $template = $appConfig->get('default_template');

        }

        if (empty($template))
            $this->displaySystemError($type);

        $dir = qf($this->ipt->templates_root."/".$template."/errors/");

        $location = $dir->getChildFile($type.".tpl");

        if (!$location->exists())
            $this->displaySystemError($type);

        $this->set('config', $appConfig);
        $this->display($location->getRealPath());
        Kernel::shutdown();
    }

    /**
     *
     */
    public function display404()
    {
        if (Config::getInstance()->isProductionEnvironment())
            \ExternalErrorLoggerService::info('Route not found', ['url' => Request::getPublicURL(), 'request' => json_encode($_REQUEST), 'post' => json_encode($_POST)]);

        $this->displayAppError('404');
    }

    /**
     * @param $path
     */
    private function display($path)
    {
        $path = realpath($path);

        if ($this->shouldTrimWhiteSpace)
        {
            $this->smarty->loadFilter('output', 'trimwhitespace');
        }

        if ($this->shouldCompressOutput)
        {
            $this->smarty->loadFilter('output', 'compress');
        }

        $this->smarty->display($path);
    }

    /**
     * @param $string
     */
    public function setHeader($string)
    {
        header($string);
    }

    public function setResponseCode($code)
    {
        http_response_code($code);
    }

    public function removeHeader($string)
    {
        header_remove($string);
    }

    public function setHeaderParam($key, $value)
    {
        $this->setHeader($key.': '.$value);
    }

    public function response($contents = null, $code = 200)
    {
        $this->setResponseCode($code);

        if ($contents)
            echo $contents;

        Kernel::shutdown();
    }

    public static function renderCriticalError($error)
    {
        $ipt = InternalPathResolver::getInstance();

        $file = qf($ipt->system_error_templates_root.$error.'.tpl');

        if ($file->exists()) {
            echo $file->getContents();
            exit();
        }
    }




}