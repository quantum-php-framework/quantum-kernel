<?php

namespace Quantum\Qubit;

class QubitTask
{
    public $app;
    public $key;
    public $task;
    public $data;
    public $status;
    public $exit_code;
    public $pid;
    public $start_date;
    public $end_date;
    public $execution_errors;



    public function __construct($app, $key, $task, $options)
    {
        $this->app = $app;
        $this->key = $key;
        $this->task = $task;
        $this->options = $options;
        $this->uuid = \quuid();
        $this->created_at = \datestamp();
    }

    /**
     * @return mixed
     */
    public function getProcessId()
    {
        return $this->pid;
    }

    /**
     * @param mixed $pid
     */
    public function setProcessId($pid)
    {
        $this->pid = $pid;
    }

    /**
     * @param mixed $exit_code
     */
    public function setExitCode($exit_code)
    {
        $this->exit_code = $exit_code;
    }

    public function setStatus($status)
    {
        $this->status = $status;
    }

    /**
     * @return mixed
     */
    public function getApp()
    {
        return $this->app;
    }

    /**
     * @return mixed
     */
    public function getKey()
    {
        return $this->key;
    }

    /**
     * @return mixed
     */
    public function getTask()
    {
        return $this->task;
    }

    /**
     * @return mixed
     */
    public function getOptions()
    {
        return $this->options;
    }

    /**
     * @return mixed
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @return mixed
     */
    public function getStartDate()
    {
        return $this->start_date;
    }

    /**
     * @param mixed $start_date
     */
    public function setStartDate($start_date)
    {
        $this->start_date = $start_date;
    }

    /**
     * @return mixed
     */
    public function getEndDate()
    {
        return $this->end_date;
    }

    /**
     * @param mixed $end_date
     */
    public function setEndDate($end_date)
    {
        $this->end_date = $end_date;
    }

    public function isCompleted()
    {
        return $this->status === QubitTaskWorker::STATUS_COMPLETED;
    }

    public function hasFailed()
    {
        return $this->status === QubitTaskWorker::STATUS_FAILED;
    }

    public function isValid()
    {
        $validator = new QubitTaskValidator($this);
        return $validator->isValid();
    }

    /**
     * @return mixed
     */
    public function getExecutionErrors()
    {
        return $this->execution_errors;
    }

    /**
     * @param mixed $execution_errors
     */
    public function setExecutionErrors($execution_errors): void
    {
        $this->execution_errors = $execution_errors;
    }

    public function shouldEncrypt()
    {
        if (\is_array($this->options))
        {
            if (\array_key_exists('encrypted', $this->options))
                return $this->options['encrypted'] == true;
        }

        return false;
    }

    public function shouldExecuteNow()
    {
        return true;
    }

}