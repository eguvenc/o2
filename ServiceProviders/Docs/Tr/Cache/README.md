

## Cache Service Provider

------


Kullanım Örneği

```php
$this->cache = $this->c->load(
    'service provider cache', 
    [
        'driver' => 'redis',
        'serializer' => 'php'
    ]
);
```

Birkez yüklendikten sonra cache metodlarına erişebilirsiniz.

```php
$this->cache->method();
```


OPSIYONLAR

Key 			Value
serializer		php