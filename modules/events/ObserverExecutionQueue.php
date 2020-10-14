<?php


namespace Quantum\Events;


class ObserverExecutionQueue
{
    public function __construct($observers)
    {
        $this->observers = $observers;
    }

    public function execute(Event $event)
    {
        $observers = $this->observers->all();

        usort($observers, function($first, $second){
            return $first->_priority > $second->_priority;
        });

        $processed_data = null;

        foreach ($observers as $observer)
        {
            $processed_data = $observer->callCallback($event);
            $event->setData($processed_data);
        }

        return $processed_data;
    }
}