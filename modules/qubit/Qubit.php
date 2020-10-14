<?php

namespace Quantum\Qubit;

class Qubit
{
    const PENDING_QUEUE_NAME = 'QubitPendingTasksQueue';
    const PROCESSED_QUEUE_NAME = 'QubitProcessedTasksQueue';
    const IN_PROGRESS_QUEUE_NAME = 'QubitInProgressTasksQueue';
    const FAILED_QUEUE_NAME = 'QubitFailedTasksQueue';
    const INVALID_QUEUE_NAME = 'QubitInvalidTasksQueue';

    public function __construct($useRedis = true)
    {
        $this->initQueue($useRedis);

    }

    private function initQueue($useRedis = true)
    {
        if (!$useRedis)
        {
            $this->queue = new QubitQueue();
        }
        else
        {
            $this->queue = new QubitRedisQueue();

            if ($this->queue->isRedisAlive())
            {
                cli_echo('Redis connected');
            }
            else
            {
                cli_echo('Redis not available, using in memory queue');
                $this->queue = new QubitQueue();
            }
        }

    }


    public function process(\Psr\Http\Message\ServerRequestInterface $request)
    {
        $qubitRequest = new QubitRequest($request);

        if (!$qubitRequest->isValid())
            return $this->getResponse(false);

        switch ($qubitRequest->getCommand())
        {
            case 'clear':
                $this->queue->clear();
                break;

            case 'queue':
                return $this->getResponse($this->createTaskFromRequest($qubitRequest));
                break;
        }

        return $this->getResponse(true, $qubitRequest->getCommand());

    }


    private function createTaskFromRequest(QubitRequest $qubitRequest)
    {
        $task = new QubitTask($qubitRequest->getApp(),
            $qubitRequest->getKey(),
            $qubitRequest->getTask(),
            $qubitRequest->getData());

        if ($task->isValid())
        {
            $this->queue->addTask($task);
            return true;
        }
        else
        {
            cli_echo('Invalid Task');
            return false;
        }
    }


    public function getResponse($success, $msg = null, $data = null)
    {
        $r = new_vt();
        $r->set('success', $success);

        if ($msg)
            $r->set('msg', $msg);

        if ($data)
            $r->set('data', $data);

        return $r->toJson();
    }



    public function workqueue()
    {
        $a = microtime(true);
        $this->queue->execute();
        $d = microtime(true) - $a;
        cli_echo('Work Time:'.round(($d*1000)/100,2));

        $memory = memory_get_usage() / 1024;
        $formatted = number_format($memory, 3).'K';
        echo "Current memory usage: {$formatted}\n";
    }



    public static function getConfig()
    {
        $ipt = \Quantum\InternalPathResolver::getInstance();
        $config = (object) include $ipt->getQubitConfigFile();
        return $config;
    }

}