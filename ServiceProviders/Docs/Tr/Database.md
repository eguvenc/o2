
## Database Service Provider

-----

```php
$this->db = $this->c['service provider database']->get(['connection' => 'default']);
$this->db->method();
```

### Configuration

You can add new connection in this connections configuration.

```php
'connections' => array(

    'default' => array(
        'dsn'      => 'mysql:host=localhost;port=;dbname=demo_blog',
        'username' => $c['env']['MYSQL_USERNAME.root'],
        'password' => $c['env']['MYSQL_PASSWORD.NULL'],
        'options'  => [
            PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES 'UTF8'",
            PDO::MYSQL_ATTR_USE_BUFFERED_QUERY => true
        ]
    ),
    'failed' => array(
        'dsn'      => 'mysql:host=localhost;port=;dbname=failed',
        'username' => $c['env']['MYSQL_USERNAME.root'],
        'password' => $c['env']['MYSQL_PASSWORD.NULL'],
        'options'  => [
            PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES 'UTF8'",
            PDO::MYSQL_ATTR_USE_BUFFERED_QUERY => true
        ]
    ),
)
```

You must send the connection name to the chosen database connection.

```php
$this->c['service provider database']->get(['connection' => 'default']);
```
Also when you use same parameters, database provider service returns same object.

```php
$db1 = $this->c['service provider database']->get(['connection' => 'default']); // Creates a new object ($db1)
$db2 = $this->c['service provider database']->get(['connection' => 'default']); // Returns same object ($db1)
$db3 = $this->c['service provider database']->get(['connection' => 'test']);	 // Creates a new object
```
<blockquote>When you change parameters, database service provider will automatically create a new object..</blockquote>

### Manuel Configuration

You can send manually your own configuration. The database service provider creates a new object.

```php
$db = $this->c['service provider database']->factory(
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

#### Create a DSN

For MySQL

```php
$dsn = 'mysql:host=localhost;port=;dbname=test';
```

For PostgreSQL

```php
$dsn = 'pgsql:host=127.0.0.1;port=5432;dbname=anydb';
```