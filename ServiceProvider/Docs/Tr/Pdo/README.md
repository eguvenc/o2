
## Pdo Service Provider

------


Kullanım Örneği

```php
$this->pdo = $this->c->load('service provider pdo', ['connection' => 'default']);
```

Birkez yüklendikten sonra mongo metodlarına erişebilirsiniz.

```php
$this->pdo->query(" ... ");
```

Factroy Örneği ( Config te olmayan yeni konnekşın lar üretmek )

```php
$this->pdo = $this->c->load(
	'service provider pdo', 
    array(
        'dsn'      => 'mysql:host=localhost;port=;dbname=test',
        'username' => 'root',
        'password' => '123456',
        'options' => [
            \PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES 'UTF8'",
            \PDO::MYSQL_ATTR_USE_BUFFERED_QUERY => true
        ]
    )
);
```