<?php

namespace Quantum\Events;

/**
 * Class Event
 * @package Quantum\Events
 */
class Event
{
    /**
     * @var \Quantum\ValueTree
     */
    private $observers;

    /**
     * @var string
     */
    public $name;
    /**
     * @var
     */
    public $data;

    public $timestamp;


    /**
     * @param $name
     * @return Event
     */
    public static function create($name)
    {
        $e = new Event($name);

        return $e;
    }

    /**
     * Event constructor.
     * @param string $name
     */
    public function __construct($name = "")
    {
        $this->observers  = new_vt();

        if (!empty($name))
            $this->name = $name;
    }


    public function add($callback, $priority, $callOnlyOnce)
    {
        if ($this->hasCallback($callback))
            throw_exception("Callback already added to event:".$this->name);

        $this->observers->add(new Observer($callback, $priority, $callOnlyOnce));
    }


    /**
     * @param $data
     */
    public  function notifyListeners($data)
    {
        $event = self::create($this->name);
        $event->data = $data;
        $event->observers = $this->observers->count();
        $event->timestamp = microtime(true);

        $queue = new ObserverExecutionQueue($this->observers);
        return $queue->execute($event);
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return mixed
     */
    public function getData()
    {
        return $this->data;
    }

    public function setData($data)
    {
        $this->data = $data;
    }

    /**
     * @param $callback
     * @return bool
     */
    public function detach ($callback)
    {
        foreach ($this->observers->all() as $key => $observer)
        {
            if ($observer->getCallbackHash() == Observer::createCallbackHash($callback))
                $this->observers->remove($key);
        }

        return false;
    }


    /**
     * @param $callback
     * @return bool
     */
    public function hasCallback($callback)
    {
        foreach ($this->observers->all() as $observer)
        {
            if  ($observer->getCallbackHash() == Observer::createCallbackHash($callback))
                return true;
        }

        return false;
    }
}

