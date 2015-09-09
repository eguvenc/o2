
## Maintenance Katmanı

> Maintenance eklentisi uygulamanıza ait domain adresleri yada isim alanlarıyla belirlenmiş uygulamanın bütününü yada belirli kısımlarını bakıma alma özelliği sunar. 

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
php task app down root
```

Uygulamanızı bakımdan çıkarmak için aşağıdaki komutu çalıştırın.

```php
php task app up root
```

<a name="maintenance-configuration"></a>

### Alan Adları ile Konfigürasyon

Eğer tanımlı değilse <kbd>config/$env/maintenance.php</kbd> dosyası içerisinden uygulamanıza domainlere ait regex ( düzenli ) ifadeleri belirleyin.

```php

return array(

    'root' => [
        'maintenance' => 'up',
    ],
    'mydomain.com' => [
        'maintenance' => 'up',
        'namespace' => null,
        'regex' => '^mydomain.com$',
    ],
    'sub.domain.com' => [
        'maintenance' => 'up',
        'namespace' => null,
        'regex' => '^sub.domain.com$',
    ],
);

/* Location: .config/local/maintenance.php */
```

Dosya içerisindeki <b>"maintenance"</b> anahtarları domain adresinin bakıma alınıp alınmadığını kontrol eder, <b>"regex"</b> anahtarı ise geçerli route adresleriyle eşleşme yapılabilmesine olanak sağlar. Domain adresinize uygun düzenli ifadeyi regex kısmına girin.

Domain adresinizi route yapısına tutturmak <kbd>app/routes.php</kbd> dosyası içerisinde domain grubunuza ait <b>domain</b> ve <b>middleware</b> anahtarlarını aşağıdaki gibi güncelleyin.

```php
$c['router']->group(
    [
        'name' => 'GenericUsers',
        'domain' => 'mydomain.com', 
        'middleware' => array('Maintenance')
    ],
    function () {

        $this->defaultPage('welcome');
        $this->attach('(.*)');
    }
);
```

>**Not:** Dosyadaki ilk anahtar olan **root** anahtarını değiştirmemeniz gerekir bu anahtar kök anahtardır ve uygulamanızdaki tüm domain adreslerini kapatıp açmak için kullanılır.

### İsim Alanları ile Konfigürasyon

Alan adları yerine isim alanları kullanmak istiyorsanız konfigürasyon dosyasında regex yerine <kbd>namespace</kbd> değerine bir isim alanı girin.

```php
return array(

    'root' => [
        'maintenance' => 'up',
    ],
    'test' => [
        'maintenance' => 'up',
        'namespace' => 'Welcome',
        'regex' => null,
    ]
);

/* Location: .config/local/maintenance.php */
```

Ve route grubunuza bu isim alanını verin.

```php
$c['router']->group(
    [
        'namespace' => 'Welcome',
        'middleware' => array('Maintenance')
    ],
    function () {

        $this->defaultPage('welcome');
        $this->attach('(.*)');
    }
);
```

Konsoldan uygulamayı bakıma alın.

```php
php task app down test
```

Sayfayı aşağıdaki gibi ziyaret ettiğinizde bakım altında sayfasını görmeniz gerekir.

```php
http://example.com/Welcome
```

> **Not:** Bakım altında sayfasını <kbd>app/templates/errors/maintenance.php</kbd> dosyasından düzenleyebilirsiniz.


### Çifte Konfigürasyon

Dilerseniz hem isim alanları hem de domain adresi ile ikili bir kontrol yapabilirsiniz. Bu durumda uygulamanın bakıma alınabilmesi için hem domain adına göre hemde isim alanına göre bir eşleşme gerekir.

```php
return array(

    'root' => [
        'maintenance' => 'up',
    ],
    'test' => [
        'maintenance' => 'up',
        'namespace' => 'Welcome',
        'regex' => '^mydomain.com$',
    ]
);

/* Location: .config/local/maintenance.php */
```

```php
$c['router']->group(
    [
        'namespace' => 'Welcome',
        'domain' => '^mydomain.com$',
        'middleware' => array('Maintenance')
    ],
    function () {

        $this->defaultPage('welcome');
        $this->attach('(.*)');
    }
);
```