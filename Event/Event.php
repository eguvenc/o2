<?php

/**
 * Event libray
 */
class Event
{
    /**
     * The event firing stack.
     *
     * @var array
     */
    protected $firing = array();

    /**
     * Listeners
     * 
     * @var array
     */
    protected $listeners = array();

    /**
     * Create a new event dispatcher instance.
     *
     * @return void
     */
    public function __construct()
    {

    }

    /**
     * Register an event listener with the dispatcher.
     *
     * @param string|array $events
     * @param mixed        $listener
     * @param int          $priority
     * 
     * @return void
     */
    public function listen($events, $listener, $priority = 0)
    {
        foreach ((array) $events as $event) {
            $this->listeners[$event][$priority][] = (is_string($listener)) ?  $this->createClassListener($listener) : $listener;

            unset($this->sorted[$event]);
            
        }
    }

    /**
     * Determine if a given event has listeners.
     *
     * @param string $event name
     * 
     * @return bool
     */
    public function hasListeners($event)
    {
        return isset($this->listeners[$event]);
    }

    /**
     * Register an event subscriber with the dispatcher.
     *
     * @param  string  $subscriber
     * 
     * @return void
     */
    public function subscribe($subscriber)
    {
        $subscriber = $this->resolveSubscriber($subscriber);

        $subscriber->subscribe($this);
    }

    /**
     * Resolve the subscriber instance.
     *
     * @param  mixed  $subscriber
     * @return mixed
     */
    protected function resolveSubscriber($subscriber)
    {
        if (is_string($subscriber)) {
            return new $subscriber();
        }
        return $subscriber;
    }

    /**
     * Fire an event until the first non-null response is returned.
     *
     * @param  string  $event
     * @param  array   $payload
     * @return mixed
     */
    public function until($event, $payload = array())
    {
        return $this->fire($event, $payload, true);
    }

    /**
     * Fire an event and call the listeners.
     *
     * @param  string  $event
     * @param  mixed   $payload
     * @param  bool    $halt
     * @return array|null
     */
    public function fire($event, $payload = array(), $halt = false)
    {
        $responses = array();

        // If an array is not given to us as the payload, we will turn it into one so
        // we can easily use call_user_func_array on the listeners, passing in the
        // payload to each of them so that they receive each of these arguments.
        if ( ! is_array($payload)) {
            $payload = array($payload);
        }
        $this->firing[] = $event;

        $listeners = $this->getListeners($event);

        foreach ($listeners as $listener) {
            $response = call_user_func_array($listener, $payload);

            // If a response is returned from the listener and event halting is enabled
            // we will just return this response, and not call the rest of the event
            // listeners. Otherwise we will add the response on the response list.
            if ( ! is_null($response) AND $halt) {
                array_pop($this->firing);
                return $response;
            }

            // If a boolean false is returned from a listener, we will stop propagating
            // the event to any further listeners down in the chain, else we keep on
            // looping through the listeners and firing every one in our sequence.
            if ($response === false) {
                break;
            }
            $responses[] = $response;
        }

        array_pop($this->firing);

        return $halt ? null : $responses;
    }

    /**
     * Get all of the listeners for a given event name.
     *
     * @param string $event name
     * 
     * @return array
     */
    public function getListeners($event)
    {
        if ( ! isset($this->sorted[$event])) {
            $this->sortListeners($event);
        }
        return $this->sorted[$event];
    }

    /**
     * Sort the listeners for a given event by priority.
     *
     * @param string $event name
     * 
     * @return array
     */
    protected function sortListeners($event)
    {
        $this->sorted[$event] = array();

        // If listeners exist for the given event, we will sort them by the priority
        // so that we can call them in the correct order. We will cache off these
        // sorted event listeners so we do not have to re-sort on every events.
        if (isset($this->listeners[$event])) {
            krsort($this->listeners[$event]);
            $this->sorted[$event] = call_user_func_array('array_merge', $this->listeners[$event]);
        }
    }

    /**
     * Create a class based listener using the IoC container.
     *
     * @param mixed $listener
     * 
     * @return \Closure
     */
    public function createClassListener($listener)
    {
        return function() use ($listener) {
            // If the listener has an "." sign, we will assume it is being used to delimit
            // the class name from the handle method name. This allows for handlers
            // to run multiple handler methods in a single class for convenience.
            $segments = explode('.', $listener);
            $method = count($segments) == 2 ? $segments[1] : 'handle';

            // We will make a callable of the listener instance and a method that should
            // be called on that instance, then we will pass in the arguments that we
            // received in this method into this listener class instance's methods.
            $data = func_get_args();

            return call_user_func_array(array(new $segments[0], $method), $data);
        };
    }

    /**
     * Remove a set of listeners from the dispatcher.
     *
     * @param  string  $event
     * @return void
     */
    public function forget($event)
    {
        unset($this->listeners[$event]);
    }

}

/*
$event = new Event;

$event->listen(
    'user.login', 
    function () {
        echo 'Auth login succesfully works !!!';
    },
    30
);
$event->listen(
    'user.login', 
    function ($user) {
        print_r($user);
        echo 'Auth2  works !!!';
    },
    10
);

$event->fire('user.login', array(new stdClass));
*/