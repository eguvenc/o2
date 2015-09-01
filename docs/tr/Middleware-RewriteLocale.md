
## RewriteLocale Katmanı

> Bu katman uygulamaya <b>http://example.com/welcome</b> olarak gelen istekleri mevcut yerel dili ekleyerek <b>http://example.com/en/welcome</b> adresine yönlendirir.

### Kurulum

```php
php task middleware add rewriteLocale
```

### Kaldırma

```php
php task middleware remove rewriteLocale
```

Eğer route yapınızda bu katmanı kullandıysanız app/routes.php dosyasından ayrıca silin.

### Çalıştırma

Aşağıdaki örnek genel ziyaretçiler route grubu için RewriteLocale katmanını çalıştırır.

```php
$c['router']->group(
    [
        'name' => 'GenericUsers', 
        'domain' => 'mydomain.com', 
        'middleware' => array('Maintenance', 'RewriteLocale')
    ],
    function () use ($c) {

        $this->defaultPage('welcome');

        $this->get('(?:en|tr|de|nl)/(.*)', '$1');
        $this->get('(?:en|tr|de|nl)', 'welcome');

        $this->attach('.*');
    }
);
```