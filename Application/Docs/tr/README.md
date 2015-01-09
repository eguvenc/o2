
## Uygulama Sınıfı ( Application )

------

Uygulama sınıfı, uygulamanın yüklenmesinden önce O2 çekirdek dosyası ( o2/obullo/core.php ) içerisinden konteyner (ioc) içine komponent olarak tanımlanır. Uygulama ortam sabiti ( environment constant ) olmadan çalışamaz ve bu nedenle ortam çözümlemesi çekirdek yükleme seviyesinde <b>app/environments.php</b> dosyası okunarak <kbd>$c['app']->detectEnvironment();</kbd> metodu ile çözümlenir ve ortam sabitine dönüştürülür.
Ortam değişkeninin ortam sabitine atanmasının nedeni <b>$c['app']->getEnv()</b> metodunu uygulamanın her yerinde kullanmak yerine ortam değişkenine <b>ENV</b> sabiti ile daha rahat ulaşmaktır.

Aşağıda <kbd>o2/obullo/core.php</kbd> dosyasının ilgili içeriği bize uygulama sınıfının konteyner (ioc) içerisine nasıl tanımlandığını ve ortam değişkeninin uygulamanın yüklenme seviyesinde nasıl belirlendiğini gösteriyor.

```php
<?php
$c['app'] = function () use ($c) {
    return new Obullo\Application\Application($c);
};
/*
|--------------------------------------------------------------------------
| Detect current environment
|--------------------------------------------------------------------------
*/
$c['app']->detectEnvironment();
/*
|--------------------------------------------------------------------------
| Build environment constants
|--------------------------------------------------------------------------
*/
define('ENV', $c['app']->getEnv());

/* Location: .Obullo/Obullo/Core.php */
```

Uygulama sınıfını sabit tanımlamalar ( constants ), sınıf yükleyici ve konfigürasyon dosyasının yüklemesinden hemen sonraki aşamada tanımlı olarak gelir. Bunu daha iyi anlayabilmek için <b>kök dizindeki</b> dosyalara bir göz atalım.

#### constants dosyası

```php
<?php
define('OBULLO_CORE',  OBULLO .'Obullo'. DS .'Core.php');
define('OBULLO_CONTAINER',  OBULLO .'Container'. DS .'Container.php');
define('OBULLO_AUTOLOADER', OBULLO .'Obullo'. DS .'Autoloader.php');

/* Location: .constants */
```

#### index.php dosyası

```php
<?php

require OBULLO_CONTAINER;
require OBULLO_AUTOLOADER;
require OBULLO_CORE;        // İşte tam bu dosyanın içerisinde

/* Location: .index.php */
```

### $c['app']->detectEnvironment();

Uygulamanızın hangi ortamda çalıştığını belirleyen metottur. Ortam değişkeni <b>app/environments.php</b> dosyasına tanımlayacağınız sunucu isimlerinin ( hostname ) geçerli sunucu ismi ile karşılaştırması sonucu ile elde edilir.

>**Not:** Local ortamda çalışırken her geliştiricinin kendine ait bilgisayar ismini bu dosyaya yazması gereklidir, production veya test gibi ortamlarda çalıştığınızda sunucu isimlerini bu konfigürasyon dosyasına tanımlamanız yeterli olacaktır. 

Konfigürasyon yapılmadığında yada sunucu isimleri geçerli sunucu ismi ile eşleşmediğinde uygulama size aşağıdaki gibi bir hata dönecektir.

```
We could not detect your application environment, please correct your app/environments.php hostname array.
```


### $c['app']->getEnv();

Geçerli ortam değişkenine döner.

```php
<?php
echo $c['app']->getEnv();  // Çıktı  local
```

### $c['app']->getEnvironments();

Ortam değişkenleri konfigürasyon dosyasında tanımlı olan  ( <b>app/environments.php</b> ) ortam adlarına bir dizi içerisinde döner.

```php
<?php
print_r($c['app']->getEnvironments());

/* Çıktı
Array
(
    [0] => local
    [1] => test
    [2] => production
)
*/   
```

### $c['app']->getEnvArray();

Ortam değişkenleri konfigürasyon ( <b>app/environments.php</b> ) dosyasının içerisindeki tanımlı tüm diziye döner.

```php
<?php
print_r($c['app']->getEnvArray());

/* Çıktı
Array ( 
    [env] => Array ( 
            [local] => Array ( 
                [server] => Array ( 
                    [hostname] => Array ( 
                        [0] => my-desktop 
                        [1] => someone.computer 
                        [2] => anotherone.computer 
                        [3] => john-desktop ) ...
*/
```

### $c['app']->getEnvPath();

Geçerli ortam değişkeninin dosya yoluna geri döner.

```php
<?php
echo $c['app']->getEnvPath();  // Çıktı  /var/www/project.com/app/config/local/
```

### Mevcut Ortam Değişkenleri

<table>
    <thead>
        <tr>
            <th>Değişken</th>    
            <th>Açıklama</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td><b>local</b></td>
            <td>Yerel sunucu ortamıdır, geliştiriciler tarafından uygulama bu ortam altında geliştirilir, her bir geliştiricinin bir defalığına <b>environments.php</b> dosyası içerisine kendi bilgisayarına ait ismi (hostname) tanımlaması gereklidir.Local sunucuda kök dizine <b>.env.local.php</b> dosyası oluşturup her bir geliştiricinin kendi çalışma ortamı servislerine ait <b>password, hostname, username</b> gibi bilgileri bu dosya içerisine koyması gereklidir. Eğer bir versiyonlanma sistemi kullanıyorsanız dosyanın gözardı (ignore) edilmesini sağlayarak bu dosyanın ortak kullanılmasını önleyebilirsiniz. <b>Git</b> versiyon sistemi kullanıyorsanız ignore dosyaları nasıl oluşturacağınız hakkında bu kaynak yararlı olabilir. <a target="_blank" href="https://help.github.com/articles/ignoring-files/">https://help.github.com/articles/ignoring-files/</a></td>
        </tr>
        <tr>
            <td><b>test</b></td>
            <td>Test sunucu ortamıdır, geliştiriciler tarafından uygulama bu ortamda test edilir sonuçlar başarılı ise prodüksiyon ortamında uygulama yayına alınır, test sunucu isimlerinin bir defalığına <b>environments.php</b> dosyası içerisine tanımlaması gereklidir.Test sunucusunda kök dizine <b>.env.test.php</b> dosyası oluşturulup hassas veriler ve uygulama servislerine ait şifre bilgileri bu dosya içerisinde tutulmalıdır.</td>
        </tr>
        <tr>
            <td><b>production</b></td>
            <td>Prodüksiyon sunucu ortamıdır, geliştiriciler tarafından uygulama bu ortamda test edilir sonuçlar başarılı ise prodüksiyon ortamında uygulama yayına alınır, test sunucu isimlerinin bir defalığına <b>environments.php</b> dosyası içerisine tanımlaması gereklidir. Prodüksiyon sunucusunda kök dizine <b>.env.production.php</b> dosyası oluşturulup hassas veriler ve uygulama servislerine ait şifre bilgileri bu dosya içerisinde tutulmalıdır.</td>
        </tr>
    </tbody>
</table>


### .env.local.php





### Function Reference

------

#### $this->memory->exists(string $key)

Checks whether the <b>key</b> has been used before and checks <b>expiration</b> if it is expired, it will be deleted.

#### $this->memory->set(string $key, mixed $value, int $expiration)

Sets data to memory block with expiration time.