
## Container Class

------

A <b>DIC</b> or service container is responsible for creating and storing services. It can recursively create dependencies of the requested services and inject them.

If you are new to service containers or Dependency Injection, it would be a good idea to read up on the concept. If you are new to Pimple, reading up on it is going to be extremely important. <a href="http://pimple.sensiolabs.org/" target="_blank">Pimple's documentation</a> is pretty sparse but dense.

**Note:** <kbd>$c</kbd> variable is declared by the system as default. ( At top of Obullo/Core.php ).

```php
<?php
$c->load('service/name');
$this->name->method();
```

Above the example load your class from your <b>app/classes/Service</b> folder.

### Creating Components

------

Some services need define as component which are used widely in application.

To create your components open your <b>components.php</b> that is located in your root then register your classes to container.

```php
<?php
/*
|--------------------------------------------------------------------------
| Session
|--------------------------------------------------------------------------
*/
$c['session'] = function () use ($c) {
    return new Obullo\Session\Session($c, $c->load('config')['session']);
};

/* End of file components.php */
/* Location: .components.php */
```

Then your component will be available in controller when you load it.

```php
<?php
$c->load('session');
$this->session->method();
```

You can pass first parameter as container to reusability of "$c" ( container object ).


### Creating Services

Services similar like components the main difference is you just need to create a wrapper class for your closure. However, when you retrieve the service, the closure is executed. This allows for <b>lazy</b> service creation:

### Shared type services.

This will create the service on first invocation, and then return the <b>existing instance</b> on any subsequent access.

Example mailer service.

```php
<?php

namespace Service;

use Obullo\Mail\Send\Protocol\Smtp;

/**
 * Mailer Service
 *
 * @category  Service
 * @package   Mail
 * @author    Obullo Framework <obulloframework@gmail.com>
 * @copyright 2009-2014 Obullo
 * @license   http://www.gnu.org/licenses/gpl-3.0.html GPL Licence
 * @link      http://obullo.com/docs/services
 */
Class Mailer implements ServiceInterface
{
    /**
     * Registry
     *
     * @param object $c container
     * 
     * @return void
     */
    public function register($c)
    {
        $c['mailer'] = function () use ($c) {
            return new Smtp($c, $c->load('config')['mail']);
        };
    }
}

// END Mailer class

/* End of file Mailer.php */
/* Location: .classes/Service/Mailer.php */
```


### Loading Services

This function calls your services from your <b>app/classes/Service</b> folder.

```php
<?php
$c->load('service/name');
```

We reach them using <b>$c->load()</b> method.

```php
<?php
$app = new Controller(
    function ($c) {
        $c->load('service/provider/database as db');   // creates db connection using db component.
    }
);
$app->func(
    'index',
    function () {
        $this->db->query('....');
    }
);
```

Below the example load the session component.

```php
<?php
$c->load('sess');
$this->sess->set('test', 12345);
```

### Mailer Service Example

We want to build a Mailer service and we have Mailer Class in our <kbd>app/classes</kbd> folder.

Now <b>$this->mailer</b> object available in container then you can use it like below.

```php
<?php
$app = new Controller(
    function ($c) {
        $c->load('service/mailer');
    }
);
$app->func(
    'index',
    function () {
    	$this->mailer->to('me@me.com');
    	$this->mailer->subject('test');
    	$this->mailer->message('Hello World !');
    	$this->mailer->send();
    }
);

/* End of file hello_world.php */
/* Location: .public/tutorials/controller/hello_world.php */
```

### Extending to Services

Below the example override default sender.

```php
<?php
$app = new Controller(
    function ($c) {
        $c->extend(
            'mailer',
            function($mailer) {
                $mailer->from('Web Site Mail Service <admin@example.com>');
                return $mailer;
            }
        );
    }
);
$app->func(
    'index',
    function () {
        $this->mailer->to('me@me.com');
        $this->mailer->subject('test');
        $this->mailer->message('Hello World !');
        $this->mailer->send();
    }
);
```

## Class Loader

Container also assist you to load your application classes.

<table>
    <thead>
        <tr>
            <th>Command</th>
            <th>Description</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td>new</td>
            <td>Creates new instance of object.</td>
        </tr>
        <tr>
            <td>return</td>
            <td>Returns to instance of your object without store it in your controller.</td>
        </tr>
        <tr>
            <td>as</td>
            <td>Stores your class into your contoller using a different name.</td>
        </tr>
    </tbody>
</table>

#### "New" ( Singleton ) Example

Sometimes we need to use same instance of object and sometimes same. Let's show an example by creating a test class in your <b>app/classes</b> path.

```php
<?php
$c->load('view');       // use view component instance if component not has an instance creates new one.
$c->load('new view');   // creates new instance of view component
$c->load('view');       // uses old instance of view component
$c->load('new service/cache');  // creates new service cache instance
$c->load('service/cache');      // uses old service cache instance
$c->load('new service/provider/cache'); // creates new cache providers instance
$c->load('service/provider/cache'); // uses old cache providers instance
```

#### "Return" Example

Return command returns to instance of class and class name does not stored into controller instance.

```php
<?php
$app = new Controller(
    function ($c) {
        $db = $c->load('return service/provider/database');
        $db->query('...');
        if ( ! isset($this->db)) {
            echo 'Database object not stored into $this->db variable !';
        }
    }
);
```

#### "As" Example

Sometimes we need to store a class using an alias. Forexample you may want store crud object as "db".

```php
<?php
$app = new Controller(
    function ($c) {
        $c->load('crud as db');
        $this->db->where('user_id', 5);
        $results = $this->db->resultArray();

        print_r($results);
    }
);
```

### "Alias" Example

Sometimes we deal with long names of some objects in the application so you may want define aliases for them like below.

```php
$c['permissions'] = $c->alias(
    'perms', // Add your alias as first parameter
    function () use ($c) {
        return new Permissions(
            $c,
            array(
                $c->load('config')->load('rbac')['permissions']
            )
        );
    }
);
```

After that loading permission service object will be available in <b>$this->perms</b> variable without using "as" command.

```php
$app = new Controller(
    function ($c) {
        $c->load('service/rbac/permissions');
        var_dump($this->perms);
    }
);
```


### Removing A Service

```php
<?php
unset($c['cache']);  // remove service
var_dump($c->exists('cache'));  // false

$c['cache'] = function () {   // re assign your service
    return new stdClass;
};
```

### Protecting Your Parameters

Because Container sees anonymous functions as service definitions, you need to wrap anonymous functions with the protect() method to store them as parameter or method:

```php
<?php
$c['random'] = $c->protect(
    function () { 
        return rand(); 
    }
);
echo $c['random']();  // gives 1822553960 
```

### Function Reference

------

#### $c->load('servicename');

Returns to service instance if service defined otherwise it creates new instance using Obullo classes.

#### $c->load('name/space/class');

In syntax the first word means the folder name, the second word is the class name.

#### $c->load('name/space/class as alias');

Stores a service into controller instance with your alias.

#### $c->load('new name/space/class', array $params);

In syntax the first parameters creates new instance of object and others reserved for your class construct parameters.

#### $c->load('return name/space/class');

Returns to instance of class and class name does not stored into controller instance.

#### $c->alias(string $alias, object $callable);

Stores your service into container with an alias.

#### $c->exists(string $classname);

Returns true if service exists otherwise false.

#### $c->extend(string $classname, closure $callable);

Extends your class and override methods or variables using current instance of the object.

Get same instance of provider before you created.

#### $c->raw(string $classname);

Returns closure data of the class.

#### $c->keys();

Returns to all stored keys ( class names ) in the container.