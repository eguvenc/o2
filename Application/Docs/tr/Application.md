
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

>Obullo Http sınıfı konteyner içerisine $c['app'] olarak kaydedilir. Konsol ortamında ise o2/Application/Cli.php çağırıldığı için bu sınıf Http değil artık Cli sınıfıdır.

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

### environments.php dosyası

Uygulamanızın hangi ortamda çalıştığını belirleyen konfigurasyon dosyasıdır. Ortam değişkeni <b>app/environments.php</b> dosyasına tanımlayacağınız sunucu isimlerinin ( <b>hostname</b> ) geçerli sunucu ismi ile karşılaştırması sonucu ile elde edilir. Aşağıda <b>app/environments.php</b> dosyasının bir örneğini inceleyebilirsiniz.

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
We could not detect your application environment, please correct your app/environments.php hostnames.
```

### $c['app']->env();

Geçerli ortam değişkenine geri döner.

```php
echo $c['app']->env();  // Çıktı  local
```

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
    [0] => my-desktop 
    [1] => someone.computer 
    [2] => anotherone.computer 
    [3] => john-desktop 
)
*/
```

### $c['app']->envPath();

Geçerli ortam değişkeninin dosya yoluna geri döner.

```php
echo $c['app']->envPath();  // Çıktı  /var/www/project.com/app/config/local/
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

Env fonksiyonu <b>o2/Application/Http.php</b> dosyasında tanımlı olarak gelir. Bu fonksiyon konfigürasyon dosyaları içerisinde kullanılırlar.Yukarıdaki örnekte gösterdiğimiz anahtarlar uygulama çalıştığında ile önce <b>$_ENV</b> değişkenine atanırlar ve konfigürasyon dosyasında kullanmış olduğumuz <b>$c['env']</b> sınıfı ile değerler konfigürasyon dosyalarındaki anahtarlara atanmış olurlar.

```php
echo $c['env']['MONGO_USERNAME.root']; // Root parametresi boş gelirse default değer root olacaktır.
```

Fonksiyonun <b>birinci</b> parametresi <b>$_ENV</b> değişkeninin içerisinden okunmak istenen anahtardır, noktadan sonraki ikinci parametre anahtarın varsayılan değerini tayin eder ve en son noktadan sonraki parametre anahtarın zorunlu olup olmadığını belirler.

Eğer <b>ikinci</b> parametre girildiyse <b>$_ENV</b> değişkeni içerisindeki anahtar yok sayılır ve varsayılan değer geçerli olur.

Eğer <b>son</b> parametre <b>REQUIRED</b> olarak girildiyse <b>$_ENV</b> değişkeni içerisinden anahtar değeri boş geldiğinde uygulama hata vererek işlem php <b>die()</b> metodu ile sonlanacaktır.

```php
echo $c['env']['MONGO_USERNAME.root.REQUIRED']; // Root parametresi boş gelemez.
```

Aşağıdaki örnekte mongo veritabanına ait konfigürasyon içerisine $_ENV değerlerinin <b>$c['env']</b> sınıfı ile nasıl atandığını görüyorsunuz.

```php
return array(

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

Yeni yarattığınız ortam klasörüne içine gerekli ise bir <b>config.php</b> dosyası ve database.php gibi diğer config dosyalarını yaratabilirsiniz. 

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

Aşağıdaki örnekte sadece <b>error, log, url</b> ve <b>cookie</b> anahtarları içerisindeki değişen belirli anahtarlar gözüküyor. Uygulama çalıştığında bu anahtar değerleri geçerli olurken geri kalan anahtar değerleri local ortam dosyasından okunur.

#### config.php

```php
return array(
                    
    'error' => array(
        'debug' => false,
        'reporting' => false,
    ),

    'log' =>   array(
        'enabled' => true,
    ),

    'url' => [
        'webhost'  => 'example.xom',
        'baseurl'  => '/',
        'assets'   => [
            'url' => '/',
            'folder' => '/assets/', 
        ],
    ],

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

##### $this->c['app']->env();

Geçerli ortam değişkenine geri döner.

##### $this->c['app']->middleware(string | object $class, $params = array());

Uygulamaya dinamik olarak http katmanı ekler. Birinci parametre sınıf ismi veya nesnenin kendisi, ikinci parametre ise sınıf içerisine enjekte edilebilecek parametrelerdir.

##### $this->c['app']->method();

Uygulama sınıfında eğer metod tanımlı değilse Controller sınfından çağırır.

##### $this->c['app']->router->method();

Uygulamada kullanılan evrensel <b>router</b> nesnesine geri döner. Uygulama içerisinde bir hiyerarşik katman ( HMVC bknz. Layer paketi  ) isteği gönderildiğinde router nesnesi istek gönderilen url değerinin yerel değişkenlerinden yeniden oluşturulur ve bu yüzden evrensel router değişime uğrar. Böyle bir durumda bu method sizin ilk durumdaki http isteği yapılan evrensel router nesnesine ulaşmanıza imkan tanır.

##### $this->c['app']->uri->method();

Uygulamada kullanılan evrensel <b>uri</b> nesnesine geri döner. Uygulama içerisinde bir katman ( bknz. Layer paketi ) isteği gönderildiğinde uri nesnesi istek gönderilen url değerinin yerel değişkenlerinden yeniden oluşturulur ve bu yüzden evrensel router değişime uğrar. Böyle bir durumda bu method sizin ilk durumdaki http isteği yapılan evrensel uri nesnesine ulaşmanıza imkan tanır.

##### $this->c['app']->isCli();

Uygulamaya eğer bir konsol arayüzünden çalışıyorsa true değerine aksi durumda false değerine geri döner.

##### $this->c['app']->environments();

Ortam konfigürasyon dosyasında ( app/environments.php ) tanımlı olan ortam adlarına bir dizi içerisinde geri döner.

##### $this->c['app']->envArray();

Ortam konfigürasyon dosyasının ( app/environments.php ) içerisindeki tanımlı tüm diziye geri döner.

##### $this->c['app']->envPath();

Geçerli ortam değişkeninin dosya yoluna geri döner.