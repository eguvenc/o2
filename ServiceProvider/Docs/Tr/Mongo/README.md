
## Mongo Service Provider

------


Kullanım Örneği

```php
$this->mongo = $this->c['service provider mongo']->get(['connection' => 'default'])->selectDb('db');
```

Birkez yüklendikten sonra mongo metodlarına erişebilirsiniz.

```php
$this->mongo->find();
```

Factroy Örneği ( Config te olmayan yeni konnekşın lar üretmek )

```php
$this->mongo = $this->c['service provider mongo']->get(
	[
		'server' => 'mongodb://localhost:27017',
		'options' => array('connect' => true)
	]
);
```     