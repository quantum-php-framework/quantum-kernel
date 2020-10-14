<?php

namespace Quantum\Events;

use Quantum\Singleton;

/**
 * Class EventRegisterException
 * @package Quantum\Events
 */
class EventRegisterException extends  \Exception {};

/**
 * Class EventDispatchException
 * @package Quantum\Events
 */
class EventDispatchException extends  \Exception {};


/**
 * Class EventsManager
 * @package Quantum\Events
 */
class EventsManager extends Singleton
{

    /**
     * EventsManager constructor.
     */
    function __construct()
    {
        $this->events = new_vt();
    }

    /**
     * @param $event_key
     * @param $listener
     * @throws EventRegisterException
     */
    public function addSingleCallObserver($event_key, $listener)
    {
        $this->addObserver($event_key, $listener, true);
    }

    /**
     * @param $event_key
     * @param $listener
     * @param bool $callOnlyOnce
     * @param bool $shouldPassEvent
     * @param bool $shouldPassData
     * @throws EventRegisterException
     */
    public function addObserver($event_key, $listener, $priority = 100, $callOnlyOnce = false)
    {
        if (!is_string($event_key))
            throw new EventRegisterException("Event key must be a string");

        if (!is_callable($listener) && get_class($listener) != Observer::class)
            throw new EventRegisterException("Object to register must be a callable or an Observer ".gettype($listener). " given");

        $event = $this->getEvent($event_key);
        $event->add($listener, $priority, $callOnlyOnce);
    }

    /**
     * @param $event_key
     * @param $controllerName
     * @param $controllerMethod
     * @throws EventRegisterException
     */
    public function addControllerObserver($event_key, $controllerName, $controllerMethod, $callOnlyOnce = false)
    {
        if (!method_exists($controllerName, $controllerMethod))
            throw new EventRegisterException("$controllerName::$controllerMethod not found");

        $this->addObserver($event_key, array(\Quantum\ControllerFactory::create($controllerName), $controllerMethod), $callOnlyOnce);
    }


    /**
     * @param $event_key
     * @param null $data
     * @param false $failedIfNotFound
     * @return mixed
     * @throws EventDispatchException
     */
    public function dispatch($event_key, $data = null, $failedIfNotFound = false)
    {
        $event = $this->events->get($event_key, null);

        if (is_null($event))
        {
            if ($failedIfNotFound)
                throw new EventDispatchException("Event not found");
        }
        else
        {
            qm_profiler_start("EventsManager::dispatch::".$event->getName());
            return $event->notifyListeners($data);
            qm_profiler_stop("EventsManager::dispatch::".$event->getName());
        }

    }


    /**
     * @param $event_key
     * @return bool|mixed|Event
     */
    public function getEvent($event_key)
    {
        if ($this->events->has($event_key)) {
            return $this->events->get($event_key);
        }

        $event = Event::create($event_key);
        $this->events->set($event_key, $event);

        return $event;
    }

    /**
     * @param $event_key
     * @param $listener
     */
    public function detach($event_key, $listener)
    {
        $this->getEvent($event_key)->detach($listener);
    }


    /**
     * @param $event_key
     * @return bool
     */
    public function hasEvent($event_key)
    {
        return $this->events->has($event_key);
    }




}