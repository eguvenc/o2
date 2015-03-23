
## Memcached Service Provider

------

Kullanım Örneği

```php
$this->memcached = $this->c['service provider memcached']->get(['connection' => 'default']);
```

Birkez yüklendikten sonra cache metotlarına erişebilirsiniz.

```php
$this->memcached->set(" ... ");
$this->memcached->get(" ... ");
```

Factory Örneği ( Config te olmayan yeni bağlantılar üretmek için )

```php
$this->memcached = $this->c['service provider memcached']->factory( 
    [
        'host' => '127.0.0.1',
        'port' => 11211,
        'weight' => 1,
        'options' => array(
            'persistent' => false,
            'pool' => 'connection_pool',   // http://php.net/manual/en/memcached.construct.php
            'timeout' => 30,               // Seconds
            'attempt' => 100,
            'serializer' => 'php',    // php, json, igbinary
            'prefix' => null
        )
    ]
);
```
