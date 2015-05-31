
## Konfigürasyon Sınıfı 

------

Konfigürasyon sınıfı <kbd>app/config</kbd> klasöründeki uygulamanıza ait konfigürasyon dosyalarını yönetir. Bu sınıf uygulama içerisinde konfigürasyon dosyalarını çevre ortamına ( environments ) göre geçerli klasörden yükler ve çağrıldığında tüm yüklenen konfigürasyon dosyalarına ait konfigürasyonlardan oluşan bir diziye geri döner. 

> *Not:* Varsayılan konfigürasyon dosyası <kbd>app/config/env/local/config.php</kbd> dosyasıdır ve bu dosya uygulama çalıştırıldığında uygulamaya kendiliğinden dahil edilir. Bu dosyayı ayrıca yüklememelisiniz.

### Sınıfı Yüklemek

------

```php
$this->c['config']->method();
```

### Konfigürasyon Dosyalarına Erişim

Bir konfigürasyon dizisine erişim dizi erişimi ( Array Access ) yöntemi ile gerçekleşir. Bu yöntem konfigürasyon sekmelerine aşağıdaki biçiminde erişmemizi sağlayarak konfigürasyonlara erişimi kolaylaştırır.

```php
$this->c['config']['item']['subitem'];
```

Array Access yöntemi ile ilgili daha fazla bilgiye Php dökümentasyonu <a href="http://php.net/manual/tr/class.arrayaccess.php" target="_blank">http://php.net/manual/tr/class.arrayaccess.php</a> sayfasından ulaşabilirsiniz.

### Konfigürasyon Dosyalarını Yüklemek

Bir konfigürasyon dosyası config sınıfı içerisindeki <b>load()</b> metodu ile yüklenir.

```php
$this->c['config']->load('database');
```

Yukarıda verilen örnekte çevre ortamını "local" ayarlandığını varsayarsak <kbd>database.php</kbd> dosyası <kbd>app/config/env/local/</kbd> klasöründen çağrılır. Bir konfigürasyon dosyası bir kez yüklendiğinde ona config sınıfı ile her yerden ulaşabilmek mümkündür.

```php
echo $this->c['config']['database']['connections']['db']['host'];  // Çıktı localhost
```

Bununla beraber config sınıfı içerisindeki load metodu yüklenen dosyanın konfigürasyonuna geri döner.

```php
echo $this->c['config']->load('database')['connections']['db']['host'];   // Çıktı localhost
```

### Ortam Klasörü için Konfigürasyon Yaratmak

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

    'http' => [
        'debugger' => false,
    ],

    'cookie' => [
        'domain' => ''  // Set to .your-domain.com for site-wide cookies

    ],
);

/* End of file config.php */
/* Location: .app/config/env.production/config.php */
```

### Paylaşımlı Konfigürasyon Dosyaları

Herhangi bir ortam değişkeni klasörü içerisinde yer almayıp <kbd>app/config/</kbd> klasörü kök dizininde yer alan diğer bir deyişle dışarıda kalan konfigürasyon dosyaları paylaşımlı konfigürasyon dosyaları olarak adlandırılırlar.

Paylaşımlı konfigürasyon dosyalarınının yüklenme biçimleri ortam değişkeni konfigürasyon dosyaları ile aynıdır bir konfigürasyon dosyasının paylaşımlı mı yoksa ortam değişkeni mi olup olmadığı uygulama tarafından kendiliğinden belirlenir.

```php
$this->config->load('security');
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

Uygulamanıza ait çevre ortamı aşağıdaki metola elde edilir.

```
echo $this->c['app']->env();  // local
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


### .env.*.php Dosyaları

------

<b>.env*</b> dosyaları servis ve sınıf konfigürasyonlarında ortak kullanılan bilgiler yada şifreler gibi daha çok paylaşılması mümkün olmayan hassas bilgileri içerir. Bu dosyalar içerisindeki anahtarlara <b>$c['env']['variable']</b> fonksiyonu ile ulaşılmaktadır. Takip eden örnekte bir .env dosyasının nasıl gözüktüğü daha kolay anlaşılabilir.

```php
return array(
    
    'COOKIE_DOMAIN' => '',
    
    'MYSQL_USERNAME' => 'root',
    'MYSQL_PASSWORD' => '123456',

    'MONGO_USERNAME' => 'root',
    'MONGO_PASSWORD' => '123456',

    'REDIS_HOST' => '127.0.0.1',
    'REDIS_AUTH' => '',
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
            'server' => 'mongodb://root:'.$c['env']['MONGO_PASSWORD.null'].'@localhost:27017',
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

#### Konfigürasyon Dosyalarına Yazmak

Config sınıfı içerisindeki write metodu <kbd>app/config/env/$env/</kbd> klasörü içerisindeki config dosyalarınıza yeni konfigürasyon verileri kaydetmenizi sağlar. Takip eden örnekte <kbd>app/config/env/local/domain.php</kbd> domain konfigürasyon dosyasındaki <b>maintenance</b> değerini güncelliyoruz.

```php
$newArray = $this->c['config']['domain'];
$newArray['root']['maintenance'] = 'down';  // Yeni değerleri atayalım

$this->c['config']->write('domain.php', $newArray);
```

Şimdi domain.php dosyanız aşağıdaki gibi güncellenmiş olmalı.

```php
return array(

    'root' => [
        'maintenance' => 'down',
        'regex' => null,
    ],
    'mydomain.com' => [
        'maintenance' => 'up',
        'regex' => '^framework$',
    ],
    'sub.domain.com' => [
        'maintenance' => 'up',
        'regex' => '^sub.domain.com$',
    ],
);

/* End of file */
/* Location: ./var/www/framework/app/config/env/local/domain.php */
```

Yukarıdaki örnek <kbd>app/config/env/$env/</kbd> klasörü altındaki dosyalara yazma işlemi yapar. Eğer env klasörü dışında olan yani paylaşımlı bir konfigürasyon dosyasına yazma işlemi gerçekleştimek istiyorsak <b>"../"</b> dizinden çıkma karakteri kullanarak kaydetme işlemini gerçekleştirmemiz gerekir.

```php
$newArray = $this->c['config']->load('agents');
$newArray['platforms']['pc']['test'] = 'Merhaba yeni platform';  // Yeni değerleri atayalım

$this->c['config']->write('../agents.php', $newArray);
```

Şimdi <kbd>.app/config/agents.php</kbd> dosyasına bir gözatın.

```php
return array(
    
    'platforms' => [

        'pc' => [
            'gnu' => 'GNU/Linux',
            'unix' => 'Unknown Unix OS',
            'test' => 'Merhaba yeni platform',


/* End of file agents.php */
/* Location: .app/config/agents.php */
```

#### Config Sınıfı Referansı

------

##### $this->config->load(string $filename);

Konfigürasyon dosyalarınızı <kbd>app/config/env/$env/</kbd> yada <kbd>app/config/</kbd> dizininden yükler. Dosya bu iki dizinden birinde mevcut değilse php hataları ile karşılaşılır.

##### $this->config['name']['item'];

Konfigürasyon sınıfı içerisine yüklenmiş bir dosyaya ait konfigürasyona erişmeyi sağlar.

##### $this->config->array['name']['item'] = 'value';

Yüklü olan bir konfigürasyona dinamik olarak yeni değerler atar.

##### $this->config->write(string $filename, array $data);

<kbd>app/config/</kbd> klasöründeki konfigürasyon dosyalarına veri yazmayı sağlar.


#### Env Sınıfı Referansı

------

##### $c['env']['variable'];

Bir konfigürasyon dosyası içerisinde çevre ortamına duyarlı bir değişkene ulaşmayı sağlar.

##### $c['env']['variable.default'];

Bir konfigürasyon dosyası içerisinde çevre ortamına duyarlı bir değişkenin değeri yoksa varsayılan olarak girilen ("default") değerin atanmasını sağlar.

##### $c['env']['variable.null'];

Bir konfigürasyon dosyası içerisinde çevre ortamına duyarlı bir değişkenin değeri yoksa varsayılan olarak <b>"null"</b> boş değeri atanmasını sağlar.

##### $c['env']['variable.default.required']; yada $c['env']['variable.required'];

Bir konfigürasyon dosyası içerisinde çevre ortamına duyarlı bir değişkenin değeri yoksa uygulamanın durarak genel hata vermesini sağlar.