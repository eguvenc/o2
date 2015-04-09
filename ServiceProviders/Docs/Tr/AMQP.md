
## AMQP Service Provider

------


Kullanım Örneği

```php
$this->AMQPConnection = $this->c['service provider AMQP']->get(['connection' => 'default']);
```

Birkez yüklendikten sonra amqp bağlantısı açılır.

```php
$channel = new AMQPChannel($this->AMQPConnection);
```

Factory Örneği ( Config te olmayan yeni konnekşın lar üretmek için )

```php
$this->AMQPConnection = $this->c['service provider AMQP']->factory( 
    [
        'host'  => 'localhost',
        'port'  => 5672,
        'username'  => 'guest',
        'password'  => 'guest',
        'vhost' => '/',
    ]
);
```


```php
<?php

class SomeClass
{
    protected $_someMember;

    public function __construct()
    {
        $this->_someMember = 1;
    }

    public static function getSomethingStatic()
    {
        return $this->_someMember * 5;
    }
}
```