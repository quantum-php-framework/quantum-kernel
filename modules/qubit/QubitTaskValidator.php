<?php

namespace Quantum\Qubit;

class QubitTaskValidator
{
    public function __construct(QubitTask $task)
    {
        $this->task = $task;

        $config = Qubit::getConfig();
        $this->keys = $config->keys;
    }

    public function isValid()
    {
        if (in_array($this->task->getKey(), $this->keys))
            return true;

        return false;
    }

}