<?php

namespace Quantum\Qubit;

class QubitQueue
{

    public function __construct()
    {
        $this->tasks = new_vt();
        $this->in_progress_tasks = new_vt();
        $this->processed_tasks = new_vt();
    }

    public function addTask(QubitTask $task)
    {
        $this->tasks->add($task);
    }

    public function clear()
    {
        $this->tasks->clear();
    }

    public function execute()
    {
        while ($this->tasks->size() > 0)
        {
            $task = $this->tasks->pop();
            echo 'executing task:'. $task->task.PHP_EOL;

            $worker = new QubitTaskWorker($task);
            $status = $worker->perform();

            $task->setStatus($status);
            $task->setExitCode($worker->getChildExitCode());
            $task->setProcessId($worker->getForkedPid());

            $this->processed_tasks->add($task);
        }
    }
}