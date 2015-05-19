
## Uygulama Sınıfı ( Application )

Uygulama sınıfı, uygulamanın yüklenmesinden önce O2 çekirdek dosyası ( o2/Applicaiton/Http.php ) içerisinden konteyner (ioc) içine komponent olarak tanımlanır. Uygulama ortam değişkeni  olmadan çalışamaz ve bu nedenle ortam çözümlemesi çekirdek yükleme seviyesinde <b>app/environments.php</b> dosyası okunarak <kbd>$c['app']->detectEnvironment();</kbd> metodu ile ortam çözümlenir.

Ortam değişkenine <kbd>$c['app']->env()</kbd> metodu ile uygulamanın her yerinden ulaşılabilir.

Obullo da uygulama http ve console isteklerine göre Http ve Cli sınıfları olarak ikiye ayrılır. Http isteğinden sonraki çözümlemede controller dosyası <b>modules/</b> klasöründen çağrılırken Cli istekleri ise konsoldan <kbd>$php task command</kbd> yöntemi ile <b>modules/tasks</b> klasörüne yönlendirilir.

Aşağıda <kbd>o2/Application/Http.php</kbd> dosyasının ilgili içeriği bize uygulama sınıfının konteyner içerisine nasıl tanımlandığını ve ortam değişkeninin uygulamanın yüklenme seviyesinde nasıl belirlendiğini gösteriyor.

```php
/**
 * Container
 * 
 * @var object
 */
$c = new Container;

$c['app'] = function () {
    return new Http;
};

class Htttp extends Obullo {

    /**
     * Constructor
     *
     * @return void
     */
    public function run()
    {
        $this->envArray = include ROOT .'app'. DS .'environments.php';
        $this->detectEnvironment();
    }
}

/* Location: .Obullo/Application/Http.php */
```

> Obullo Http sınıfı konteyner içerisine $c['app'] olarak kaydedilir. Konsol ortamında ise o2/Application/Cli.php çağırıldığı için bu sınıf Http değil artık Cli sınıfıdır.

Uygulama sınıfını sabit tanımlamalar ( constants ), sınıf yükleyici ve konfigürasyon dosyasının yüklemesinden hemen sonraki aşamada tanımlı olarak gelir. Bunu daha iyi anlayabilmek için <b>kök dizindeki</b> index.php dosyasına bir göz atalım.


### index.php dosyası

Uygulamaya ait tüm isteklerin çözümlendiği dosya index.php dosyasıdır bu dosya sayesinde uygulama başlatılır. Bu dosyanın tarayıcıda gözükmemesini istiyorsanız bir .htaccess dosyası içerisine aşağıdaki kuralları yazmanız yeterli olacaktır.

```php
RewriteEngine on
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteCond $1 !^(index\.php|assets|robots\.txt)
RewriteRule ^(.*)$ ./index.php/$1 [L,QSA]
```

### Ortam Klasörü için Config Dosyalarını Yaratmak

Prodüksiyon ortamı üzerinden örnek verecek olursak bu klasöre ait config dosyaları içerisine yalnızca ortam değiştiğinde değişen anahtar değerlerini girmeniz yeterli olur. Çünkü konfigürasyon paketi geçerli ortam klasöründeki konfigürasyonlara ait değişen anahtarları <b>local</b> ortam anahtarlarıyla eşleşirse değiştirir aksi durumda olduğu gibi bırakır.

Mesala prodüksiyon ortamı içerisine aşağıdaki gibi bir <b>config.php</b> dosyası ekleseydik config.php dosyası içerisine sadece değişen anahtarları eklememiz yeterli olacaktı.

```php
- app
    - config
        + local
        - production
            config.php
            database.php
        + test
        - myenv
            config.php
            database.php
```

Aşağıdaki örnekte sadece dosya içerisindeki değişime uğrayan anahtarlar gözüküyor. Uygulama çalıştığında bu anahtarlar varolan local ortam anahtarları ile değiştirilirler.

#### config.php Örneği

```php
return array(
                    
    'error' => [
        'debug' => false,  // Friendly debugging feature "disabled"" in "production" environment.
    ],

    'log' =>   [
        'enabled' => false,
    ],

    'url' => [
        'webhost' => 'example.com',
        'baseurl' => '/',
        'assets' => 'http://cdn.example.com/assets/',
    ],

    'debugger' => [
        'enabled' => false,
    ],

    'cookie' => [
        'domain' => ''  // Set to .your-domain.com for site-wide cookies

    ],
);

/* End of file config.php */
/* Location: .app/config/env/production/config.php */
```

### environments.php Dosyası

Uygulamanızın hangi ortamda çalıştığını belirleyen konfigürasyon dosyasıdır. Ortam değişkeni <b>app/environments.php</b> dosyasına tanımlayacağınız sunucu isimlerinin ( <b>hostname</b> ) geçerli sunucu ismi ile karşılaştırması sonucu ile elde edilir. Aşağıda <b>app/environments.php</b> dosyasının bir örneğini inceleyebilirsiniz.

```php
return array(

    'local' => [
        'john-desktop',     // hostname
        'localhost.ubuntu', // hostname
    ],

    'test' => [
        'localhost.test',
    ],

    'production' => [
        'localhost.production',
    ],
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
We could not detect your application environment, please correct your app/environments.php hostnames.
```

### Ortam Değişkeni

Geçerli ortam değişkenine geri döner.

```php
echo $c['app']->env();  // Çıktı  local
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

### $c['app']->environments();

Ortam konfigürasyon dosyasında ( <b>app/environments.php</b> ) tanımlı olan ortam adlarına bir dizi içerisinde geri döner.

```php
print_r($c['app']->environments());

/* Çıktı
Array
(
    [0] => local
    [1] => test
    [2] => production
)
*/   
```

### $c['app']->envArray();

Ortam konfigürasyon dosyasının ( <b>app/environments.php</b> ) içerisindeki tanımlı tüm diziye geri döner.

```php
print_r($c['app']->envArray());

/* Çıktı
Array ( 
    'local' => array(
            [0] => my-desktop 
            [1] => someone.computer 
            [2] => anotherone.computer 
            [3] => john-desktop 
    ),
    'production' => array( .. )
)
*/
```

### $c['app']->envPath();

Geçerli ortam değişkeninin dosya yoluna geri döner.

```php
echo $c['app']->envPath();  // Çıktı  /var/www/project.com/app/config/local/
```

### .env.*.php Dosyaları

------

<b>.env*</b> dosyaları servis ve sınıf konfigürasyonlarında ortak kullanılan bilgiler yada şifreler gibi daha çok paylaşılması mümkün olmayan hassas bilgileri içerir. Bu dosyalar içerisindeki anahtarlara <b>$c['env']['variable']</b> fonksiyonu ile ulaşılmaktadır. Takip eden örnekte bir .env dosyasının nasıl gözüktüğü daha kolay anlaşılabilir.

```php
return array(
    
    'MYSQL_USERNAME' => 'root',
    'MYSQL_PASSWORD' => '123456',

    'MONGO_HOST'     => 'localhost',
    'MONGO_USERNAME' => 'root',
    'MONGO_PASSWORD' => '123456',

    'REDIS_HOST' => '127.0.0.1',
    'REDIS_AUTH' => '',  // aZX0bjL
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

Eğer <b>config.php</b> dosyasında <kbd>error > debug</kbd> değeri <b>false</b> ise boş bir sayfa görüntülenebilir bu gibi durumlarla karşılaşmamak için <b>local</b> ortamda <kbd>error > debug</kbd> değerini her zaman <b>true</b> yapmanız önerilir.

> **Not:** Boş sayfa hatası aldığınızda eğer konfigürasyon dosyasından error > debug açıksa ve buna rağmen hatayı göremiyorsanız <kbd>error > reporting</kbd> değerini true yaparak doğal php hataları görebilirsiniz.

### Env Sınıfı

Env sınıfı <b>o2/Application/Http.php</b> dosyasında ön tanımlı olarak gelir. Env fonksiyonları konfigürasyon dosyaları içerisinde kullanılırlar.<b>.env.*.php</b> dosyalarındaki anahtarlar uygulama çalıştığında ilk önce <b>$_ENV</b> değişkenine atanırlar ve konfigürasyon dosyasında kullanmış olduğumuz <b>Obullo\Config\Env</b> sınıfı ile bu değerler konfigürasyon dosyalarındaki anahtarlara atanmış olurlar.

Böylece konfigürasyon dosyalarındaki hassas ve istisnai ortak değerler tek bir dosyadan yönetilmiş olur.

Örnek bir env konfigürasyon çıktısı

```php
echo $c['env']['MONGO_USERNAME.root']; // Bu konfigürasyon boş gelirse default değer root olacaktır.
```

Yukarıdaki örnekte fonksiyonun <b>birinci</b> parametresi <b>$_ENV</b> değişkeninin içerisinden okunmak istenen anahtardır, noktadan sonraki ikinci parametre anahtarın varsayılan değerini tayin eder ve en son noktadan sonraki parametre anahtarın zorunlu olup olmadığını belirler.

Eğer en <b>son</b> parametre <b>required</b> olarak girilirse <b>$_ENV</b> değişkeni içerisinden anahtar değeri boş geldiğinde uygulama hata vererek işlem php <b>die()</b> metodu ile sonlanacaktır.

Boş gelemez zorunluluğuna bir örnek

```php
echo $c['env']['MONGO_USERNAME.root.required']; // Root parametresi boş gelemez.
```

Aşağıdaki örnekte ise mongo veritabanına ait konfigürasyon içerisine $_ENV değerlerinin bu sınıf ile nasıl atandığını görüyorsunuz.

```php
return array(

    'connections' =>
    [
        'default' => [
            'server' => 'mongodb://'.$c['env']['MONGO_USERNAME.root'].':'.$c['env']['MONGO_PASSWORD.null'].'@'.$c['env']['MONGO_HOST.required'].':27017',
            'options'  => ['connect' => true]
        ],
        'second' => [
            'server' => 'mongodb://test:123456@localhost:27017',
            'options'  => ['connect' => true]
        ]
    ],

);

/* End of file mongo.php */
/* Location: .app/config/local/mongo.php */
```

### Yeni Bir Ortam Değişkeni Yaratmak

Yeni bir ortam yaratmak için <b>app/environments.php</b> dosyasına ortam adını küçük harflerle girin. Aşağıdaki örnekte biz <b>myenv</b> adında bir ortam yaratttık.

#### environments.php

```php
return array(
    'local' => [ ... ],
    'test' =>  [ ... ],
    'production' => [ ... ]
    'myenv' => [
        'example.hostname'
        'example2.hostname'
    ]
);

/* End of file environments.php */
/* Location: .app/environments.php */
```

Yeni yarattığınız ortam klasörüne içine gerekli ise bir <b>config.php</b> dosyası ve database.php gibi diğer config dosyalarını yaratabilirsiniz. 

#### Servis Sağlayıcıları

Servis sağlayıcıları servislerden farklı olarak uygulama sınıfı içerisinden tanımlanırlar ve uygulamanın çoğu yerinde sıklıkla kullanılan servis sağlayıcılarının önce <kbd>app/providers.php</kbd> dosyasında tanımlı olmaları gerekir. Tanımla sıralamasında öncelik önemlidir uygulamada ilk yüklenenen servis sağlayıcıları her zaman en üstte tanımlanmalıdır. Örneğin logger servis sağlayıcısı uygulama ilk yüklendiğinde en başta log servisi tarafından kullanıldığından bu servis sağlayıcısının her zaman en tepede ilan edilmesi gerekir.

Servis sağlayıcıları <kbd>app/providers.php</kbd> dosyasına aşağıdaki gibi tanımlanırlar.

```php
/*
|--------------------------------------------------------------------------
| Register application service providers
|--------------------------------------------------------------------------
*/
$c['app']->register(
    [
        'logger' => 'Obullo\Service\Providers\LoggerServiceProvider',
        'database' => 'Obullo\Service\Providers\DatabaseServiceProvider',
        'cache' => 'Obullo\Service\Providers\CacheServiceProvider',
        'redis' => 'Obullo\Service\Providers\RedisServiceProvider',
        'memcached' => 'Obullo\Service\Providers\MemcachedServiceProvider',
        'mailer' => 'Obullo\Service\Providers\MailerServiceProvider',
        'amqp' => 'Obullo\Service\Providers\AmqpServiceProvider',
    ]
);
```

Eğer kafanızda soru işaretleri varsa servisler ve servis sağlayıcılarının tam olarak ne olduğu hakkında daha detaylı bilgi için [Container.md](/Container/Docs/tr/Container.md) dosyasına bir gözatın.


#### Application Sınıfı Referansı

------

##### $this->c['app']->env();

Geçerli ortam değişkenine geri döner.

##### $this->c['app']->middleware(string | object $class, $params = array());

Uygulamaya dinamik olarak http katmanı ekler. Birinci parametre sınıf ismi veya nesnenin kendisi, ikinci parametre ise sınıf içerisine enjekte edilebilecek parametrelerdir.

##### $this->c['app']->method();

Uygulama sınıfında eğer metod tanımlı değilse Controller sınfından çağırır.

##### $this->c['app']->router->method();

Uygulamada kullanılan evrensel <b>router</b> nesnesine geri döner. Uygulama içerisinde bir hiyerarşik katman ( HMVC bknz. Layer paketi  ) isteği gönderildiğinde router nesnesi istek gönderilen url değerinin yerel değişkenlerinden yeniden oluşturulur ve bu yüzden evrensel router değişime uğrar. Böyle bir durumda bu method sizin ilk durumdaki http isteği yapılan evrensel router nesnesine ulaşmanıza imkan tanır.

##### $this->c['app']->uri->method();

Uygulamada kullanılan evrensel <b>uri</b> nesnesine geri döner. Uygulama içerisinde bir katman ( bknz. Layer paketi ) isteği gönderildiğinde uri nesnesi istek gönderilen url değerinin yerel değişkenlerinden yeniden oluşturulur ve bu yüzden evrensel uri değişime uğrar. Böyle bir durumda bu method sizin ilk durumdaki http isteği yapılan evrensel uri nesnesine ulaşmanıza imkan tanır.

##### $this->c['app']->register(array $providers);

<kbd>.app/providers.php</kbd> dosyasında servis sağlayıcılarını uygulamaya tanımlamak için kullanılır. Uygulamanın çoğu yerinde sıklıkla kullanılan servis sağlayıcıların önce bu dosyada tanımlı olmaları gerekir. Tanımla sıralamasında öncelik önemlidir uygulamada ilk yüklenenen servis sağlayıcıları her zaman en üstte tanımlanmalıdır.

##### $this->c['app']->provider(string $name)->get(array $params);

Uygulamaya tanımlanmış servis sağlayıcısı nesnesine geri döner. Tanımlı servis sağlayıcıları <kbd>app/providers.php</kbd> dosyası içerisine kaydedilir.

##### $this->c['app']->isCli();

Uygulamaya eğer bir konsol arayüzünden çalışıyorsa true değerine aksi durumda false değerine geri döner.

##### $this->c['app']->environments();

Ortam konfigürasyon dosyasında ( app/environments.php ) tanımlı olan ortam adlarına bir dizi içerisinde geri döner.

##### $this->c['app']->envArray();

Ortam konfigürasyon dosyasının ( app/environments.php ) içerisindeki tanımlı tüm diziye geri döner.

##### $this->c['app']->envPath();

Geçerli ortam değişkeninin dosya yoluna geri döner.
