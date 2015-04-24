
## Redis Service Provider

------


Kullanım Örneği

```php
$this->redis = $this->c['app']->provider('redis')->get(['connection' => 'default']);
```

Birkez yüklendikten sonra redis metodlarına erişebilirsiniz.

```php
$this->redis->set(" ... ");
$this->redis->get(" ... ");
```

Factory Örneği ( Konfigürasyon olmayan yeni bağlantılar üretmek için )

```php
$this->redis = $this->c['app']->provider('redis')->factory( 
    [
        'host' => '127.0.0.1',
        'port' => 6379,
        'options' => array(
            'auth' => '123456',    // Connection password
            'timeout' => 30,
            'persistent' => 0,
            'reconnection.attemps' => 100,     // For persistent connections
            'serializer' => 'none',
            'database' => null,
            'prefix' => null,
        )
    ]
);
```

