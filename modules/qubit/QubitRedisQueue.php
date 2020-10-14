<?php

namespace Quantum\Qubit;

use Quantum\Cache\Backend\Redis;



class QubitRedisQueue
{
    private $busy;
    private $redis;


    public function __construct()
    {
        $this->busy = false;
        $c = Qubit::getConfig();

        $this->redis = new Redis(false);
        $this->redis->init($c->redis_scheme, $c->redis_host, $c->redis_port, $c->redis_persistent, $c->redis_password);
    }

    public function isRedisAlive()
    {
        return $this->redis->isAvailable();
    }

    public function addTask(QubitTask $task, $queue_name = Qubit::PENDING_QUEUE_NAME)
    {
        if ($task->shouldEncrypt())
            $this->redis->pushToListEncrypted($queue_name, $task);
        else
            $this->redis->pushToList($queue_name, $task);

        cli_echo('Task '.$task->task.' pushed to queue: '.$queue_name);
    }

    public function clear()
    {
        $this->redis->flushList(Qubit::PENDING_QUEUE_NAME);
    }

    public function isBusy()
    {
        return $this->busy;
    }

    public function execute()
    {
        if ($this->isBusy())
        {
            cli_echo('We are busy come back next time...');
            return;
        }

        if (!$this->redis->isAvailable())
        {
            cli_echo('Redis is not available, aborting execution...');
            return;
        }

        while ($this->redis->getListLength(Qubit::PENDING_QUEUE_NAME) > 0)
        {
            $this->busy = true;

            $task = $this->redis->popFromList(Qubit::PENDING_QUEUE_NAME);

            if (!$task->isValid())
            {
                cli_echo('Invalid task:'. $task->task);

                $this->redis->pushToList(Qubit::INVALID_QUEUE_NAME, $task);
            }
            else
            {
                cli_echo('Executing task:'. $task->task);

                $this->redis->pushToList(Qubit::IN_PROGRESS_QUEUE_NAME, $task);

                $reference_task = \serialize(clone $task);

                $worker = new QubitTaskWorker($task);

                $task->setStartDate(\datestamp());
                $status = $worker->perform();
                $task->setEndDate(\datestamp());

                $task->setStatus($status);
                $task->setExitCode($worker->getChildExitCode());
                $task->setProcessId($worker->getForkedPid());

                if ($task->isCompleted())
                {
                    $this->redis->getRedis()->lrem(Qubit::IN_PROGRESS_QUEUE_NAME, 0, $reference_task);
                    $this->redis->pushToList(Qubit::PROCESSED_QUEUE_NAME, $task);
                }
                elseif ($task->hasFailed())
                {
                    $this->redis->getRedis()->lrem(Qubit::IN_PROGRESS_QUEUE_NAME, 0, $reference_task);
                    $this->redis->pushToList(Qubit::FAILED_QUEUE_NAME, $task);
                }
            }
        }

        $this->busy = false;
    }
}