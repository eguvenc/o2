
## Pdo Service Provider

------


Kullanım Örneği

```php
$this->pdo = $this->c['app']->provider('pdo')->get(['connection' => 'default']);
```

Birkez yüklendikten sonra mongo metodlarına erişebilirsiniz.

```php
$this->pdo->query(" ... ");
```

Factory Örneği ( Config te olmayan yeni konnekşın lar üretmek için )

```php
$this->pdo = $this->c['app']->provider('pdo')->factory( 
    [
        'dsn'      => 'mysql:host=localhost;port=;dbname=test',
        'username' => 'root',
        'password' => '123456',
        'options' => array(
            \PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES 'UTF8'",
            \PDO::MYSQL_ATTR_USE_BUFFERED_QUERY => true
        )
    ]
);
```