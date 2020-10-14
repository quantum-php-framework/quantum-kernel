<?php

namespace Quantum\Qubit;

class QubitAppQueue
{
    public function __construct($app_uri, $app_key = 'k783eb387exb3e3e87e639873x37ex239b3x723y')
    {
        $this->app_uri = $app_uri;
        $this->app_key = $app_key;
    }

    public function addTask($worker_name, $options)
    {
        $redis = \Quantum\Cache\Backend\Redis::getInstance();

        $task = new \Quantum\Qubit\QubitTask($this->app_uri, $this->app_key, $worker_name, $options);

        if (!$redis->isAvailable())
        {
            return \Quantum\Result::fail('Redis is not available');
        }

        $redis->pushToList(\Quantum\Qubit\Qubit::PENDING_QUEUE_NAME, $task);

        return \Quantum\Result::ok();

    }

}