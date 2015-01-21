
## Container Class

------

A <b>DIC</b> or service container is responsible for creating and storing services. It can recursively create dependencies of the requested services and inject them.

If you are new to service containers or Dependency Injection, it would be a good idea to read up on the concept. If you are new to Pimple, reading up on it is going to be extremely important. <a href="http://pimple.sensiolabs.org/" target="_blank">Pimple's documentation</a> is pretty sparse but dense.

**Note:** <kbd>$c</kbd> variable is declared by the system as default. ( At top of Obullo/Core.php ).

## Creating Service Providers

Providers allow the developer to <b>reuse parts</b> of an application into another one. The usage of providers are the same as well services but the main differences are :

1. Each provider wraps a service.
2. Before loading them if you use new paramters we need to use <b>"new"</b> keyword to get <b>new instance</b> otherwise we get <b>old instance</b>.
3. Second parameter can be string or an array.
4. All providers implement ProviderInterface.

### Loading a Provider

In order to load and use a service provider you need call "load" command.


```php
<?php
$c->load('service/provider/mongo as mongo');
$this->mongo->method();
```

### Using "As" Command

As command helps to assign objects to controller.

```php
<?php
$this->c->load('service/provider/db as stats', array('db' => 'stats'));
$this->stats->tablename->insert(array $data);
```

### Using "Return" Command

Return command stops to assigning object value into controller then returns to instance of it.

Below the example we select the mongo collection after that we assign mongo object into controller.

```php
<?php
$mongo = $this->c->load('return service/provider/mongo');
$mongo->test->insert();
```

Your provider folder looks like below.

```php
+ app
    - classes
        Service
            Provider
                Cache.php
                Mongo.php
                ProviderInterface.php
```

Example provider


```php
<?php

namespace Service\Provider;

use Obullo\Mongo\Connection;

/**
 * Mongo Provider
 *
 * @category  Provider
 * @package   Mongo
 * @author    Obullo Framework <obulloframework@gmail.com>
 * @copyright 2009-2014 Obullo
 * @license   http://opensource.org/licenses/MIT MIT license
 * @link      http://obullo.com/docs/providers
 */
Class Mongo implements ProviderInterface
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
        // ...
    }
}

// END Mongo class

/* End of file Mongo.php */
/* Location: .classes/Service/Provider/Mongo.php */
```

We use <b>provider:</b> prefix to protect providers from services.

You can set parameters any time after the provider is registered. Because of providers can set default values for parameters.

### Querying Results Using Mongo Provider

```php
<?php
$app = new Controller(
    function () use ($c) {
        $this->c->load('view');
        $this->c->load('service/provider/mongo')->database = 'db';
    }
);
$app->func(
    'index',
    function () {;
        $cursor = $this->mongo->users->find();
        foreach ($cursor as $docs) {
            echo $docs['email'].'<br />';
        }
    }
);

/* End of file hello_world.php */
/* Location: .public/tutorials/controller/hello_world.php */
```

### Using "As" Command

```php
<?php
$app = new Controller(
    function () use ($c) {
        $this->c->load('view');
        $this->c->load('service/provider/mongo')->database = 'test';
    }
);
$app->func(
    'index',
    function () {
        $cursor = $this->mongo->users->find();
        foreach ($cursor as $docs) {
            echo $docs['email'].'<br />';
        }
    }
);

/* End of file hello_world.php */
/* Location: .public/tutorials/controller/hello_world.php */
```

### Using "New" Command

New command creates new object of instance otherwise container cache the old instance.

```php
<?php

$this->c->load('service/provider/db')->database = 'test';

// $this->db->query('test database query');

$this->c->load('service/provider/db as db', 'jobs'); // test db instance
$this->c->load('service/provider/db as db', 'jobs'); // test db instance
$this->c->load('service/provider/db as db', 'jobs'); // test db instance

// $this->db->query('test database query');

$this->c->load('new service/provider/db as db', 'jobs'); // jobs db instance

// $this->db->query('jobs database query');
```

**Important:** When you enter new parameters use <b>"new"</b> keyword otherwise provider will use old provider's instance.


### Config

```php
<?php
/*
|--------------------------------------------------------------------------
| NoSQL Config
|--------------------------------------------------------------------------
*/
return array(
    'mongo' => array(
        'db' => array(
            'host' => 'localhost',
            'username' => 'root',
            'password' => '12345',
            'port' => '27017',
            ),
        'yourSecondDatabaseName' => array(
            'host' => '',
            'username' => '',
            'password' => '',
            'port' => '',
        )
    // 'dbname' => array()  Another noSQL database provider
    ),
);

/* End of file mongo.php */
/* Location: ./app/config/env/local/nosql.php */
```

Sometimes we may want to <b>same instance</b> of the provider. Forexample we want to <b>reuse</b> loaded mongo provider.

```php
<?php
/**
 * $c hello_world
 * 
 * @var Controller
 */
$app = new Controller(
    function ($c) {
        $this->c->load('view');
        $this->c->load('service/provider/mongo', 'db');
    }
);
```
In your libraries you can reach same instance that used in Controller.

```php
<?php

Class Dummy {

    public function __construct($c) {
        $this->mongo = $c->load('return service/provider/mongo');  // same instance and we stop assigning object instance into controller.
    }
}
```

Now <b>$this->mongo</b> object available in our dummy class.

If you need new instance of provider you can use <b>"new"</b> word.

```php
<?php

Class Dummy {

    public function __construct($c) {
        $this->mongo = $c->load('return new service/provider/mongo');  // new instance
    }
}
```

### Default Providers

There are a few service providers that you get out of the box. All of these are located in your <kbd>app/classes/Service/Provider</kbd> folder.

<table>
    <thead>
        <tr>
            <th>Provider Name</th>
            <th>Description</th>
            <th>Parameters</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td><b>Cache</b></td>
            <td>Creates new Cache instance with your cache driver parameters.</td>
            <td>array('cache.serializer' => 'SERIALIZER_NONE')</td>
        </tr>
        <tr>
            <td><b>Database</b></td>
            <td>Creates new RBDMS database instance with your database config key.</td>
            <td>Config Key</td>
        </tr>
        <tr>
            <td><b>Mongo</b></td>
            <td>Creates new NoSQL mongo db instance with your database config key.</td>
            <td>Config Key</td>
        </tr>
        <tr>
            <td><b>Crud</b></td>
            <td>Creates new Active Record Db ( Crud ) instance with your database config key.</td>
            <td>Config Key</td>
        </tr>
    </tbody>
</table>


### Function Reference

------

#### $c->load('service/provider/name', $params = array());

Load service provider from <b>app/classes/Services/Provider</b> folder and stores it into controller instance.

#### $c->load('service/provider/name as name');

Stores the service instance into controller with provided alias.

#### $c->load('return service/provider/name');

Stops assigning object value into controller and returns service instance.