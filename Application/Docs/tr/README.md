
## Uygulama Sınıfı ( Application )

Uygulama sınıfı, uygulamanın yüklenmesinden önce O2 çekirdek dosyası ( o2/obullo/core.php ) içerisinden konteyner (ioc) içine komponent olarak tanımlanır. Uygulama ortam sabiti ( environment constant ) olmadan çalışamaz ve bu nedenle ortam çözümlemesi çekirdek yükleme seviyesinde <b>app/environments.php</b> dosyası okunarak <kbd>$c['app']->detectEnvironment();</kbd> metodu ile çözümlenir ve ortam sabitine dönüştürülür.
Ortam değişkeninin ortam sabitine atanmasının nedeni <kbd>$c['app']->getEnv()</kbd> metodunu uygulamanın her yerinde kullanmak yerine ortam değişkenine <b>ENV</b> sabiti ile daha rahat ulaşabilmektir.

Aşağıda <kbd>o2/obullo/core.php</kbd> dosyasının ilgili içeriği bize uygulama sınıfının konteyner (ioc) içerisine nasıl tanımlandığını ve ortam değişkeninin uygulamanın yüklenme seviyesinde nasıl belirlendiğini gösteriyor.

```php
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
define('OBULLO_CORE',  OBULLO .'Obullo'. DS .'Core.php');
define('OBULLO_CONTAINER',  OBULLO .'Container'. DS .'Container.php');
define('OBULLO_AUTOLOADER', OBULLO .'Obullo'. DS .'Autoloader.php');

/* Location: .constants */
```

#### index.php dosyası

```php
require OBULLO_CONTAINER;
require OBULLO_AUTOLOADER;
require OBULLO_CORE;        // İşte tam bu dosyanın içerisinde

/* Location: .index.php */
```

### $c['app']->detectEnvironment();

Uygulamanızın hangi ortamda çalıştığını belirleyen metottur. Ortam değişkeni <b>app/environments.php</b> dosyasına tanımlayacağınız sunucu isimlerinin ( <b>hostname</b> ) geçerli sunucu ismi ile karşılaştırması sonucu ile elde edilir. Aşağıda <b>app/environments.php</b> dosyasının bir örneğini inceleyebilirsiniz.

```php
return array(

    'local' => array (
        'john-desktop',     // hostname
        'localhost.ubuntu', // hostname
    ),

    'test' => array (
        'localhost.test',
    ),

    'production' => array (
        'localhost.production',
    ),
);

/* End of file environments.php */
/* Location: .app/environments.php */
```

Linux benzeri işletim sistemlerinde bilgisayarınızın adını hostname komutuyla kolayca öğrenebilirsiniz.

```
root@localhost: hostname   // localhost.ubuntu
```

>**Not:** Local ortamda çalışırken her geliştiricinin kendine ait bilgisayar ismini <b>app/environments.php</b> dosyası <b>local</b> dizisi içerisine bir defalığına eklemesi gereklidir, prodüksiyon veya test gibi ortamlarda çalışmaya hazırlık için sunucu isimlerini yine bu konfigürasyon dosyasındaki prodüksiyon ve test dizileri altına tanımlamanız yeterli olacaktır. 

Konfigürasyon yapılmadığında yada sunucu isimleri geçerli sunucu ismi ile eşleşmediğinde uygulama size aşağıdaki gibi bir hata dönecektir.

```
We could not detect your application environment, please correct your app/environments.php hostname array.
```

### $c['app']->getEnv();

Geçerli ortam değişkenine geri döner.

```php
echo $c['app']->getEnv();  // Çıktı  local
```

### $c['app']->getEnvironments();

Ortam konfigürasyon dosyasında ( <b>app/environments.php</b> ) tanımlı olan ortam adlarına bir dizi içerisinde geri döner.

```php
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

Ortam konfigürasyon dosyasının ( <b>app/environments.php</b> ) içerisindeki tanımlı tüm diziye geri döner.

```php
print_r($c['app']->getEnvArray());

/* Çıktı
Array ( 
    [0] => my-desktop 
    [1] => someone.computer 
    [2] => anotherone.computer 
    [3] => john-desktop 
)
*/
```

### $c['app']->getEnvPath();

Geçerli ortam değişkeninin dosya yoluna geri döner.

```php
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
            <td>Yerel sunucu ortamıdır, geliştiriciler tarafından uygulama bu ortam altında geliştirilir, her bir geliştiricinin bir defalığına <b>environments.php</b> dosyası içerisine kendi bilgisayarına ait ismi (hostname) tanımlaması gereklidir.Local sunucuda kök dizine <b>.env.local.php</b> dosyası oluşturup her bir geliştiricinin kendi çalışma ortamı servislerine ait <b>password, hostname, username</b> gibi bilgileri bu dosya içerisine koyması gereklidir.</td>
        </tr>
        <tr>
            <td><b>test</b></td>
            <td>Test sunucu ortamıdır, geliştiriciler tarafından uygulama bu ortamda test edilir sonuçlar başarılı ise prodüksiyon ortamında uygulama yayına alınır, test sunucu isimlerinin bir defalığına <b>environments.php</b> dosyası içerisine tanımlaması gereklidir.Test sunucusunda kök dizine <b>.env.test.php</b> dosyası oluşturulup hassas veriler ve uygulama servislerine ait şifre bilgileri bu dosya içerisinde tutulmalıdır.</td>
        </tr>
        <tr>
            <td><b>production</b></td>
            <td>Prodüksiyon sunucu ortamıdır, geliştiriciler tarafından testleri geçmiş başarılı uygulama prodüksiyon ortamında yayına alınır, prodüksiyon sunucu isimlerinin bir defalığına <b>environments.php</b> dosyası içerisine tanımlaması gereklidir. Prodüksiyon sunucusunda kök dizine <b>.env.production.php</b> dosyası oluşturulup hassas veriler ve uygulama servislerine ait şifre bilgileri bu dosya içerisinde tutulmalıdır.</td>
        </tr>
    </tbody>
</table>

### .env.local.php

------

<b>.env*</b> dosyaları servis ve sınıf konfigürasyonlarında ortak kullanılan bilgiler yada şifreler gibi daha çok paylaşılması mümkün olmayan hassas bilgileri içerir. Bu dosyalar içerisindeki anahtarlara <b>$c['env']['variable']</b> fonksiyonu ile ulaşılmaktadır. Takip eden örnekte bir .env dosyasının nasıl gözüktüğü daha kolay anlaşılabilir.

```php
return array(

    'DATABASE_USERNAME' => 'root',
    'DATABASE_PASSWORD' => '123456',

    'MYSQL_USERNAME' => 'root',
    'MYSQL_PASSWORD' => '123456',

    'MONGO_HOST'     => 'localhost',
    'MONGO_USERNAME' => 'root',
    'MONGO_PASSWORD' => '123456',

    'REDIS_HOST' => '127.0.0.1',
    'REDIS_AUTH' => 'aZX0bjL',

    'MANDRILL_API_KEY' => '8923j9m',
    'MANDRILL_USERNAME' => 'obulloframework@gmail.com',

    'AMQP_HOST' => '127.0.0.1',
    'AMQP_USERNAME' => 'root',
    'AMQP_PASSWORD' => '123456',    
);

/* End of file .env.local.php */
/* Location: .env.local.php */
```

> **Not:** Eğer bir versiyonlanma sistemi kullanıyorsanız <b>.env.*</b> dosyalarının gözardı (ignore) edilmesini sağlayarak bu dosyaların ortak kullanılmasını önleyebilirsiniz. Ortak kullanım önlediğinde her geliştiricinin kendine ait bir <b>env.local.php</b> konfigürasyon dosyası olacaktır. Uygulamanızı versiyonlanmak için <b>Git</b> yazılımını kullanıyorsanız ignore dosyalarını nasıl oluşturacağınız hakkında bu kaynak size yararlı olabilir. <a target="_blank" href="https://help.github.com/articles/ignoring-files/">https://help.github.com/articles/ignoring-files/</a>


Ortam değişikliği sözkonusu olduğunda .env* dosyalarını her bir ortam için bir defalığına kurmuş olamanız gerekir. Env dosyaları için dosya varmı kontrolü yapılmaz bu nedenle eğer uygulamanızda bu dosya mevcut değilse aşağıdaki gibi <b>php warning</b> hataları alırsınız.

```php
Warning: include(/var/www/example/.env.local.php): failed to open stream: 
No such file or directory in /o2/Config/Config.php on line 79
```

Eğer <b>config.php</b> dosyasında <kbd>error > debug</kbd> değeri <b>false</b> ise boş bir sayfa görüntülenebilir bu gibi bir durumlarla karşılaşmamak için <b>local</b> ortamda <kbd>error > debug</kbd> değerini her zaman <b>true</b> yapmanız önerilir.

> **Not:** Boş sayfa hatası aldığınızda eğer framework debugger bile hatayı göremiyorsa <kbd>error > reporting</kbd> değerini true yaparak tüm hataları görebilirsiniz. Yinede hataları göremiyorsanız <b>index.php</b> dosyasının en başına 
<b>ini_set('display_errors', 1);</b> ve <b>error_reporting(1);</b> komutlarını yazın. Bu türden boş sayfa hatalarına çok nadir rastlanır ve genellikle kütüphane geliştirme ortamlarında ortaya çıkabilirler.

### $c['env']['key']; 

Env fonksiyonu <b>obullo/core.php</b> dosyasında tanımlı olarak gelir. Bu fonksiyon konfigürasyon dosyaları içerisinde kullanılırlar.Yukarıdaki örnekte gösterdiğimiz anahtarlar uygulama çalıştığında ile önce <b>$_ENV</b> değişkenine atanırlar ve konfigürasyon dosyasında kullanmış olduğumuz <b>$c['env']</b> sınıfı ile değerler konfigürasyon dosyalarındaki anahtarlara atanmış olurlar.

Fonksiyonun <b>birinci</b> parametresi <b>$_ENV</b> değişkeninin içerisinden okunmak istenen anahtardır, ikinci parametresi anahtarın varsayılan değerini tayin eder ve üçüncü parametre anahtarın zorunlu olup olmadığını belirler.

Eğer <b>ikinci</b> parametre girildiyse <b>$_ENV</b> değişkeni içerisindeki anahtar yok sayılır ve varsayılan değer geçerli olur.

Eğer <b>üçüncü</b> parametre <b>true</b> olarak girildiyse <b>$_ENV</b> değişkeni içerisinden anahtar değeri boş geldiğinde uygulama hata vererek işlem php <b>die()</b> metodu ile sonlanacaktır.

Aşağıdaki örnekte mongo veritabanına ait konfigürasyon içerisine $_ENV değerlerinin <b>$c['env']</b> sınıfı ile nasıl atandığını görüyorsunuz.

```php
return array(
    'default' => array(
        'connection'   => 'default',
        'database' => 'db',
    ),
    'connections' => array(
        
        'default' => array(
            'host' => $c['env']['MONGO_HOST.REQUIRED'],
            'username' => $c['env']['MONGO_USERNAME.root'],
            'password' => $c['env']['MONGO_PASSWORD.null'],
            'port' => '27017',
            'options'  => array('connect' => true)
            ),
    ),
);

/* End of file mongo.php */
/* Location: .app/config/local/mongo.php */
```

### Yeni Bir Ortam Değişkeni Yaratmak

Yeni bir ortam yaratmak için <b>app/environments.php</b> dosyasına ortam adını küçük harflerle girin. Aşağıdaki örnekte biz <b>myenv</b> adında bir ortam yaratttık.

#### environments.php

```php
return array(
    'local' => array ( ... ),
    'test' => array ( ... ),
    'production' => array( ... )
    'myenv' => array ( 
        'example.hostname'
        'example2.hostname'
    )
);

/* End of file environments.php */
/* Location: .app/environments.php */
```

Ayrıca yarattığınız ortam için bir konfigürasyon <b>klasörü</b> ve bir <b>config.env</b> dosyası yaratmanız gerekiyor.

```php
- app
    - config
        + local
        + production
        + test
        - myenv
            config.env
```

Yeni yarattığınız ortam klasörüne içine gerekli ise bir <b>config.php</b> dosyası ve database.php gibi diğer config dosyalarını yaratabilirsiniz. 

### Ortam Klasörü için Config Dosyalarını Yaratmak

Prodüksiyon ortamı üzerinden örnek verecek olursak bu klasöre ait config dosyaları içerisine yalnızca ortam değiştiğinde değişen anahtar değerlerini girmeniz yeterli olur. Çünkü konfigürasyon paketi geçerli ortam klasöründeki konfigürasyonlara ait değişen anahtarları <b>local</b> ortam anahtarlarıyla eşleşirse değiştirir aksi durumda olduğu gibi bırakır.

Mesala prodüksiyon ortamı içerisine aşağıdaki gibi bir <b>config.php</b> dosyası ekleseydik config.php dosyası içerisine sadece değişen anahtarları eklememiz yeterli olacaktı.

```php
- app
    - config
        + local
        - production
            config.env
            config.php
        + test
        - myenv
            config.env
```

Aşağıdaki örnekte sadece <b>error, log, url</b> ve <b>cookie</b> anahtarları içerisindeki değişen belirli anahtarlar gözüküyor. Uygulama çalıştığında bu anahtar değerleri geçerli olurken geri kalan anahtar değerleri local ortam dosyasından okunur.

#### config.php

```php
return array(
                    
    'error' => array(
        'debug' => false,
        'reporting' => false,
    ),

    'log' =>   array(
        'control' => array(
            'enabled' => true,
            'firelog'  => false,
        )
    ),

    'url' => array(
        'webhost'   => 'example.com', 
        'base'   => '/',         // Base Url 
        'assets' => '/assets/',  // Assets Url
    ),

    'cookie' => array( 
        'domain' => '.example.com'  // Set to .your-domain.com for site-wide cookies
    ),

);

/* Location: .app/config/production/config.php */
```

### Konfigürasyon ayarlarına erişim

Konfigürasyon dosyaları load metodu ile yüklendiğinde çevre ortamı ne olursa olsun ortak biri dizi içerisinde kaydedilirler ve config sınıfı ile bu diziden ilgili konfigürasyon dosyası ayarlarına ulaşılır. Lüten aşağıdaki örneğe bir göz atın.

```php
$c['config']->load('database');

echo $c['config']['database']['connections']['db']['host'];  // Çıktı localhost
```


#### Application Sınıfı Referansı

------

##### $this->c['app']->router->method();

Uygulamada kullanılan evrensel <b>router</b> nesnesine geri döner. Uygulama içerisinde bir katman isteği gönderildiğinde router nesnesi değişime uğrayarak istek gönderilen 
url değerinin yerel değişkenlerine geri döner. Böyle bir durumda bu method sizin gerçek http isteği yapılan evrensel router nesnesine ulaşmanıza imkan tanır.

##### $this->c['app']->uri->method();

Uygulamada kullanılan evrensel <b>uri</b> nesnesine geri döner. Uygulama içerisinde bir katman isteği gönderildiğinde uri nesnesi değişime uğrayarak istek gönderilen 
url değerinin yerel değişkenlerine geri döner. Böyle bir durumda bu method sizin gerçek http isteği yapılan evrensel uri nesnesine ulaşmanıza imkan tanır.

##### $this->c['app']->getEnv();

Geçerli ortam değişkenine geri döner.

##### $this->c['app']->getEnvironments();

Ortam konfigürasyon dosyasında ( app/environments.php ) tanımlı olan ortam adlarına bir dizi içerisinde geri döner.

##### $this->c['app']->getEnvArray();

Ortam konfigürasyon dosyasının ( app/environments.php ) içerisindeki tanımlı tüm diziye geri döner.

##### $this->c['app']->getEnvPath();

Geçerli ortam değişkeninin dosya yoluna geri döner.