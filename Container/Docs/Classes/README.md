
## Creating Classes

------

A <b>DIC</b> or service container is responsible for creating and storing services. It can recursively create dependencies of the requested services and inject them.

If you are new to service containers or Dependency Injection, it would be a good idea to read up on the concept. If you are new to Pimple, reading up on it is going to be extremely important. <a href="http://pimple.sensiolabs.org/" target="_blank">Pimple's documentation</a> is pretty sparse but dense.

**Note:** <kbd>$c</kbd> variable is declared by the system as default. ( At top of Obullo/Core.php ).


#### Loading Obullo Classes

Below the example shows a not defined service. Using namespaces framework do auto bind and load it as a service.

#### Namespaces

```php
<?php
$c->load('tree/db'); // load Obullo/Tree/Db.php class
echo $this->treeDb->addTree(string $text);
```

If folder name and class name is same you can call it directly.

```php
<?php
$c->load('html');   // load  Obullo/Html/Html.php
echo $this->htm->css('test.css');
```

In syntax the first word means the folder name, the second word is the class name. Above the example load <b>Obullo/Tree/Db</b> class from Obullo folder.

Using <b>new</b> keyword you can create new instance of the service.


#### Core Classes

Core classes are already loaded in framework : Config, Uri, Router, Logger.

You don't need to load again in Controller and you can use them like below.

```php
<?php
$app = new Controller(
    function ($c) {
        $this->uri->getUriString();
        $this->config->load('filename');
        $this->router->fetchDirectory();
        $this->logger->debug('Hello World !');
    }
);
```

#### New Keyword

```php
<?php
$c->load('tree/db');       // uses old instance of Obullo/Tree/Db class.
$c->load('new tree/db');   // creates new instance of Obullo/Tree/Db class.
$c->load('tree/db');       // uses old instance of Obullo/Tree/Db class.
```

#### Return Keyword

Returns class instance otherwise it stores the instance into controller.

```php
<?php
$agent = $c->load('return user/agent');
$agent->getReferer();
```

#### As Keyword

Stores object instance into contoller object using your alias.

```php
<?php
$c->load('new user/uid as uid');

echo $this->uid->addHostname()->addIp()->addMacAddress()->generateString().'<br>';  // gives  2130706433-bc:ae:c5:39:10:44-obullo-desktop-4213360135
```

#### Loading Your Classes ( User Classes )

Put your classes into <b>app/classes</b> folder then you can call them using native way.

```php
<?php
$class = new MyNamespace\MyClass;
$class->method();
```