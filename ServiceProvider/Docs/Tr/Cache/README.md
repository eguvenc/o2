

## Cache Service Provider

------


Kullanım Örneği

```php
$this->cache = $this->c->load(
    'service provider cache', 
    [
        'driver' => 'redis'
    ]
);
```

Birkez yüklendikten sonra cache metodlarına erişebilirsiniz.

```php
$this->cache->method();
```

Serializer seçimi

```php
$this->cache->setOption(array('serializer' => 'SERIALIZER_PHP'));
```


OPSIYONLAR

Key 			Value
serializer		SERIALIZER_PHP