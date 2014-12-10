
## Event Class

------

The Event class provides a simple observer implementation, allowing you to subscribe and listen for events in your application.

### Initializing the Event Class

------

```php
<?php
$c->load('event');
$this->event->method();
```


## Getting Started

Programlamada event lar belirli bir zaman dilimi içerisinde bir anda gerçekleşen olaylardır. Event yapısı uygulama içerisindeki olayların gerçekleşeği an için tetikleyici fonksiyonlar ve bu fonksiyonlara bağlı çalışacak programları çalıştırmamızı sağlar. Bir örnek vermek gerekirse mesela uygulamamız içerisiden bir login modülü olsun. Event fire methodu ile login nesnemiz içerisinde bir an belirleriz daha sonra listen komutu ile de bu an gerçekleştiğinde yapılacak işleri tanımlayabiliriz. Ne kadar çok listener yada subscriber ımız olursa olsun belirlediğimiz olay gerçekleştiğinde listener lar önemlilik derecelerine göre parse edilip çalıştırılırlar.

Listener lar anonymous bir fonksiyon olabilecekleri gibi subscribe metodu ile abone edilmiş birer sınıf ta olabilirler.


Following the example shows basic usage of the events.


### Subscribing To An Event


```php
<?php
$this->event->listen(
    'user.login',
    function ($db, $user_id) {
        $data['time'] = new DateTime;
        $this->db->update('users', $data, $where = array('user_id' => $user_id));
    }
);
```

### Firing An Event


```php
<?php
$this->event->fire('user.login', array($db, $user_id));
```

### Subscribing To Events With Priority

You may also specify a priority when subscribing to events. Listeners with higher priority will be run first, while listeners that have the same priority will be run in order of subscription.


```php
<?php
$this->event->listen('user.login', 'LoginHandler', 10);
$this->event->listen('user.login', 'OtherHandler', 5);
```

### Stopping The Propagation Of An Event

Sometimes, you may wish to stop the propagation of an event to other listeners. You may do so using by returning false from your listener:

```php
<?php
$this->event->listen(
    'user.login', 
    function ($event) {
        return false;
    }
);
```


### Globally Registering Events

Eventler her yerde register edilebilecekleri gibi sınıflar , servisler ya da servis sağlayıcıları içerisinde <b>listen</b> yada <b>subsrcibe</b> komutları ile global olarak dinlenebilirler.

Uygulamaya ait global event lar <b>app/events.php</b> içerisinde tanımlanmştır.


```php
<?php
/*
|--------------------------------------------------------------------------
| Events
|--------------------------------------------------------------------------
| This file specifies the your application global events.
*/
/*
|--------------------------------------------------------------------------
| Request - Response Handler
|--------------------------------------------------------------------------
*/

$c['event']->subscribe(new Event\UserRequestHandler);


/* End of file events.php */
/* Location: .events.php */
```


### Using Classes As Listeners

In some cases, you may wish to use a class to handle an event rather than a Closure.

```php
<?php
$this->event->listen('event.name', 'Event\LoginHandler');
```

By default, the handle method on the LoginHandler class will be called:

```php
<?php 

namespace Event;

class LoginHandler {

    public function handle($data)
    {
        //
    }

}
```


### Specifying Which Method To Subscribe

If you do not wish to use the default handle method, you may specify the method that should be subscribed:


```php
<?php
$this->event->listen('user.login', 'Event\LoginHandler.onLogin');
```

## Event Subscribers


### Defining An Event Subscriber

Event subscribers are classes that may subscribe to multiple events from within the class itself. Subscribers should define a subscribe method, which will be passed an event dispatcher instance:

```php
<?php

namespace Event;

/**
 * User event handler
 * 
 * @category  Event
 * @package   UserEventHandler
 * @author    Obullo Framework <obulloframework@gmail.com>
 * @copyright 2009-2014 Obullo
 * @license   http://www.gnu.org/licenses/gpl-3.0.html GPL Licence
 * @link      http://obullo.com/docs/event
 */
Class UserEventHandler
{
    /**
     * Container
     * 
     * @var object
     */
    protected $c;

    /**
     * Constructor
     *
     * @param object $c container
     * 
     * @return void
     */
    public function __construct($c)
    {
        $this->c = $c;
    }

    /**
     * Handler user login events
     * 
     * @return void
     */
    public function onUserLogin()
    {
        // ..
    }

    /**
     * Handle user logout events.
     *
     * @return void
     */
    public function onUserLogout()
    {
        // ..
    }

    /**
     * Register the listeners for the subscriber.
     * 
     * @param object $event event class
     * 
     * @return void
     */
    public function subscribe($event)
    {
        $event->listen('user.login', 'Event\UserEventHandler.onUserLogin');
        $event->listen('user.logout', 'Event\UserEventHandler.onUserLogout');
    }

}

// END UserEventHandler class

/* End of file UserEventHandler.php */
/* Location: .Event/UserEventHandler.php */
```

### Registering An Event Subscriber

Once the subscriber has been defined, it may be registered with the Event class.

```php
<?php
$subscriber = new Event\UserEventHandler;

$c['event']->subscribe($subscriber);
```


### Subscribe Events Using Routes

If your event not global you may want to attach it to a site route to get better performance. Forexample we want to load user event handler just at login requests.

Open your <b>routes.php</b> and add below the lines.

```php
<?php
$c['router']->route(
    'get|post', 'examples/login(.*)', null, 
    function () use ($c) {
        $c['event']->subscribe(new Event\UserEventHandler($c));
    }
);

/* End of file routes.php */
/* Location: .routes.php */
```

### Function Reference

------

#### $this->event->fire(string $event, array $payload = array(), bool $halt = false);

#### $this->event->listen(string|array $events, mixed $listener, int $priority = 0);

#### $this->event->subscribe(object $subscriber);

#### $this->event->hasListeners(string $event);

#### $this->event->getListeners(string $event);

#### $this->event->forget(string $event);