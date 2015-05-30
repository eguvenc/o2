
## Maintenance Katmanı

> Maintenance eklentisi uygulamanıza ait domain adreslerini bakıma alma özelliği sunar. 

<a name="maintenance-configuration"></a>

### Konfigürasyon

Eğer tanımlı değilse <kbd>app/config/$env/domain.php</kbd> dosyası içerisinden uygulamanıza ait domainleri ve bu domainlere ait regex ( düzenli ) ifadeleri belirleyin.

```php

return array(

    'root' => [
        'maintenance' => 'up',
        'regex' => null,
    ],
    'mydomain.com' => [
        'maintenance' => 'up',
        'regex' => '^mydomain.com$',
    ],
    'sub.domain.com' => [
        'maintenance' => 'up',
        'regex' => '^sub.domain.com$',
    ],
);

/* End of file */
/* Location: .app/config/env/local/domain.php */
```

Dosya içerisindeki <b>"maintenance"</b> anahtarları domain adresinin bakıma alınıp alınmadığını kontrol eder, <b>"regex"</b> anahtarı ise geçerli route adresleriyle eşleşme yapılabilmesine olanak sağlar. Domain adresinize uygun düzenli ifadeyi regex kısmına girin.

Domain adresinizi route yapısına tutturmak <kbd>app/routes.php</kbd> dosyası içerisinde domain grubunuza ait <b>domain</b> ve <b>middleware</b> anahtarlarını aşağıdaki gibi güncelleyin.

```php
$c['router']->group(
    [
        'name' => 'GenericUsers',
        'domain' => $c['config']['domain']['mydomain.com'], 
        'middleware' => array('Maintenance')
    ],
    function () {

        $this->defaultPage('welcome');
        $this->attach('(.*)');
    }
);
```

>**Not:** Dosyadaki ilk anahtar olan **root** anahtarını değiştirmemeniz gerekir bu anahtar kök anahtardır ve uygulamanızdaki tüm domain adreslerini kapatıp açmak için kullanılır.

<a name="maintenance-add"></a>

### Kurulum

```php
php task middleware add maintenance
```

<a name="maintenance-remove"></a>

### Kaldırma

```php
php task middleware remove maintenance
```

Eğer app/routes.php içinde bu katmanı kullandıysanız middleware dizileri içinden silin.

<a name="maintenance-run"></a>

### Çalıştırma

Uygulamanızı bakıma almak için aşağıdaki komutu çalıştırın.

```php
php task domain down root
```

Uygulamanızı bakımdan çıkarmak için aşağıdaki komutu çalıştırın.

```php
php task domain up root
```