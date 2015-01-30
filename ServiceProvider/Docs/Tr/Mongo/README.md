
## Mongo Service Provider

------


Kullanım Örneği

```php
$this->mongo = $this->c->load('service provider mongo', ['connection' => 'default'])->selectDb('db');
```

Birkez yüklendikten sonra mongo metodlarına erişebilirsiniz.

```php
$this->mongo->find();
```

Factroy Örneği ( Config te olmayan yeni konnekşın lar üretmek )

```php
$this->mongo = $this->c->load(
	'service provider mongo', 
	[
		'server' => 'mongodb://localhost:27017', 'options' => array('connect' => true)
	]
);
```     