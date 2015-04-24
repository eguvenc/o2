

## Cache Service Provider

------

Varolan bağlantılar


```php
$this->cache = $this->c['app']->provider('cache')->get, 
    [
        'driver' => 'redis',
        'options' => array(
        	'connection' => 'default'
        )
    ]
);
```


Kullanım Örneği

```php
$this->cache = $this->c['app']->provider('cache')->factory, 
    [
        'driver' => 'redis',
        'options' => array(
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
       )
    ]
);
```

Birkez yüklendikten sonra cache metodlarına erişebilirsiniz.

```php
$this->cache->method();
```