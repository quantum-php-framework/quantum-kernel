<?php

namespace Quantum;

/**
 * Class Config
 * @package Quantum
 */
class Config extends Singleton
{

    /**
     * @var
     */
    public $environment;

    public $active_kernel_plugins_list;

    public $active_app_plugins_list;

    /**
     * Config constructor.
     * @throws \Exception
     */
    function __construct()
    {
        $this->domainBasedAutoEnvConfig();
        $this->appConfigMultipleHandler();
    }

    /**
     * @return bool
     */
    public function getEnvironment() {

        if (!empty($this->environment)) {
            return $this->environment;
        }

        return false;

    }

    /**
     * @param $environment
     */
    public function setEnvironment($environment)
    {
        $this->environment = $environment;
    }

    /**
     * @throws \Exception
     */
    public function domainBasedAutoEnvConfig()
    {

        $this->config_root = InternalPathResolver::getInstance()->config_root;

        $cfg_file = $this->config_root.'environment.php';

        if (!is_file($cfg_file))
            trigger_error("environment.php not found in config directory", E_USER_ERROR);

        require_once($cfg_file);

        if (!isset($QUANTUM_ENVIRONMENTS))
            trigger_error("QUANTUM_ENVIRONMENTS are not set", E_USER_ERROR);

        if (isset($_SERVER['SERVER_NAME']))
        {
            $current_domain = $_SERVER['SERVER_NAME'];

            $current_env = '';

            foreach ($QUANTUM_ENVIRONMENTS as $key => $environment)
            {
                if ($environment['domain'] == $current_domain)
                {
                    $current_env = (object)$environment;
                }

            }

            if (is_object($current_env)) {
                $this->setEnvironment($current_env);
            }
            else if (!Request::getInstance()->isCommandLine()) {
                Output::getInstance()->displaySystemError('500');
            }
        }

    }

    /**
     * This will attempt to load an app through multiple handlers
     * See Quantum docs article: Single or Multiple Apps.
     * @throws \Exception
     */
    private function appConfigMultipleHandler()
    {
        $r = $this->teleportAppConfigAttempt();

        if ($r->wasOk())
            return;

        $r = $this->kernelConfigDefaultAppConfigAttempt();

        if ($r->wasOk())
            return;

        $r = $this->domainBasedAutoAppConfigAttempt();

        if ($r->wasOk())
            return;

        $r = $this->kernelConfigFallbackAppConfigAttempt();

        if ($r->wasOk())
            return;

        $r = $this->kernelConfigCLIAppConfigAttempt();

        if ($r->wasOk())
            return;

        if (!Request::getInstance()->isCommandLine())
            throw_exception('No app config handler found');
    }

    /**
     * @param $uri
     * @return bool|mixed
     * @throws \Exception
     */
    private function findHostedAppConfig($uri)
    {
        $apps = $this->getHostedApps();

        if (empty($apps))
        {
            throw_exception("No hosted apps found");
        }

        foreach ($apps as $app)
        {
            if ($app['uri'] == $uri)
            {
                return $app;
            }
        }

        return false;
    }


    /**
     *
     */
    public function domainBasedAutoAppConfigAttempt()
    {
        if (Request::getInstance()->isCommandLine())
            return Result::fail();

        if (!isset($_SERVER["HTTP_HOST"]))
            return Result::fail();

        $urlParts = explode('.', $_SERVER["HTTP_HOST"]);

        if (empty($urlParts))
            return Result::fail();

        $subdomain_value = $urlParts[0];

        $config = $this->findHostedAppConfig($subdomain_value);

        if (!empty($config))
        {
            $this->setAppConfig($config);
            return Result::ok();
        }

        return Result::fail();
    }

    /**
     * @return Result
     */
    private function kernelConfigDefaultAppConfigAttempt()
    {
        if (Request::getInstance()->isCommandLine())
            return Result::fail();

        return $this->attemptConfigBasedOnKernelConfigValue('default_app');
    }

    /**
     * @return Result
     */
    private function kernelConfigFallbackAppConfigAttempt()
    {
        if (Request::getInstance()->isCommandLine())
            return Result::fail();

        return $this->attemptConfigBasedOnKernelConfigValue('fallback_app');
    }

    /**
     * @return Result
     */
    private function kernelConfigCLIAppConfigAttempt()
    {
        if (!Request::getInstance()->isCommandLine())
            return Result::fail();

        return $this->attemptConfigBasedOnKernelConfigValue('cli_app');
    }


    /**
     * @return Result
     * @throws \Exception
     */
    private function teleportAppConfigAttempt()
    {
        if (!isset($_ENV["QM_TELEPORT_SERVER_APP"]))
            return Result::fail();

        $env_app = $_ENV["QM_TELEPORT_SERVER_APP"];

        if (empty($env_app))
            return Result::fail();

        $config = $this->findHostedAppConfig($env_app);

        if (!empty($config))
        {
            $this->setAppConfig($config);
            return Result::ok();
        }

        return Result::fail();
    }


    /**
     * @param $value
     * @return Result
     * @throws \Exception
     */
    private function attemptConfigBasedOnKernelConfigValue($value)
    {
        $kernelConfig = $this->getKernelConfig();

        if (!empty($kernelConfig) && $kernelConfig->has($value))
        {
            $config = $this->findHostedAppConfig($kernelConfig->get($value));

            if (!empty($config))
            {
                $this->setAppConfig($config);
                return Result::ok();
            }
        }

        return Result::fail();
    }



    /**
     * @return ValueTree
     */
    public function getGlobalRoutes()
    {
        $this->config_root = InternalPathResolver::getInstance()->config_root;

        require_once($this->config_root.'routes.php');

        if (isset($QUANTUM_CONTROLLER_ROUTES) && empty($this->controller_routes))
        {
            $this->controller_routes = new_vt();

            foreach ($QUANTUM_CONTROLLER_ROUTES as $key => $app)
            {
                $QUANTUM_CONTROLLER_ROUTES[$key] = new_locked_vt($app);
            }

            $this->controller_routes->replaceProperties($QUANTUM_CONTROLLER_ROUTES);
        }

        return $this->controller_routes;
    }


    /**
     * @return ValueTree
     */
    public function getActiveAppRoutes()
    {
        return $this->getApprovedAppRoutes();
    }


    /**
     * @param $uri
     * @return bool|mixed
     */
    public function getRouteForUri($uri)
    {
        //$routes = $this->getApprovedAppRoutes();


        $routes = RoutesRegistry::getInstance()->getRoutes();

        if (empty($routes))
            return false;

        foreach ($routes as $route)
        {
            $route_uri = $route->get('uri');

            if (str_contains($route_uri, "*"))
            {
                $route_uri = str_replace("*", "", $route_uri);

                if (starts_with($uri, $route_uri))
                    return $route;
            }

            if ($uri == $route_uri)
                return $route;
        }

        $possible_route = dispatch_event('route_not_found');

        if ($possible_route)
            return $possible_route;

        return false;
    }

    /**
     * @return mixed
     */
    public function getHostedApps()
    {
        if (empty($this->hosted_apps))
        {
            $this->config_root = InternalPathResolver::getInstance()->config_root;

            require_once($this->config_root.'apps.php');

            if (isset($QUANTUM_HOSTED_APPS))
            {
                foreach ($QUANTUM_HOSTED_APPS as $key => $app)
                {
                    $QUANTUM_HOSTED_APPS[$key] = new_locked_vt($app);
                }

                $this->hosted_apps = $QUANTUM_HOSTED_APPS;

            }
        }

        return $this->hosted_apps;
    }

    /**
     * @return ValueTree
     */
    public function getApprovedAppRoutes()
    {
        if (empty($this->approved_app_routes))
        {
            $this->config_root = InternalPathResolver::getInstance()->app_config_root;

            require_once(qf($this->config_root . 'routes.php')->getRealPath());

            if (isset($QUANTUM_APP_ROUTES))
            {
                $this->approved_app_routes = new_vt();

                foreach ($QUANTUM_APP_ROUTES as $key => $app)
                {
                    $QUANTUM_APP_ROUTES[$key] = new_locked_vt($app);
                }

                $this->approved_app_routes->replaceProperties($QUANTUM_APP_ROUTES);
            }

        }

        return $this->approved_app_routes;
    }


    /**
     * @param $uri
     * @return bool
     */
    public function hasHostedApp($uri)
    {
        $apps = $this->getHostedApps();

        foreach ($apps as $app)
        {
            if ($app['uri'] == $uri)
                return true;
        }

        return false;
    }

    /**
     * @param $uri
     * @return mixed|null
     */
    public function getHostedApp($uri)
    {
        $apps = $this->getHostedApps();

        foreach ($apps as $app)
        {
            if ($app['uri'] == $uri)
                return $app;
        }

        return null;
    }

    /**
     * @param ValueTree $app
     */
    public function setAppConfig(ValueTree $app)
    {
        $this->hosted_app_config = $app;
        InternalPathResolver::getInstance()->updateAppRoot($this->hosted_app_config->get('dir'));
        Autoloader::getInstance()->addAppDirectories();
        //echo "Operating on:".$this->hosted_app_config->get('dir').PHP_EOL;
    }

    /**
     * @return Database
     */
    public function getDatabase()
    {
        if (!isset($this->database))
            $this->database = new Database($this->environment->db_name, $this->environment->db_host, $this->environment->db_user, $this->environment->db_password);

        return $this->database;
    }

    /**
     * @return mixed
     */
    public function getEnvironmentInstance()
    {
        return $this->environment->instance;
    }

    /**
     * @return mixed
     */
    public function getEnvironmentDomain()
    {
        return $this->environment->domain;
    }

    /**
     * @return mixed
     */
    public function getPath()
    {
        return $this->environment->path;
    }

    /**
     * @return mixed
     */
    public function getSystemSalt()
    {
        return $this->environment->system_salt;
    }

    /**
     * @return bool
     */
    public function getHostedAppConfig()
    {
        if (!empty($this->hosted_app_config))
            return $this->hosted_app_config;

        return false;
    }

    /**
     * @param $key
     * @param bool $fallback
     * @return mixed
     */
    public function getHostedAppProperty($key, $fallback = false)
    {
        return $this->hosted_app_config->get($key, $fallback);
    }

    /**
     * @return mixed
     */
    public function getHostedAppUri()
    {
        return $this->getHostedAppProperty('uri');
    }

    /**
     * @return mixed
     */
    public function getHostedAppDir()
    {
        return $this->getHostedAppProperty('dir');
    }

    /**
     * @return bool|mixed
     */
    public function getActiveAppName()
    {
        return $this->getActiveAppConfig()->get('name');
    }

    /**
     * @return bool|mixed
     */
    public function getActiveAppDeveloper()
    {
        return $this->getActiveAppConfig()->get('developer');
    }

    /**
     * @return bool|mixed
     */
    public function getActiveAppSharedSecret()
    {
        return $this->getActiveAppConfig()->get('shared_secret');
    }

    /**
     * @return ValueTree
     */
    public function getActiveAppConfig()
    {
        if (empty($this->private_app_config))
        {
            $uri = InternalPathResolver::getInstance()->app_config_root;

            $file = $uri."config.php";

            if (!file_exists($file))
                throw new \RuntimeException("File not found: ". $file);

            require_once($file);

            if (isset($QUANTUM_APP_CONFIG))
            {
                $this->private_app_config = new_vt($QUANTUM_APP_CONFIG);
            }
        }

        return $this->private_app_config;
    }

    /**
     * @return ValueTree
     */
    public function getSystemEncryptionKeys()
    {
        if (empty($this->system_encryption_keys))
        {
            $configDir = new File(InternalPathResolver::getInstance()->config_root);

            $keysFile = $configDir->getChildFile("keys.json");

            if (!$keysFile->exists())
                throw new \RuntimeException("File not found: ". $keysFile->getPath());

            $keys = $keysFile->loadAsJson();
            if (!empty($keys))
                $this->system_encryption_keys = new_vt($keys);

        }

        return $this->system_encryption_keys;
    }

    /**
     * @return bool|mixed
     */
    public function getMasterEncryptionKey()
    {
        $keys = $this->getSystemEncryptionKeys();

        if (!empty($keys))
            return $keys->get('master_encryption_key');

        return false;
    }

    /**
     * @return bool|mixed
     */
    public function getPublicRsaKey()
    {
        $keys = $this->getSystemEncryptionKeys();

        if (!empty($keys))
            return $keys->get('public_encrypted_rsa_key');

        return false;
    }

    /**
     * @return bool|mixed
     */
    public function getPrivateRsaKey()
    {
        $keys = $this->getSystemEncryptionKeys();

        if (!empty($keys))
            return $keys->get('private_encrypted_rsa_key');

        return false;
    }


    /**
     * @return ValueTree
     */
    public function getKernelConfig()
    {
        if (empty($this->kernel_config))
        {
            $uri = InternalPathResolver::getInstance()->config_root;

            $file = $uri."config.php";

            if (!file_exists($file))
                throw new \RuntimeException("File not found: ". $file);

            require_once($file);

            if (isset($QUANTUM_KERNEL_CONFIG))
            {
                $this->kernel_config = new_vt($QUANTUM_KERNEL_CONFIG);
            }
        }

        return $this->kernel_config;
    }

    /**
     * @return bool|mixed
     */
    public function getCurrentRoute()
    {
        if (empty($this->current_route))
        {
            $uri = Request::getInstance()->getUri();

            $uri = $this->checkForIdInUriAndTokenize($uri);

            $route = $this->getRouteForUri($uri);

            if (empty($route))
                return false;

            if ($route->has('templates'))
                $route = $this->mergeWithRouteTemplates($route);

            $this->current_route = $route;
        }

        return $this->current_route;
    }

    private function mergeWithRouteTemplates(ValueTree $route)
    {
        $new_route = new_vt($route->toStdArray());

        $templates = new_vt(include InternalPathResolver::getInstance()->getRouteTemplatesFile());

        $templates_to_apply = explode('|', $route->get('templates'));

        foreach ($templates_to_apply as $template_to_apply)
        {
            if ($templates->has($template_to_apply))
            {
                $template = $templates->get($template_to_apply);

                $new_route->setProperties($template);
            }
        }

        $new_route->remove('templates');
        $new_route->setUnmutable(true);
        $new_route->setLocked(true);

        return $new_route;
    }

    /**
     * @param $uri
     * @return QString
     */
    private function checkForIdInUriAndTokenize($uri)
    {
        $request = Request::getInstance();

        $id = $request->findId();

        $uri = qs($uri)->replace($id, '{id}');

        return $uri->toStdString();
    }

    /**
     * @return bool
     */
    public function isCurrentRouteWildcard()
    {
        $uri = $this->getCurrentRouteUri();

        if (empty($uri))
            return false;

        return qs($uri)->contains('*');
    }

    /**
     * @return bool
     */
    public function currentRouteRequiresId()
    {
        $uri = $this->getCurrentRouteUri();

        if (empty($uri))
            return false;

        return qs($uri)->contains('{id}');
    }

    /**
     * @return bool|mixed
     */
    public function getCurrentRouteUri()
    {
        if (empty($this->current_route_uri))
        {
            $route = $this->getCurrentRoute();

            if (empty($route))
                return false;

            $this->current_route_uri = $route['uri'];
        }

        return $this->current_route_uri;
    }

    /**
     * @return bool
     */
    public function getCurrentRouteMinAccessLevel()
    {
        $route = $this->getCurrentRoute();

        if ($route === false)
            return false;

        return $route->get('min_access_level', false);
    }

    /**
     * @return bool
     */
    public function getCurrentRouteMaxAccessLevel()
    {
        $route = $this->getCurrentRoute();

        if ($route === false)
            return false;

        return $route->get('max_access_level', false);
    }

    /**
     * @return array|bool
     */
    public function getCurrentRouteStrictAccessLevels()
    {
        $route = $this->getCurrentRoute();

        if ($route === false)
            return false;

        if ($route->has('strict_access_levels'))
        {
            $levels = $route->get('strict_access_levels');
            return qs($levels)->explode(',');
        }

        return false;
    }

    /**
     * @return bool
     */
    public function isCurrentRoutePublic()
    {
        $level = qs($this->getCurrentRouteMinAccessLevel());

        if ($level->isNotEmpty() && $level->equals('public'))
            return true;

        return false;
    }

    /**
     * @return bool
     */
    public function isProductionEnvironment()
    {
        return $this->environment->instance === 'production';
    }

    /**
     * @return bool
     */
    public function isDevelopmentEnvironment()
    {
        return $this->environment->instance === 'development';
    }

    public static function getKernelSetting($key, $fallback = false)
    {
        $config = self::getInstance()->getKernelConfig();

        if (is_vt($config)) {
            return $config->get($key, $fallback);
        }

        return $fallback;
    }


    public static function getHostedAppSetting($key, $fallback = false)
    {
        $config = self::getInstance()->getHostedAppConfig();

        if (is_vt($config)) {
            return $config->get($key, $fallback);
        }

        return $fallback;
    }

    public static function getActiveAppSetting($key, $fallback = false)
    {
        $config = self::getInstance()->getActiveAppConfig();

        if (is_vt($config)) {
            return $config->get($key, $fallback);
        }

        return $fallback;
    }


    public static function getCurrentRouteSetting($key, $fallback = false)
    {
        $config = self::getInstance()->getCurrentRoute();

        if (is_vt($config)) {
            return $config->get($key, $fallback);
        }

        return $fallback;
    }

    public static function getCurrentEnvironmentSetting($key, $fallback = false)
    {
        $env = self::getInstance()->getEnvironment();

        if (is_object($env))
        {
            if (isset($env->$key))
                return $env->$key;
        }

        return $fallback;
    }


    public function getEnabledKernelPlugins()
    {
        if (!isset($this->active_kernel_plugins_list))
        {
            $this->active_kernel_plugins_list = [];

            $file = $this->getEnabledKernelPluginsFile();

            if ($file) {
                $this->active_kernel_plugins_list = $file->getList();
            }
        }

        return $this->active_kernel_plugins_list;
    }


    public function getEnabledActiveAppPlugins()
    {
        if (!isset($this->active_app_plugins_list))
        {
            $this->active_app_plugins_list = [];

            $file = $this->getEnabledActiveAppPluginsFile();

            if ($file) {
                $this->active_app_plugins_list = $file->getList();
            }
        }

        return $this->active_app_plugins_list;
    }

    public function getEnabledKernelPluginsFile()
    {
        $ipt = InternalPathResolver::getInstance();
        $settings_file = qf($ipt->config_root.'plugins.php');

        if ($settings_file->existsAsFile()) {
            return new \Quantum\Plugins\EnabledPluginsListFile($settings_file->getRealPath());
        }

        return false;
    }

    public function getEnabledActiveAppPluginsFile()
    {
        $ipt = InternalPathResolver::getInstance();
        $settings_file = qf($ipt->app_config_root.'plugins.php');

        if ($settings_file->existsAsFile()) {
            return new \Quantum\Plugins\EnabledPluginsListFile($settings_file->getRealPath());
        }

        return false;
    }


    public function getKernelAndActiveAppPlugins()
    {
        if (!isset($this->all_active_plugins_list))
        {
            $kernel_plugins = $this->getEnabledKernelPlugins();
            $app_plugins = $this->getEnabledActiveAppPlugins();

            $this->all_active_plugins_list = array_merge($kernel_plugins, $app_plugins);
        }

        return $this->all_active_plugins_list;
    }


    /**
     * @throws \Exception
     */
    public function setEnvironmentByInstance($instance)
    {
        $this->config_root = InternalPathResolver::getInstance()->config_root;

        $cfg_file = $this->config_root.'environment.php';

        if (!is_file($cfg_file))
            trigger_error("environment.php not found in config directory", E_USER_ERROR);


        include $cfg_file;


        if (!isset($QUANTUM_ENVIRONMENTS))
            trigger_error("QUANTUM_ENVIRONMENTS are not set", E_USER_ERROR);

        foreach ($QUANTUM_ENVIRONMENTS as $key => $environment)
        {
            if ($environment['instance'] == $instance)
            {
                $current_env = (object)$environment;
                $this->setEnvironment($current_env);
                return;
            }

        }
    }




}