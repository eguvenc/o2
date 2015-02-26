
## Event Class

------

The Event class provides a simple observer implementation, allowing you to subscribe and listen for events in your application.

### Initializing the Event Class

------

```php
<?php

$this->c['event'];
$this->event->method();
```


## Getting Started

Programlamada event lar belirli bir zaman dilimi içerisinde bir anda gerçekleşen olaylardır. Event yapısı uygulama içerisindeki olayların gerçekleşeği an için tetikleyici fonksiyonlar ve bu fonksiyonlara bağlı çalışacak programları çalıştırmamızı sağlar. 

<b>Bir örnek</b> vermek gerekirse mesela uygulamamız içerisiden bir login modülü olsun. 

Event <b>fire</b> methodu ile login nesnemiz içerisinde bir an belirleriz ve daha sonra listen komutu ile de bu an gerçekleştiğinde yapılacak işleri tanımlayabiliriz. Kaydedilen olay anını <b>listen</b> yada <b>subscribe</b> komutu ile fonksiyonlara atarız. Subscribe komutunu listen komutunundan ayıran en önemli özellik bu metodun dinleyicileri bir sınıf içerisinde gruplayarak kodlarınızın sürdürülebilirliğini attırmasıdır. Bu durumda subscribe komutu listen komutuna genişlemektedir. 

Son olarak olay anını ne kadar çok dinleyicimiz  ( <b>listeners / subscribers</b> ) dinlerse dinlesin olay gerçekleştiğinde dinleyicilere ( aboneler ) tanımlanan fonksiyonlar önemlilik derecelerine göre parse edilip çalıştırılırlar.

Dinleyiciler <b>anonymous</b> bir fonksiyon olabilecekleri gibi <b>subscribe</b> metodu ile abone edilmiş birer <b>sınıf</b> ta olabilirler.

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

$this->event->listen('user.login', 'Event\LoginHandler', 10);
$this->event->listen('user.login', 'Event\OtherHandler', 5);
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

$c['event']->subscribe(new Event\Redirect);


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

class Login {

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

$this->event->listen('user.login', 'Event\Login@onLogin');
```

## Event Subscribers

Subscribe komutunu listen komutunundan ayıran en önemli özellik bu metodun dinleyicileri bir sınıf içerisinde gruplayarak kodlarınızın sürdürülebilirliğini attırmasıdır. Bu durumda subscribe komutu listen komutuna genişlemektedir.

### Defining An Event Subscriber

Event subscribers are classes that may subscribe to multiple events from within the class itself. Subscribers should define a subscribe method, which will be passed an event dispatcher instance:

```php
<?php

namespace Event\Login;

/**
 * User event handler
 * 
 * @category  Event
 * @package   User
 * @author    Obullo Framework <obulloframework@gmail.com>
 * @copyright 2009-2014 Obullo
 * @license   http://opensource.org/licenses/MIT MIT license
 * @link      http://obullo.com/docs/event
 */
Class Attempt
{


}

// END User class

/* End of file User.php */
/* Location: .Event/User.php */
```

### Registering An Event Subscriber

Once the subscriber has been defined, it may be registered with the Event class.

```php
<?php

$c['event']->subscribe(new Event\User($c));
```

### Subscribing to Events in Controllers

Below the example we have login controller and we listen login attempts by subscribing to <b>Event\User</b> class.

```php
<?php

Class Welcome extends Controller
{
    /**
     * Loader
     * 
     * @return void
     */
    public function load()
    {
        $this->c['url'];
        $this->c['form'];
        $this->c['view'];
        $this->c['post'];
        $this->c['service/user'];
        $this->c['event']->subscribe(new Event\Login\Attempt($this->c)];        
    }

    /**
     * Index
     * 
     * @return void
     */
    public function index()
    {
        $this->c['view']->load(
            'welcome',
            function () {
                $this->assign('name', 'Obullo');
                $this->assign('footer', $this->template('footer', false));

                if ($this->post['dopost']) {

                    $this->c['validator'];

                    $this->validator->setRules('email', 'Email', 'required|email|trim');
                    $this->validator->setRules('password', 'Password', 'required|min(6)|trim');

                    if ($this->validator->isValid()) {

                        $result = $this->user->login->attempt(
                            array(
                                Auth\Constant::IDENTIFIER => $this->validator->value('email'), 
                                Auth\Constant::PASSWORD => $this->validator->value('password')
                            ),
                            $this->post['rememberMe']
                        );

                        if ($result->isValid()) {
                            $this->flash->success('You have authenticated successfully.');
                            $this->url->redirect('examples/login');
                        } else {
                            $this->validator->setErrors($result->getArray());
                        }
                    }

                    $this->form->setErrors($this->validator);
                }

                $this->c['view']->load(
                    'login',
                    function () {
                        $this->assign('footer', $this->template('footer'));
                    }
                );
            }
        );
    }
}

```

### Subscribing Events Using Routes

If your event realized in more controllers you may want to attach it to a site routes. Forexample we want to load user event handler just at login direcyory requests.

Open your <b>routes.php</b> and add below the lines.

```php
<?php

$c['router']->get(
    'examples/login(.*)', null, 
    function () use ($c) {
        $c['event']->subscribe(new Event\User($c));
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