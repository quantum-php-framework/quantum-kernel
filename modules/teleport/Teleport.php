<?php


namespace Quantum;


define("QM_TELEPORT_VERSION", "1.2.0.0");

/**
 * Class Teleport
 */
class Teleport {

    /**
     *
     */
    const VERSION = QM_TELEPORT_VERSION;

    /**
     * @var
     */
    protected $params;
    /**
     * @var
     */
    protected $matterType;
    /**
     * @var
     */
    protected $argv;
    /**
     * @var
     */
    protected $quantum_root;
    /**
     * @var
     */
    protected $controllers_root;
    /**
     * @var
     */
    protected $models_root;
    /**
     * @var
     */
    protected $views_root;
    /**
     * @var
     */
    protected $scripts_root;
    /**
     * @var
     */
    protected $teleport_root;
    /**
     * @var
     */
    protected $filters_root;
    /**
     * @var
     */
    protected $app_root;
    /**
     * @var
     */
    protected $smarty_root;
    /**
     * @var
     */
    protected $libs_root;
    /**
     * @var
     */
    protected $tmp_root;
    /**
     * @var
     */
    protected $matter_root;
    /**
     * @var
     */
    protected $local_root;

    /**
     * Teleport constructor.
     */
    function __construct()
    {


    }

    function init()
    {
        $this->autoLoader = \Quantum\Autoloader::getInstance();
        $this->ipt = \Quantum\InternalPathResolver::getInstance();

        $this->starGate();
        $this->dispatcher();
    }

    /**
     *
     */
    private function starGate() {

        $this->output('Welcome to Quantum Teleport: '. Teleport::VERSION);
        $this->setParams();
        $this->setFolders();
        $this->matterType = $this->argv[1];
    }

    /**
     *
     */
    private function dispatcher() {

        $this->matterType = (string) $this->argv[1];

        if (empty($this->matterType)) {
            $this->error('Error: I need to know what kind of matter to teleport');
        }

        switch ($this->matterType) {

            case "controller":
                $this->teleportController();
                break;

            case "model":
                $this->teleportModel();
                break;

            case "view":
                $this->teleportView();
                break;

            case "filter":
                $this->teleportFilter();
                break;

            case "template":
                $this->teleportTemplate();
                break;

            case "help":
                $this->help();
                break;

            case "app":
                $this->teleportApp();
                break;

            case "test":
                $this->teleportTest();
                break;

            case "start-maintenance":
                $this->setMaintenace (true);
                break;
            case "stop-maintenance":
                $this->setMaintenace(false);
                break;

            case "enable-profiler":
                $this->setProfiler (true);
                break;
            case "disable-profiler":
                $this->setProfiler(false);
                break;

            case "migration":
                $this->teleportMigration();
                break;

            case "migrate":
                $this->executeMigration();
                break;

            case "rollback-migration":
                $this->rollbackMigration();
                break;

            case "update-kernel":
                $this->updateKernel();
                break;

            case "check-kernel-update":
                $this->kernelUpdateCheck();
                break;

            case "-c":
                $this->teleportController();
                break;

            case "-m":
                $this->teleportModel();
                break;

            case "-v":
                $this->teleportView();
                break;

            case "-f":
                $this->teleportFilter();
                break;

            case "-t":
                $this->teleportTemplate();
                break;

            case "master-keys":

                $this->teleportMasterKeys();

                break;

            case "geoipdbupdate":

                $this->geoIpDbUpdate();

                break;

            case "doxygen-api-docs":

                $this->genApiDocs();
                break;


            case "doxygen-apps-docs":

                $this->genAppsDocs();
                break;

            case "doxygen-global-docs":

                $this->genGlobalDocs();
                break;

            case "phpdoc-api-docs":
                $this->genPhpdocApiDocs();
                break;

            case "phpdoc-apps-docs":
                $this->genPhpdocAppsDocs();
                break;

            case "phpdoc-global-docs":
                $this->genPhpdocGlobalDocs();
                break;

            case "all-docs":
                $this->genAllDocs();
                break;

            case "-a":
                $this->teleportApp();
                break;

            case "-h":
                $this->help();
                break;


            case "server":
                $this->server();
                break;

            case "start-server":
                $this->startServer();
                break;

            case "stop-server":
                $this->stopServer();
                break;


            case "shared-plugin-migration":
                $this->genSharedPluginMigration();
                break;

            case "app-migration":
                $this->genAppMigration();
                break;

            case "app-plugin-migration":
                $this->genAppPluginMigration();
                break;


            case "shared-plugin-seed":
                $this->genSharedPluginSeed();
                break;

            case "app-seed":
                $this->genAppSeed();
                break;

            case "app-plugin-seed":
                $this->genAppPluginSeed();
                break;


            case "execute-app-migrations":
                $this->executeAppMigrations();
                break;

            case "execute-app-plugin-migrations":
                $this->executeAppPluginMigration();
                break;

            case "execute-shared-plugin-migrations":
                $this->executeSharedPluginMigration();
                break;


            case "rollback-app-migrations":
                $this->rollbackAppMigrations();
                break;

            case "rollback-app-plugin-migrations":
                $this->rollbackAppPluginMigration();
                break;

            case "rollback-shared-plugin-migrations":
                $this->rollbackSharedPluginMigration();
                break;


            default :
                $this->error("I can't teleport that scotty !");
        }


    }







    /**
     *
     */
    private function setFolders()
    {
        $this->teleport_root = $this->ipt->script_root;
        $this->quantum_root  = $this->ipt->quantum_root;

        $this->local_root = $this->quantum_root.'local/';

        $this->config_root = $this->local_root.'config/';
        $this->libs_root = $this->local_root.'lib/';

        $this->smarty_root = $this->libs_root.'smarty/';

        $this->setAppFolders();

        $this->tmp_root = $this->ipt->tmp_root;
        $this->matter_root = $this->ipt->system_views_root.'teleport/';
    }

    public function setAppFolders()
    {
        $ipt = \Quantum\InternalPathResolver::getInstance();
        if (isset($ipt->app_root))
        {
            $this->app_root = $this->ipt->app_root;
            $this->controllers_root = $this->app_root.'controllers/';
            $this->models_root = $this->app_root.'models/';
            $this->views_root = $this->app_root.'views/';
            $this->filters_root = $this->app_root.'filters/';
        }
    }


    /**
     * @return bool
     */
    private function setParams() {

        global $argv;
        $this->argv = $argv;
        if (empty($this->argv[1])) {
            $this->error('Error: To teleport i need parameters', false);
            $this->error('To get help type ./teleport help or ./teleport -h');
        }

        $this->params = array();
        parse_str(implode('&', array_slice($argv, 1)), $this->params);

        return true;
    }


    /**
     *
     */
    private function help() {

        $this->output('*****************************');
        $this->output('Quantum Teleport Help:');
        $this->output('Available methods:');
        $this->output('*****************************');
        $this->output('');
        $this->output('Teleport a controller: ');
        $this->output('./teleport controller app=AppName name=ControllerName');
        $this->output('./teleport -c app= name=');
        $this->output('EX: ./teleport -c app=admin name=blog - Will generate a BlogController');
        $this->output('Optional parameters:');
        $this->output('public = Comma delimited series of public functions to generate on the controller');
        $this->output('private = Comma delimited series of private functions to generate on the controller');
        $this->output('protected = Comma delimited series of protected functions to generate on the controller');
        $this->output('EX: ./teleport -c name=blog public=latest,all private=savePostHook protected=deletePostHook');
        $this->output('');
        $this->output('*****************************');
        $this->output('');
        $this->output('Teleport a model: ');
        $this->output('./teleport model name= app=');
        $this->output('./teleport -m name=');
        $this->output('EX: ./teleport -m app=admin name=post - Will generate a Post model');
        $this->output('Optional parameters:');
        $this->output('public = Comma delimited series of public functions to generate on the model');
        $this->output('private = Comma delimited series of private functions to generate on the model');
        $this->output('protected = Comma delimited series of protected functions to generate on the model');
        $this->output('EX: ./teleport -m app=admin name=player public=increaseScore private=initScore protected=resetScore');
        $this->output('');
        $this->output('*****************************');
        $this->output('');
        $this->output('Teleport a view: ');
        $this->output('./teleport view controller=blog, action=index app=');
        $this->output('./teleport -v controller=blog, action=index app=');
        $this->output('EX: ./teleport -v app=admin controller=blog action=index - Will generate a view in app/views/blog/index.tpl');
        $this->output('');
        $this->output('*****************************');
        $this->output('');
        $this->output('Teleport a filter: ');
        $this->output('./teleport filter name= type= app=');
        $this->output('./teleport -f app=admin name= type=');
        $this->output('EX: ./teleport -f app=admin name=if_logged type=before - Will generate a Before Filter');
        $this->output('Type of filters available type=before, type=after');
        $this->output('');
        $this->output('*****************************');
        $this->output('');
        $this->output('Teleport a template: ');
        $this->output('./teleport template name= app=');
        $this->output('./teleport -t app=admin name=cms');
        $this->output('EX: ./teleport -t app=admin name=cms - Will generate a template named cms in the admin app');
        $this->output('');
        $this->output('*****************************');

    }


    /**
     *
     */
    private function initSmarty() {

        define('SMARTY_DIR', $this->libs_root.'smarty/');
        define('SMARTY_SYSPLUGINS_DIR', $this->libs_root.'smarty/sysplugins/');
        define('SMARTY_PLUGINS_DIR', $this->libs_root.'smarty/plugins');
        require_once ($this->libs_root.'smarty/bootstrap.php');

        $this->smarty = new \Smarty();
        $this->smarty ->template_dir = $this->views_root;
        $this->smarty->compile_dir =   $this->tmp_root;
        //$this->smarty->allow_php_tag = true;
        //$this->smarty->plugins_dir[] = $this->libs_root.'smarty/plugins';

    }


    /**
     * @param $string
     * @param bool $newline
     */
    private function output($string, $newline = true) {

        if ($newline) {
            echo($string . "\n");
        } else {
            echo($string);
        }
    }

    /**
     * @param $string
     * @param bool $kill
     */
    private function error($string, $kill = true) {

        if ($kill) {
            $this->output($string);
            exit();
        }
        else {
            $this->output($string);
        }

    }

    /**
     *
     */
    private function teleportApp()
    {
        if (!isset($this->params['name']) )
        {
            $this->error('Error: You must pass a name parameter to teleport an app', false);
            $this->error('EX: ./teleport app name=warehouse, will generate a Warehouse App');
        }

        $name = $this->params['name'];

        $this->output("Teleporting an app: ". $name);

        $this->initSmarty();

        $app_root = $this->ipt->hosted_apps_root.$name;

        $dirs = array(
            $app_root."/controllers",
            $app_root."/filters",
            $app_root."/helpers",
            $app_root."/models",
            $app_root."/templates",
            $app_root."/observers",
            $app_root."/middleware",
            $app_root."/modules",
            $app_root."/workers"
        );

        foreach ($dirs as $dir)
        {
            $this->createDirIfNeeded($dir);
        }

        $c = $this->smarty->fetch($this->matter_root.'app.tpl');

        $this->createFile($app_root,'/App.php', $c);

    }

    /**
     *
     */
    private function teleportTest()
    {
        if (!isset($this->params['name']) )
        {
            $this->error('Error: You must pass a name parameter to teleport a test', false);
            $this->error('EX: ./teleport test name=something, will generate a something Test');
        }

        $name = ucfirst($this->params['name'])."Test";

        $this->output("Teleporting a test: ". $name);

        $this->initSmarty();
        $this->smarty->assign('test_name', $name);

        $c = $this->smarty->fetch($this->matter_root.'test.tpl');

        $this->createFile($this->ipt->tests_root, $name.'.php', $c);

    }

    /**
     *
     */
    private function teleportTemplate()
    {
        if (!isset($this->params['name']) || !isset($this->params['app']) )
        {
            $this->error('Error: You must pass a name parameter to teleport a template', false);
            $this->error('EX: ./teleport template name=cms app=admin, will generate a cms template in the admin app');
        }

        $this->ipt->updateAppRoot($this->params['app']);
        $this->setFolders();

        $name = $this->params['name'];

        $this->output("Teleporting a template: ". $name);

        $templates_root = $this->ipt->templates_root;

        $template_root = $templates_root.$name;
        $layout_root = $template_root."/layout";
        $views_root = $template_root."/views";

        $dirs = array(
            $template_root,
            $layout_root,
            $views_root
        );

        foreach ($dirs as $dir)
        {
            $this->createDirIfNeeded($dir);
        }

        $this->createFile($layout_root,'/header.tpl', "");
        $this->createFile($layout_root,'/footer.tpl', "");

    }

    /**
     *
     */
    private function teleportController()
    {
        if (!isset($this->params['name']) || !isset($this->params['app']))
        {
            $this->error('Error: You must pass name and app parameters to teleport a controller', false);
            $this->error('EX: ./teleport controller app=admin name=blog will generate a BlogController in the admin app');
        }

        $this->ipt->updateAppRoot($this->params['app']);
        $this->setFolders();

        $name = ucfirst($this->params['name']).'Controller';

        $this->output("Teleporting a controller: ". $name);

        $this->initSmarty();
        $this->smarty->assign('controller_name', $name);

        if (isset($this->params['public'])) {
            $public_functions = explode(',', $this->params['public']);
            $this->smarty->assign('public_functions', $public_functions);
        }

        if (isset($this->params['private'])) {
            $private_functions = explode(',', $this->params['private']);
            $this->smarty->assign('private_functions', $private_functions);
        }

        if (isset($this->params['protected'])) {
            $protected_functions = explode(',', $this->params['protected']);
            $this->smarty->assign('protected_functions', $protected_functions);
        }

        $c = $this->smarty->fetch($this->matter_root.'controller.tpl');

        $this->createFile($this->controllers_root, $name.'.php', $c);


    }


    /**
     *
     */
    private function teleportModel() {

        if (!isset($this->params['name']) || !isset($this->params['app']))
        {
            $this->error('Error: You must pass name and app parameters to teleport a model', false);
            $this->error('EX: ./teleport model app=admin name=user will generate a User.php file at app/models in the admin app');
        }

        $this->ipt->updateAppRoot($this->params['app']);
        $this->setFolders();

        $name = ucfirst($this->params['name']);

        $this->output("Teleporting a model: ". $name);

        $this->initSmarty();
        $this->smarty->assign('model_name', $name);

        if (isset($this->params['public']))
        {
            $public_functions = explode(',', $this->params['public']);
            $this->smarty->assign('public_functions', $public_functions);
        }

        if (isset($this->params['private']))
        {
            $private_functions = explode(',', $this->params['private']);
            $this->smarty->assign('private_functions', $private_functions);
        }

        if (isset($this->params['protected']))
        {
            $protected_functions = explode(',', $this->params['protected']);
            $this->smarty->assign('protected_functions', $protected_functions);
        }

        $c = $this->smarty->fetch($this->matter_root.'model.tpl');

        $this->createFile($this->models_root, $name.'.php', $c);


    }


    /**
     *
     */
    private function teleportView()
    {

        if (!isset($this->params['controller']) || !isset($this->params['action']) || !isset($this->params['app']) || !isset($this->params['template'])) {
            $this->error('Error: You must pass a controller and action to generate a view.tpl', false);
            $this->error('EX: ./teleport view app=admin controller=blog action=index template=cms will generate a index.tpl file at app/views/blog');
        }

        $this->ipt->updateAppRoot($this->params['app']);
        $this->setFolders();

        $name = $this->params['controller'].'/'.$this->params['action'];

        $this->output("Teleporting a view: ".$name);

        $view_dir = $this->ipt->templates_root.$this->params['template'].'/views/'.$this->params['controller']."/";

        if (!is_dir($view_dir)) {
            $this->output("View directory not exists... creating it.");
            $this->createDirIfNeeded($view_dir);
        }

        $this->createFile($view_dir, $this->params['action'].'.tpl', $c = null);

    }


    /**
     *
     */
    private function teleportFilter() {

        if (!isset($this->params['name']) || !isset($this->params['type']) || !isset($this->params['app']) ) {
            $this->error('Error: You must pass app, name and type params to generate a filter', false);
            $this->error('EX: ./teleport filter app=admin name=if_logged type=before, will generate a before_filter_if_logged.php file at app/filters');
        }

        $this->ipt->updateAppRoot($this->params['app']);
        $this->setFolders();

        if ($this->params['type'] == "before") {
            $name = "before_filter_".$this->params['name'];
            $template = "before_filter.tpl";
        }

        if ($this->params['type'] == "after") {
            $name = "after_filter_".$this->params['name'];
            $template = "after_filter.tpl";
        }

        $this->output("Teleporting a filter: ". $name);

        $this->initSmarty();
        $this->smarty->assign('filter_name', $name);

        $c = $this->smarty->fetch($this->matter_root.$template);

        $this->createFile($this->filters_root, $name.'.php', $c);


    }


    /**
     * @param $location
     * @param $filename
     * @param $contents
     */
    private function createFile($location, $filename, $contents) {

        $filename = $location.$filename;

        if (file_exists($filename)) {
            $this->error('Error: File already exists: '.$filename);
        }

        $this->output('Creating file:');
        $this->output($filename);

        if (!$handle = fopen($filename, 'w')) {
            $this->error("Cannot open file ($filename)");
        }

        if (fwrite($handle, $contents) === FALSE) {
            $this->error("Cannot write to file ($filename)");
        }

        $this->output('Success, created file: '.qf($filename)->getRealPath());

        fclose($handle);


    }

    /**
     * @param $path
     * @return bool
     */
    function createDirIfNeeded($path)
    {
        if (is_dir($path))
            return true;

        $prev_path = substr($path, 0, strrpos($path, '/', -2) + 1 );
        $return = self::createDirIfNeeded($prev_path);
        $result = ($return && is_writable($prev_path)) ? mkdir($path) : false;

        if ($result)
            $this->output("Success, created dir: ($path)");
        else
            $this->error("Cannot create dir ($path)");

        return $result;
    }

    /**
     * @param $shouldBeOnMaintenance
     */
    function setMaintenace($shouldBeOnMaintenance)
    {
        $location = $this->ipt->locks_root;
        $name = "maintenance.lock";
        $file = $location.$name;

        if ($shouldBeOnMaintenance)
        {
            $this->createFile($location, $name, date("Y-m-d h:i:sa"));
            $this->output("Maintenance mode file created.");
        }
        else
        {
            if (file_exists($file))
            {
                unlink($file);
                $this->output("Removed maintenance file.");
            }

        }
    }

    /**
     * @param $shouldBeOnMaintenance
     */
    function setProfiler($enableProfiler)
    {
        $location = $this->ipt->locks_root;
        $name = "profiler.lock";
        $file = $location.$name;

        if ($enableProfiler)
        {
            $this->createFile($location, $name, date("Y-m-d h:i:sa"));
            $this->output("Profiler enabled");
        }
        else
        {
            if (file_exists($file))
            {
                unlink($file);
                $this->output("Profiler Disabled");
            }

        }
    }

    /**
     * @throws \Defuse\Crypto\Exception\BadFormatException
     * @throws \Defuse\Crypto\Exception\EnvironmentIsBrokenException
     */
    function teleportMasterKeys()
    {
        $encryption_key = Quantum\Crypto::genKey();

        $key = \Quantum\RSACrypto::genKey();
        $private_key = $key['privatekey'];
        $public_key  = $key['publickey'];

        $private_key = \Quantum\Crypto::encrypt($private_key, $encryption_key);
        $public_key  = \Quantum\Crypto::encrypt($public_key, $encryption_key);

        $data = array(
            'master_encryption_key' => $encryption_key,
            'public_encrypted_rsa_key' => $public_key,
            'private_encrypted_rsa_key' => $private_key
        );

        $dir = Quantum\InternalPathResolver::getInstance()->config_root;

        $contents = json_encode($data, JSON_PRETTY_PRINT);

        $file = qf ($dir."keys.json");

        if ($file->exists())
            $file->delete();

        $file->create($contents);

        $this->output("Created keys file at: ");
        $this->output($file->getRealPath());
    }

    /**
     * @throws Exception
     */
    function geoIpDbUpdate()
    {
        $file = qf(Quantum\MaxmindGeoIp::downloadDb());

        if ($file->exists())
        {
            $this->output("Created MaxMind GeoIp DB file at: ");
            $this->output($file->getRealPath());
        }
        else
        {
            $this->output("Impossible to create file: ");
            $this->error($file->getRealPath());
        }

    }

    private function genApiDocs()
    {
        $path = qf($this->ipt->docs_root)->getChildFile('config')->getChildFile('kernel.config')->getRealPath();

        $this->callDoxygen($path);

        $this->output('Api docs generated!');
    }

    private function genAppsDocs()
    {
        $path = qf($this->ipt->docs_root)->getChildFile('config')->getChildFile('apps.config')->getRealPath();

        $this->callDoxygen($path);

        $this->output('Apps docs generated!');
    }

    private function genGlobalDocs()
    {
        $path = qf($this->ipt->docs_root)->getChildFile('config')->getChildFile('all.config')->getRealPath();

        $this->callDoxygen($path);

        $this->output('Global docs generated!');
    }


    private function callDoxygen($path)
    {
        $command = 'doxygen ' .qs($path)->surround("'");

        $exec = new Quantum\Exec($command);
        $exec->launch();
    }

    private function genPhpdocApiDocs()
    {
        $command = 'php phpDocumentor.phar -d ../kernel -t ../extras/phpdocs/api';

        $exec = new Quantum\Exec($command);
        $exec->launch();

        $this->output('Api PhpDocs generated!');
    }

    private function genPhpdocAppsDocs()
    {
        $command = 'php phpDocumentor.phar -d ../apps -t ../extras/phpdocs/apps';

        $exec = new Quantum\Exec($command);
        $exec->launch();

        $this->output('Apps PhpDocs generated!');
    }

    private function genPhpdocGlobalDocs()
    {
        $command = 'php phpDocumentor.phar -d ../../quantum -t ../extras/phpdocs/global';

        $exec = new Quantum\Exec($command);
        $exec->launch();

        $this->output('Global PhpDocs generated!');
    }

    private function genAllDocs()
    {
        $this->genApiDocs();
        $this->genAppsDocs();
        $this->genGlobalDocs();

        $this->genPhpdocApiDocs();
        $this->genPhpdocAppsDocs();
        $this->genPhpdocGlobalDocs();
    }

    private function teleportMigration()
    {
        if (!isset($this->params['name']) )
        {
            $this->error('Error: You must pass a name  to teleport a migration', false);
            $this->error('EX: ./teleport migration name=MyMigration will generate a migration in quantum/local/etc/migrations/');
        }

        $name = $this->params['name'];


        $ipt = InternalPathResolver::getInstance();
        $phinx = qf($ipt->quantum_root)->getSiblingFile('composer')->getChildFile('vendor/bin/phinx ');

        $command = $phinx->getPath().' create '.$name;

        $this->output($command);

        $exec = new Quantum\Exec($command);
        $exec->launch();

        $this->output('Migration generated!');
    }

    private function genSharedPluginMigration()
    {
        if (!isset($this->params['name']) || !isset($this->params['plugin']) )
        {
            $this->error('Error: You must pass a name and a plugin to teleport a migration', false);
            $this->error('EX: ./teleport shared-plugin-migration name=MyMigration plugin=plugin_folder will generate a migration in quantum/apps/shared/plugins/plugin_folder/migrations');
        }

        $name = $this->params['name'];
        $plugin_folder = $this->params['plugin'];

        $ipt = InternalPathResolver::getInstance();
        $path = qf($ipt->shared_app_plugins_root)->getChildFile($plugin_folder);

        $this->genMigration($name, $path);
    }

    private function genAppMigration()
    {
        if (!isset($this->params['name']) || !isset($this->params['app']) )
        {
            $this->error('Error: You must pass a name and an app to teleport a migration', false);
            $this->error('EX: ./teleport app-migration name=MyMigration app=default will generate a migration in quantum/apps/hosted/default/migrations');
        }

        $name = $this->params['name'];
        $app_folder = $this->params['app'];

        $ipt = InternalPathResolver::getInstance();
        $path = qf($ipt->hosted_apps_root)->getChildFile($app_folder);

        $this->genMigration($name, $path);
    }

    private function genAppPluginMigration()
    {
        if (!isset($this->params['name']) || !isset($this->params['app']) || !isset($this->params['plugin']) )
        {
            $this->error('Error: You must pass a migration name an app and a plugin to teleport a migration', false);
            $this->error('EX: ./teleport app-plugin-migration name=MyMigration app=default plugin=plugin_folder will generate a migration in quantum/apps/hosted/default/plugin_folder/migrations');
        }

        $name = $this->params['name'];
        $app_folder = $this->params['app'];
        $plugin_folder = $this->params['plugin'];

        $ipt = InternalPathResolver::getInstance();
        $path = qf($ipt->hosted_apps_root)->getChildFile($app_folder)->getChildFile('plugins')->getChildFile($plugin_folder);

        $this->genMigration($name, $path);
    }

    private function genMigration ($class_name, $path)
    {
        if (!$path->isDirectory()) {
            $this->error('Error: Migration target base path not found:'.$path->getPath(), false);
            return;
        }

        $migrations_dir = $path->getChildFile('migrations');

        if (!$migrations_dir->isDirectory()) {
            $migrations_dir->create();
        }

        $file_name = \Phinx\Util\Util::mapClassNameToFileName($class_name);

        $this->initSmarty();
        $this->smarty->assign('migration_name', $class_name);

        $content = $this->smarty->fetch($this->matter_root.'migration.tpl');

        $this->createFile(ensure_last_slash($migrations_dir->getPath()), $file_name, $content);

        $this->output('Migration generated!');
    }

    private function genSharedPluginSeed()
    {
        if (!isset($this->params['name']) || !isset($this->params['plugin']) )
        {
            $this->error('Error: You must pass a name and a plugin to teleport a migration', false);
            $this->error('EX: ./teleport shared-plugin-seed name=MyMigration plugin=plugin_folder will generate a seed in quantum/apps/shared/plugins/plugin_folder/migrations/seeds');
        }

        $name = $this->params['name'];
        $plugin_folder = $this->params['plugin'];

        $ipt = InternalPathResolver::getInstance();
        $path = qf($ipt->shared_app_plugins_root)->getChildFile($plugin_folder);

        $this->genSeed($name, $path);
    }

    private function genAppSeed()
    {
        if (!isset($this->params['name']) || !isset($this->params['app']) )
        {
            $this->error('Error: You must pass a name and an app to teleport a seed', false);
            $this->error('EX: ./teleport app-seed name=MySeed app=default will generate a seed in quantum/apps/hosted/default/migrations/seeds');
        }

        $name = $this->params['name'];
        $app_folder = $this->params['app'];

        $ipt = InternalPathResolver::getInstance();
        $path = qf($ipt->hosted_apps_root)->getChildFile($app_folder);

        $this->genSeed($name, $path);
    }

    private function genAppPluginSeed()
    {
        if (!isset($this->params['name']) || !isset($this->params['app']) || !isset($this->params['plugin']) )
        {
            $this->error('Error: You must pass a migration name an app and a plugin to teleport a seed', false);
            $this->error('EX: ./teleport app-plugin-seed name=MySeed app=default plugin=plugin_folder will generate a seed in quantum/apps/hosted/default/plugin_folder/migrations/seeds');
        }

        $name = $this->params['name'];
        $app_folder = $this->params['app'];
        $plugin_folder = $this->params['plugin'];

        $ipt = InternalPathResolver::getInstance();
        $path = qf($ipt->hosted_apps_root)->getChildFile($app_folder)->getChildFile('plugins')->getChildFile($plugin_folder);

        $this->genSeed($name, $path);
    }

    private function genSeed ($class_name, $path)
    {
        if (!$path->isDirectory()) {
            $this->error('Error: Migration target base path not found:'.$path->getPath(), false);
            return;
        }

        $seeds_dir = $path->getChildFile('migrations')->getChildFile('seeds');

        if (!$seeds_dir->isDirectory()) {
            $seeds_dir->create();
        }

        $file_name = \Phinx\Util\Util::mapClassNameToFileName($class_name);

        $this->initSmarty();
        $this->smarty->assign('seed_name', $class_name);

        $content = $this->smarty->fetch($this->matter_root.'seed.tpl');

        $this->createFile(ensure_last_slash($seeds_dir->getPath()), $file_name, $content);

        $this->output('Seed generated!');
    }

    private function setConfigEnvironmentByInstance()
    {
        $instance = isset($this->params['env']) ? $this->params['env'] : "development";
        \Quantum\Config::getInstance()->setEnvironmentByInstance($instance);
    }

    private function executeAppMigrations()
    {
        if (!isset($this->params['app']) )
        {
            $this->error('Error: You must pass an app folder name to execute its migrations', false);
            $this->error('EX: ./teleport execute-app-migrations app=default will execute migrations in quantum/apps/hosted/default/migrations');
        }

        $app_folder = $this->params['app'];

        $ipt = InternalPathResolver::getInstance();
        $path = qf($ipt->hosted_apps_root)->getChildFile($app_folder)->getChildFile('migrations');

        $this->setConfigEnvironmentByInstance();

        $phinx = new PhinxMigrationRunner($path->getPath());
        $phinx->executeMigrations();
    }



    private function rollbackAppMigrations()
    {
        if (!isset($this->params['app']) )
        {
            $this->error('Error: You must pass an app folder name to execute its migrations', false);
            $this->error('EX: ./teleport rollback-app-migrations app=default will rollback migrations in quantum/apps/hosted/default/migrations');
        }

        $app_folder = $this->params['app'];

        $ipt = InternalPathResolver::getInstance();
        $path = qf($ipt->hosted_apps_root)->getChildFile($app_folder)->getChildFile('migrations');

        $this->setConfigEnvironmentByInstance();

        $phinx = new PhinxMigrationRunner($path->getPath());
        $phinx->rollbackMigrations();
    }

    private function executeAppPluginMigration()
    {
        if (!isset($this->params['app']) || !isset($this->params['plugin']) )
        {
            $this->error('Error: You must pass an app and a plugin to run its migration', false);
            $this->error('EX: ./teleport execute-app-plugin-migrations app=default plugin=plugin_folder will execute migrations in quantum/apps/hosted/default/plugin_folder/migrations');
        }

        $app_folder = $this->params['app'];
        $plugin_folder = $this->params['plugin'];

        $ipt = InternalPathResolver::getInstance();
        $path = qf($ipt->hosted_apps_root)->getChildFile($app_folder)->getChildFile('plugins')->getChildFile($plugin_folder)->getChildFile('migrations');

        $this->setConfigEnvironmentByInstance();

        $phinx = new PhinxMigrationRunner($path->getPath());
        $phinx->executeMigrations();
    }

    private function rollbackAppPluginMigration()
    {
        if (!isset($this->params['app']) || !isset($this->params['plugin']) )
        {
            $this->error('Error: You must pass an app and a plugin to rollback its migration', false);
            $this->error('EX: ./teleport rollback-app-plugin-migrations app=default plugin=plugin_folder will rollback migrations in quantum/apps/hosted/default/plugin_folder/migrations');
        }

        $app_folder = $this->params['app'];
        $plugin_folder = $this->params['plugin'];

        $ipt = InternalPathResolver::getInstance();
        $path = qf($ipt->hosted_apps_root)->getChildFile($app_folder)->getChildFile('plugins')->getChildFile($plugin_folder)->getChildFile('migrations');

        $this->setConfigEnvironmentByInstance();

        $phinx = new PhinxMigrationRunner($path->getPath());
        $phinx->rollbackMigrations();
    }

    private function executeSharedPluginMigration()
    {
        if (!isset($this->params['plugin']) )
        {
            $this->error('Error: You must pass a plugin folder dir to execute its migration', false);
            $this->error('EX: ./teleport execute-shared-plugin-migrations plugin=plugin_folder will execute migrations in quantum/apps/shared/plugins/plugin_folder/migrations');
        }

        $plugin_folder = $this->params['plugin'];

        $ipt = InternalPathResolver::getInstance();
        $path = qf($ipt->shared_app_plugins_root)->getChildFile($plugin_folder)->getChildFile('migrations');

        $this->setConfigEnvironmentByInstance();

        $phinx = new PhinxMigrationRunner($path->getPath());
        $phinx->executeMigrations();
    }

    private function rollbackSharedPluginMigration()
    {
        if (!isset($this->params['plugin']) )
        {
            $this->error('Error: You must pass a plugin folder dir to rollback its migration', false);
            $this->error('EX: ./teleport rollback-shared-plugin-migrations plugin=plugin_folder will rollback migrations in quantum/apps/shared/plugins/plugin_folder/migrations');
        }

        $plugin_folder = $this->params['plugin'];

        $ipt = InternalPathResolver::getInstance();
        $path = qf($ipt->shared_app_plugins_root)->getChildFile($plugin_folder)->getChildFile('migrations');

        $this->setConfigEnvironmentByInstance();

        $phinx = new PhinxMigrationRunner($path->getPath());
        $phinx->rollbackMigrations();
    }

    private function executeMigration()
    {
        $environment = isset($this->params['env']) ? $this->params['env'] : "development";

        $ipt = InternalPathResolver::getInstance();
        $phinx = qf($ipt->quantum_root)->getSiblingFile('composer')->getChildFile('vendor/bin/phinx ');

        $command = $phinx->getPath().' migrate -e '.$environment;

        $this->output($command);

        $exec = new Quantum\Exec($command);
        $exec->launch();

        $this->output('Migrations executed!');
    }

    private function rollbackMigration()
    {
        $environment = isset($this->params['env']) ? $this->params['env'] : "development";

        $ipt = InternalPathResolver::getInstance();
        $phinx = qf($ipt->quantum_root)->getSiblingFile('composer')->getChildFile('vendor/bin/phinx ');

        $command = $phinx->getPath().' rollback -e '.$environment;

        $this->output($command);

        $exec = new Quantum\Exec($command);
        $exec->launch();

        $this->output('Migration rollback!');
    }

    private function stopServer()
    {
        $location = $this->ipt->locks_root;
        $name = "server.pid";

        $pidFile = qf($location.$name);

        if ($pidFile->existsAsFile())
        {
            $pid = $pidFile->loadAsString();

            $exec = new Quantum\Exec("kill -9 ".$pid);
            $exec->launch();

            $this->output('Teleport Server Stopped');

            $pidFile->delete();

            return;
        }
    }

    private function startServer()
    {
        if (isset($_ENV["QM_TELEPORT_SERVER_APP"]))
            putenv("QM_TELEPORT_SERVER_APP");

        $port = isset($this->params['port']) ? $this->params['port'] : "6890";

        if (isset($this->params['app']))
        {
            putenv("QM_TELEPORT_SERVER_APP=".$this->params['app']);
        }

        $command = 'php -S localhost:'.$port.' -t ../../webroot ../../webroot/index.php';

        $this->output('Teleport Server started on Port: '.$port);

        $exec = new Quantum\Exec($command);
        $pid = $exec->launchInBackground();

        $this->output('Teleport Server ProcessID: '.$pid);

        $location = $this->ipt->locks_root;
        $name = "server.pid";

        $this->createFile($location, $name, $pid);
    }

    private function server()
    {
        $this->stopServer();

        $this->startServer();
    }

    private function updateKernel()
    {
        $updater = new Updater();

        $result = $updater->updateKernel();

        if ($result->wasOk())
        {
            $release = $updater->getLatestRelease();
            cli_echo('Success, kernel updated to version: '.$release->tag_name);
        }
        else {
            cli_echo('Error');
            cli_echo($result->getErrorMessage());
        }

    }

    private function kernelUpdateCheck()
    {
        $updater = new Updater();

        $result = $updater->isKernelUpdateAvailable();

        if ($result)
        {
            $release = $updater->getLatestRelease();
            cli_echo('Version update avaiable: '.$release->tag_name);
        }
        else {
            cli_echo('It seems you are on the latest version.');
        }
    }



}