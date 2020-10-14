<?php

namespace Quantum;

/**
 * Class Exec
 * @package Quantum
 */
class Exec
{
    /**
     * @var string
     */
    public $commandLine;
    /**
     * @var
     */
    public $resultCode;
    /**
     * @var
     */
    public $output;
    /**
     * @var
     */
    public $pid;

    /**
     * @param $commandLine
     * @return Exec
     */
    public static function withCommandLine ($commandLine)
    {
        return new Exec ($commandLine);
    }

    /**
     * Exec constructor.
     * @param $commandLine
     */
    function __construct ($commandLine)
    {
        $this->commandLine = escapeshellcmd($commandLine);
    }

    /**
     * @return string
     */
    public function launch ()
    {
        return exec (($this->commandLine), $this->output, $this->resultCode);
    }

    /**
     * @return string
     */
    public function getCommandLine ()
    {
        return $this->commandLine;
    }

    /**
     * @return mixed
     */
    public function getResultCode ()
    {
        return $this->resultCode;
    }

    /**
     * @return mixed
     */
    public function getOutput ()
    {
        return $this->output;
    }

    /**
     * @param int $priority
     * @return string|null
     */
    public function launchInBackground($priority = 0)
    {
        $command = $this->commandLine;

        if($priority)
            $this->pid = shell_exec("nohup nice -n $priority $command > /dev/null & echo $!");
        else
            $this->pid = shell_exec("nohup $command > /dev/null & echo $!");

        return($this->pid);
    }

    /**
     * @return bool
     */
    public function isRunning()
    {
        exec("ps $this->pid", $processState);
        return(count($processState) >= 2);
    }

    /**
     * @return bool
     */
    function kill()
    {
        if($this->isRunning($this->pid))
        {
            exec("kill -KILL $this->pid");
            return true;
        }

        return false;
    }

}