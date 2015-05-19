
## Composer Kurulumu

Composer PHP için geliştirilmiş bir bağımlılık ( paket ) yönetim aracıdır. Proje ihtiyaçlarınıza göre projenize dahil etmek istediğiniz birbirine bağımlı olabilen kütüphaneleri <kbd>composer.json</kbd> adlı bir dosya altında tanımlayarak onları proje kök dizininde <kbd>/vendor</kbd> klasörü içerisine bir paket olarak kurmanıza yada projeden kaldırmanıza olanak sağlar.

Composer projesi hakkında daha detaylı bilgi için <a href="http://getcomposer.org" target="_blank">http://getcomposer.org</a> adresini ziyaret edebilirsiniz.

### Linux / Unix / OSX İşletim Sistemleri İçin Kurulum

Proje kök dizininde <b>composer.json</b> dosyanızı oluşturun ve obullo yükleyicilerini tanımlayın.

Composer dosyanızda kullandığınız obullo sürümüne göre ana dosya klasörünü gösteren aşağıdaki gibi bir yükleyici tanımlamanız gerekli ayrıca <b>app/classes</b> dizinindeki model, middleware gibi kütüphanelerinizin yüklenebilmesi için de <b>""</b> ile yine aşağıdaki gibi bir <b>fallback</b> yükleyici ekleyin.

```php
{
    "autoload": {
        "psr-4": {
            "Obullo\\": "o2/",
            "": "app/classes"
        }
    }
}
```

Konsolonuza gidin ve proje kök dizininde aşağıdaki komutu çalıştırarak composer.phar dosyasını indirin.

```php
curl -sS https://getcomposer.org/installer | php
```

> **Not:** Eğer curl paketi bilgisayarınızda yüklü değilse bu işlemi yapmadan önce curl paketini yükleyin.

Bu yüklemeden sonra composer paketlerini php komutu ile çalıştırabilirsiniz.

```php
php composer install
```

Eğer php önekini kullanmadan composer komutlarına evrensel olarak çalıştırmak istiyorsanız aşağıdaki gibi indirilen <kbd>composer.phar</kbd> dosyasını <kbd>/usr/local/bin/composer</kbd> dizini altına kopyalamanız gerekiyor.


```php
mv composer.phar /usr/local/bin/composer
```

Şimdi composer komutlarını aşağıdaki gibi çalıştırabilirsiniz.

```php
composer update
```

Eğer henüz composer paketleri kurulmadıysa aşağıdaki gibi yükleme işlemini başlatın.

```php
php composer install
```

### Windows İşletim Sistemleri İçin Kurulum

Composer.json konfigürasyonu Linux / Unix / OSX İşletim Sistemlerinde olduğu gibidir, composer paketinin Windows kurulumu için ise <a href="https://getcomposer.org/doc/00-intro.md#installation-windows">bu linkten</a> faydalanabilirsiniz.

### Konfigürasyon

Composer ile çalışmaya başlayabilmek için aşağıdaki 4 kolay adımı uygulama ana dizininde gerçekleştirmeniz gerekiyor.

#### index.php 

Proje ana dizininde index.php dosyası içerisinden <kbd>Obullo\Application\Autoloader::register();</kbd> satırını silin ve yerine <kbd>require 'vendor/autoload.php';</kbd> satırını ekleyin.

```php
/*
|--------------------------------------------------------------------------
| Autoloader
|--------------------------------------------------------------------------
*/
require 'vendor/autoload.php';

// Obullo\Application\Autoloader::register();
/*
|--------------------------------------------------------------------------
| Initialize
|--------------------------------------------------------------------------
*/
$c['app']->run();
```

Aynı işlemi aşağıdaki gibi konsol arayüzü içinde yapmanız gerekiyor.

#### cli.php 

```php
/*
|--------------------------------------------------------------------------
| Autoloader
|--------------------------------------------------------------------------
*/
require 'vendor/autoload.php';

// Obullo\Application\Autoloader::register();
/*
|--------------------------------------------------------------------------
| Initialize
|--------------------------------------------------------------------------
*/
$c['app']->run();
```

#### Obullo Yükleyicilerini Tanımlayın

Proje kök dizininde bir <b>composer.json</b> dosyanızı oluşturun ve obullo yükleyicilerini tanımlayın. Eğer bu işlemi yukarıda yaptıysanız dikkate almayın.

```php
{
    "autoload": {
        "psr-4": {
            "Obullo\\": "o2/",
            "": "app/classes"
        }
    }
}
```

#### Autoload Önbelleğini Tazeleyin

Son olarak yeni eklediğimiz yükleyicilerin çalışabilmesi için composer dan autoload dosyalarını aşağıdaki gibi yeniden oluşturmasını istememiz gerekiyor. Konsolonuza giderek aşağıdaki komutu yazın.

```php
composer dump-autoload
```

Eğer bu işlemleri yaparken composer kurulumu yapılmadıysa bu işlemi yapmanıza gerek yok <kbd>composer dump-autoload</kbd> komutu yerine <kbd>composer install</kbd> komutunu çalıştırmanız yeterli olacaktır.

Şimdi uygulamanız composer ile çalışmaya hazır.