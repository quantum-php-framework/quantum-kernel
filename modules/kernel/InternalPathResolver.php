<?php

namespace Quantum;

require_once ("Singleton.php");

/**
 * Class InternalPathResolver
 * @package Quantum
 */
class InternalPathResolver extends Singleton
{
    /**
     * @var bool|string
     */
    public $root_folder;
    /**
     * @var bool|string
     */
    public $web_root;
    /**
     * @var string
     */
    public $quantum_root;
    /**
     * @var string
     */
    public $extras_root;
    /**
     * @var string
     */
    public $tests_root;
    /**
     * @var string
     */
    public $kernel_root;
    /**
     * @var string
     */
    public $system_root;
    /**
     * @var string
     */
    public $system_modules_root;
    /**
     * @var string
     */
    public $system_kernel_root;
    /**
     * @var string
     */
    public $system_helpers_root;
    /**
     * @var string
     */
    public $system_models_root;
    /**
     * @var string
     */
    public $system_abstract_root;
    /**
     * @var string
     */
    public $system_views_root;
    /**
     * @var string
     */
    public $system_mails_root;
    /**
     * @var string
     */
    public $system_error_templates_root;
    /**
     * @var string
     */
    public $system_middlewares_root;
    /**
     * @var string
     */
    public $docs_root;
    /**
     * @var string
     */
    public $apps_root;
    /**
     * @var string
     */
    public $shared_app_resources_root;
    /**
     * @var string
     */
    public $shared_app_models_root;
    /**
     * @var string
     */
    public $shared_app_services_root;
    /**
     * @var string
     */
    public $shared_app_activerecord_models_root;
    /**
     * @var string
     */
    public $shared_app_singleton_models_root;
    /**
     * @var string
     */
    public $shared_app_filters_root;
    /**
     * @var string
     */
    public $shared_app_helpers_root;
    /**
     * @var string
     */
    public $shared_app_templates_root;
    /**
     * @var string
     */
    public $shared_app_controllers_root;
    /**
     * @var string
     */
    public $shared_app_modules_root;
    /**
     * @var string
     */
    public $shared_app_plugins_root;
    /**
     * @var string
     */
    public $hosted_apps_root;
    /**
     * @var string
     */
    public $local_root;
    /**
     * @var string
     */
    public $config_root;
    /**
     * @var string
     */
    public $lib_root;
    /**
     * @var string
     */
    public $etc_root;
    /**
     * @var string
     */
    public $var_root;
    /**
     * @var string
     */
    public $tmp_root;
    /**
     * @var string
     */
    public $locks_root;
    /**
     * @var string
     */
    public $logs_root;
    /**
     * @var string
     */
    public $script_root;
    /**
     * @var string
     */
    public $controllers_root;
    /**
     * @var string
     */
    public $store_controllers_root;
    /**
     * @var string
     */
    public $models_root;
    /**
     * @var string
     */
    public $filters_root;
    /**
     * @var string
     */
    public $views_root;
    /**
     * @var string
     */
    public $helpers_root;
    /**
     * @var string
     */
    public $templates_root;
    /**
     * @var string
     */
    public $app_modules_root;
    /**
     * @var string
     */
    public $app_etc_root;
    /**
     * @var string
     */
    public $app_config_root;

    /**
     * InternalPathResolver constructor.
     */
    public function __construct()
    {
        $this->root_folder = realpath(__DIR__."/../../../../../webroot");

        $this->web_root = realpath($this->root_folder);

        $this->quantum_root = realpath($this->root_folder.'/../quantum/').'/';

        $this->extras_root = $this->quantum_root."extras/";

        $this->tests_root = realpath($this->root_folder.'/../tests/').'/';

        $this->kernel_root = $this->quantum_root.'kernel/';
        $this->system_root = $this->kernel_root.'system/';
        $this->system_plugins_root = $this->kernel_root.'plugins/';
        $this->system_modules_root = $this->system_root.'modules/';
        $this->system_kernel_root = $this->system_modules_root.'kernel/';
        $this->system_helpers_root = $this->system_root.'helpers/';
        $this->system_models_root = $this->system_root.'models/';
        $this->system_abstract_root = $this->system_root.'abstract/';
        $this->system_views_root = $this->system_root.'views/';
        $this->system_mails_root = $this->system_views_root.'mails/';
        $this->system_error_templates_root = $this->system_views_root.'errors/';
        $this->system_middlewares_root = $this->system_root.'middleware/';

        $this->docs_root = $this->extras_root."docs/";

        $this->apps_root = $this->quantum_root."apps/";
        $this->shared_app_resources_root = $this->apps_root.'shared/';
        $this->shared_app_models_root = $this->shared_app_resources_root.'models/';
        $this->shared_app_services_root = $this->shared_app_resources_root.'services/';
        $this->shared_app_activerecord_models_root = $this->shared_app_models_root.'activerecord/';
        $this->shared_app_singleton_models_root = $this->shared_app_models_root.'singleton/';
        $this->shared_app_filters_root = $this->shared_app_resources_root.'filters/';
        $this->shared_app_helpers_root = $this->shared_app_resources_root.'helpers/';
        $this->shared_app_templates_root = $this->shared_app_resources_root.'templates/';
        $this->shared_app_controllers_root = $this->shared_app_resources_root.'controllers/';

        $this->shared_app_modules_root = $this->shared_app_resources_root.'modules/';
        $this->shared_app_plugins_root = $this->shared_app_resources_root.'plugins/';

        $this->hosted_apps_root = $this->apps_root."hosted/";

        $this->local_root = $this->quantum_root.'local/';

        $this->config_root = $this->local_root.'config/';
        $this->lib_root = $this->local_root.'lib/';
        $this->etc_root = $this->local_root.'etc/';
        $this->var_root = $this->local_root.'var/';
        $this->tmp_root = $this->var_root.'tmp/';
        $this->locks_root = $this->var_root.'locks/';
        $this->logs_root = $this->etc_root.'logs/';

        $this->script_root = $this->quantum_root."script/";

        $this->app_root = '';

        //$this->updateAppRoot("admin");

        //$this->accounts_fs_root = $this->root_folder."/accounts/fs/";

    }

    /**
     * @param $dir
     * @throws \Exception
     */
    public function updateAppRoot($dir)
    {
        $this->app_root = $this->hosted_apps_root.$dir.'/';

        if (!is_dir($this->app_root))
           throw new \Exception ("App Root Folder not found: ".$this->app_root);

        $this->controllers_root = $this->app_root.'controllers/';
        $this->store_controllers_root = $this->app_root.'controllers/store/';
        $this->models_root = $this->app_root.'models/';
        $this->views_root = $this->app_root.'views/';
        $this->filters_root = $this->app_root.'filters/';
        $this->helpers_root = $this->app_root.'helpers/';
        $this->templates_root = $this->app_root.'templates/';
        $this->app_modules_root = $this->app_root.'modules/';
        $this->app_plugins_root = $this->app_root.'plugins/';

        $this->app_etc_root = $this->app_root.'etc/';

        $this->app_config_root = $this->app_etc_root.'config/';
    }

    /**
     *
     */
    public function getAppRoot()
    {
        return $this->apps_root;
    }




    /**
     * @return string
     */
    public function getMaintenanceLockFile()
    {
        $location = $this->locks_root;
        $name = "maintenance.lock";
        $file = $location . $name;

        return $file;
    }


    /**
     * @return string
     */
    public function getRouteTemplatesFile()
    {
        $location = $this->config_root;
        $name = "route_templates.php";
        $file = $location . $name;

        return $file;
    }

    /**
     * @return string
     */
    public function getAppsRoot()
    {
        return $this->apps_root;
    }

    /**
     * @return string
     */
    public function getConfigRoot()
    {
        return $this->config_root;
    }

    /**
     * @return string
     */
    public function getDocsRoot()
    {
        return $this->docs_root;
    }

    /**
     * @return string
     */
    public function getEtcRoot()
    {
        return $this->etc_root;
    }

    /**
     * @return string
     */
    public function getExtrasRoot()
    {
        return $this->extras_root;
    }

    /**
     * @return string
     */
    public function getHostedAppsRoot()
    {
        return $this->hosted_apps_root;
    }

    /**
     * @return string
     */
    public function getKernelRoot()
    {
        return $this->kernel_root;
    }

    /**
     * @return string
     */
    public function getLibRoot()
    {
        return $this->lib_root;
    }

    /**
     * @return string
     */
    public function getLocalRoot()
    {
        return $this->local_root;
    }

    /**
     * @return string
     */
    public function getLocksRoot()
    {
        return $this->locks_root;
    }

    /**
     * @return string
     */
    public function getLogsRoot()
    {
        return $this->logs_root;
    }

    /**
     * @return string
     */
    public function getQuantumRoot()
    {
        return $this->quantum_root;
    }

    /**
     * @return bool|string
     */
    public function getRootFolder()
    {
        return $this->root_folder;
    }

    /**
     * @return string
     */
    public function getScriptRoot()
    {
        return $this->script_root;
    }

    /**
     * @return string
     */
    public function getSharedAppActiverecordModelsRoot()
    {
        return $this->shared_app_activerecord_models_root;
    }

    /**
     * @return string
     */
    public function getSharedAppControllersRoot()
    {
        return $this->shared_app_controllers_root;
    }

    /**
     * @return string
     */
    public function getSharedAppFiltersRoot()
    {
        return $this->shared_app_filters_root;
    }

    /**
     * @return string
     */
    public function getSharedAppHelpersRoot()
    {
        return $this->shared_app_helpers_root;
    }

    /**
     * @return string
     */
    public function getSharedAppModelsRoot()
    {
        return $this->shared_app_models_root;
    }

    /**
     * @return string
     */
    public function getSharedAppModulesRoot()
    {
        return $this->shared_app_modules_root;
    }

    /**
     * @return string
     */
    public function getSharedAppPluginsRoot()
    {
        return $this->shared_app_plugins_root;
    }

    /**
     * @return string
     */
    public function getSharedAppResourcesRoot()
    {
        return $this->shared_app_resources_root;
    }

    /**
     * @return string
     */
    public function getSharedAppServicesRoot()
    {
        return $this->shared_app_services_root;
    }

    /**
     * @return string
     */
    public function getSharedAppSingletonModelsRoot()
    {
        return $this->shared_app_singleton_models_root;
    }

    /**
     * @return string
     */
    public function getSharedAppTemplatesRoot()
    {
        return $this->shared_app_templates_root;
    }

    /**
     * @return string
     */
    public function getSystemAbstractRoot()
    {
        return $this->system_abstract_root;
    }

    /**
     * @return string
     */
    public function getSystemErrorTemplatesRoot()
    {
        return $this->system_error_templates_root;
    }

    /**
     * @return string
     */
    public function getSystemHelpersRoot()
    {
        return $this->system_helpers_root;
    }

    /**
     * @return string
     */
    public function getSystemKernelRoot()
    {
        return $this->system_kernel_root;
    }

    /**
     * @return string
     */
    public function getSystemMailsRoot()
    {
        return $this->system_mails_root;
    }

    /**
     * @return string
     */
    public function getSystemMiddlewaresRoot()
    {
        return $this->system_middlewares_root;
    }

    /**
     * @return string
     */
    public function getSystemModelsRoot()
    {
        return $this->system_models_root;
    }

    /**
     * @return string
     */
    public function getSystemModulesRoot()
    {
        return $this->system_modules_root;
    }

    /**
     * @return string
     */
    public function getSystemRoot()
    {
        return $this->system_root;
    }

    /**
     * @return string
     */
    public function getSystemViewsRoot()
    {
        return $this->system_views_root;
    }

    /**
     * @return string
     */
    public function getTestsRoot()
    {
        return $this->tests_root;
    }

    /**
     * @return string
     */
    public function getTmpRoot()
    {
        return $this->tmp_root;
    }

    /**
     * @return string
     */
    public function getVarRoot()
    {
        return $this->var_root;
    }

    /**
     * @return string
     */
    public function getCacheRoot()
    {
        return $this->var_root.'cache';
    }

    /**
     * @return string
     */
    public function getLocalDbRoot()
    {
        return $this->var_root.'files-db';
    }

    /**
     * @return bool|string
     */
    public function getWebRoot()
    {
        return $this->web_root;
    }

}

