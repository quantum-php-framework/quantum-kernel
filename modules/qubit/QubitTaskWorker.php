<?php

namespace Quantum\Qubit;

use RuntimeException;

class QubitTaskWorker
{

    const STATUS_COMPLETED = 'Completed';
    const STATUS_FAILED = 'Failed';

    private $task;
    private $environment_instance;
    private $forked_pid;
    private $child_exit_code;

    private $start_date;
    private $end_date;

    public function __construct(QubitTask $task)
    {
        $this->task = $task;

        global $argv;
        if (isset($argv[1]))
            $this->environment_instance = $argv[1];
        else
            $this->environment_instance = 'development';
    }


    public function perform()
    {
        if(function_exists('pcntl_fork'))
        {
            return $this->performForked();
        }
        else
        {
            return $this->childProcessWork();
        }
    }

    private function performForked()
    {
        $pid = self::fork();

        if ($pid === 0 || $pid === false || $pid === -1)
        {
            cli_echo($pid.'child working...');
            $this->childProcessWork();

            if ($pid === 0) {
                exit(0);
            }
        }

        if($pid > 0)
        {
            $status = 'Forked ' . $pid . ' at ' . strftime('%F %T');
            $this->forked_pid = $pid;
            cli_echo($status);

            while (pcntl_wait($status, WNOHANG) === 0)
            {
                if(function_exists('pcntl_signal_dispatch'))
                {
                    pcntl_signal_dispatch();
                }

                usleep(500000);
            }

            if (pcntl_wifexited($status) !== true)
            {
                cli_echo('Job exited abnormally');
                return self::STATUS_FAILED;
            }
            elseif (($exitStatus = pcntl_wexitstatus($status)) !== 0)
            {
                cli_echo('Job exited with exit code ' . $exitStatus);
                $this->child_exit_code = $exitStatus;
                return self::STATUS_FAILED;
            }
            else
            {
                cli_echo('Job completed:'.$pid);
                $this->child_exit_code = pcntl_wexitstatus($status);
                return self::STATUS_COMPLETED;
            }

        }

    }

    /**
     * @return mixed
     */
    public function getChildExitCode()
    {
        return $this->child_exit_code;
    }

    /**
     * @return mixed
     */
    public function getForkedPid()
    {
        return $this->forked_pid;
    }

    private function childProcessWork()
    {
        $app = $this->locateHostedApp($this->task->getApp());

        \Quantum\Config::getInstance()->setAppConfig($app);
        \Quantum\Autoloader::getInstance()->initDirectories();

        $this->initEnvironment($app);
        $this->initActiveRecord($app);

        //
        $app = \Quantum\HostedAppFactory::create();

        $this->callClassMethod($app,'cli_init');

        $this->callClassMethod($app,"cli_pre_controller_dispatch");
        //

        $className = qs($this->task->getTask())->upToFirstOccurrenceOf(':')->toStdString();

        if (class_exists($className))
        {
            $worker = new $className();

            $this->callClassMethod($worker, 'before_perform');

            $this->callClassMethod($worker, 'perform', $this->task->getOptions());

            $this->callClassMethod($worker, 'after_perform');
        }
        else
        {
            echo 'class not found:'.$className.PHP_EOL;
        }

        $this->callClassMethod($app,"cli_post_controller_dispatch");

        $this->callClassMethod($app,"cli_shutdown");

        return self::STATUS_COMPLETED;
    }

    private static function fork()
    {
        if(!function_exists('pcntl_fork')) {
            echo 'pcntl_fork not installed';
            //halt();
            return false;
        }

        $pid = pcntl_fork();
        if($pid === -1) {
            throw new RuntimeException('Unable to fork child worker.');
        }

        return $pid;
    }

    /**
     * @param $method_name
     */
    private function callClassMethod($class, $method, $data = null)
    {
        $className = class_basename($class);

        if (method_exists($class, $method))
        {
            cli_echo('Executing: '.$className.'::'.$method);
            call_user_func_array([$class, $method], [$data]);
        }
        else
        {
            cli_echo('Method not found: '.$className.'::'.$method);
        }
    }

   private function locateHostedApp($appName)
   {
       $apps = \Quantum\Config::getInstance()->getHostedApps();

       foreach ($apps as $app)
       {
           if ($app['uri'] == $appName)
               return $app;
       }

       return null;
   }


    /**
     * @param $app
     */
    private function initEnvironment($app)
    {
        $ipt = \Quantum\InternalPathResolver::getInstance();

        $config_root = $ipt->config_root;

        $cfg_file = $config_root.'environment.php';

        if (!is_file($cfg_file))
            \trigger_error("environment.php not found in config directory", E_USER_ERROR);

        require ($cfg_file);

        if (!isset($QUANTUM_ENVIRONMENTS))
            \trigger_error("QUANTUM_ENVIRONMENTS are not set", E_USER_ERROR);

        $current_env = '';

        foreach ($QUANTUM_ENVIRONMENTS as $key => $environment)
        {
            if (qs($environment['domain'])->startsWith($app['uri']) && $environment['instance'] == $this->environment_instance)
            {
                $current_env = (object)$environment;
            }
        }

        if (is_object($current_env))
        {
            \Quantum\Config::getInstance()->setEnvironment($current_env);
        }
        else
        {
            echo 'no environment found'.PHP_EOL;
        }
    }

    /**
     *
     */
    private function initActiveRecord($app)
    {
        \Quantum\Import::library('activerecord/ActiveRecord.php');

        $ipt = \Quantum\InternalPathResolver::getInstance();

        $environment = \Quantum\Config::getInstance()->getEnvironment();



        $cfg = \ActiveRecord\Config::instance();
        $models_dirs = array(qf($ipt->models_root)->getRealPath(), qf($ipt->shared_app_activerecord_models_root)->getRealPath());

        //var_dump($models_dirs);
        $cfg->set_model_directory($models_dirs);

        $conn = array(
            $environment->instance => 'mysql://' . $environment->db_user . ':' .
                $environment->db_password . '@' .
                $environment->db_host . '/' .
                $environment->db_name . ''

        );

        //var_dump($conn);

        $cfg->set_connections($conn, $environment->instance);
    }
}