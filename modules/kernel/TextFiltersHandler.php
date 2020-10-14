<?php


namespace Quantum;

use Quantum\Events\EventsManager;

/**
 * Class TextFiltersHandler
 * @package Quantum
 */
class TextFiltersHandler extends Singleton
{

    /**
     * @var ValueTree
     */
    private $events;


    /**
     * TextFiltersHandler constructor.
     */
    protected function __construct()
    {
        parent::__construct();
        $this->events = new_vt();
    }

    /**
     * @param $name
     * @param $callback
     * @param int $priority
     * @throws Events\EventRegisterException
     */
    public function addFilter($name, $callback, $priority = 100)
    {
        if ($this->events->has($name))
        {
            $event = $this->events->get($name);
        }
        else {
            $event = new \Quantum\Events\Event($name);
            $this->events->set($name, $event);
        }

        $event->add($callback, $priority, true);
    }



    public function applyFilter($event_key, $value)
    {
        $event = $this->events->get($event_key, null);

        if (!is_event($event)) {
            return $value;
        }

        $args = func_get_args();
        array_shift($args);

        $num_args = count ($args);

        $observers = $event->getObservers();

        if (is_vt($observers) && !$observers->isEmpty())
        {
            $observers = $observers->toStdArray();

            usort($observers, function($first, $second){
                return $first->_priority > $second->_priority;
            });

            foreach ($observers as $observer)
            {
                $callable = $observer->_callback;

                $accepted_params_count = $observer->getNumCallableArguments();

                if ($accepted_params_count == 0) {
                    $args[0] = call_user_func( $callable );
                }
                elseif ($accepted_params_count >= $num_args) {
                    $args[0] = call_user_func_array($callable, $args );
                }
                else {
                    $args[0] = call_user_func_array($callable, array_slice( $args, 0, (int) $accepted_params_count ) );
                }
            }

            $event->clearObservers();
        }

        return $args[0];
    }


}