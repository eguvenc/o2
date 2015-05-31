
## Konteyner Sınıfı ( Container )

------

Bir Dependency Injection Container <b>DIC</b> veya kısaca konteyner, servisleri yaratmak ve uygulamaya yüklemek için kullanılır. Konteyner sınıfı yinelemeli olarak istenenen servislerin bağımlılıklarını yaratır ve onları uygulamaya enjekte eder.

Eğer servis konteynerların yada bağımlılık enjeksiyonunun ne olduğu hakkında çok fazla bilgiye sahip değilseniz bu konsept hakkında birşeyler okumak iyi bir başlangıç olabilir. İsterseniz konteynırlar arasında en basit ve popüler bir sınıf olan <a href="http://pimple.sensiolabs.org/" target="_blank">Pimple</a>  adlı projenin dökümentasyonuna bir gözatın. Obullo içerisinde kullanılan konteyner bu sınıfın biraz daha sadeleştirilip çerçeveye göre uyarlanmış versiyonudur.

> **Not:** <b>$c</b> değişkeni konteyner sınıfına eşitlenerek uygulamanın ( Application/Http paketinin ) en başında ilan edilmiştir. Uygulamada gördüğünüz bir <b>$c</b> değişkeni her zaman konteyner sınıfını temsil eder.

<ul>
<li>
    <a href="#services">Servisler</a>
    <ul>
        <li><a href="#service-definition">Servisleri Tanımlamak</a></li>
        <li><a href="#service-load">Servisleri Yüklemek</a></li>
        <li><a href="#service-get">Servis Nesnesine Dönmek ( $this->c->get() )</a></li>
        <li><a href="#service-loading-a-class">Servis Olmayan Bir Sınıfı Yüklemek</a></li>
        <li><a href="#service-environments">Servisleri Çevre Ortamına Duyarlı Hale Getirmek</a></li>
    </ul>
</li>

<li>
    <a href="#service-providers">Servis Sağlayıcıları</a>
    <ul>
        <li><a href="#service-provider-definition">Servis Sağlayıcılarını Tanımlamak</a></li>
        <li><a href="#service-provider-load">Servis Sağlayıcılarını Yüklemek</a></li>
        <li><a href="#service-providers-list">Mevcut Servis Sağlayıcıları</a></li>
        <li><a href="#service-providers-custom">Kendi Servis Sağlayıcılarınızı Tanımlamak</a></li>
    </ul>
</li>
<li><a href="#application-doc">Uygulama Sınıfı Belgelerine Bir Gözatın</a></li>
<li><a href="#container-reference">Konteyner Sınıfı Referansı</a></li>
</ul>

<a name="services"></a>

## Servisler

Servisler uygulama kalitesini arttıran aracı sınıflardır. Bir sınıfın servis haline getirilmesinin nedeni kütüphaneyi uygulama içerisinde kullandırırken kuruluma ait metotları tekrar tekrar yazmak yerine onu bir servis içerisinden hazırlamış metot ve parametreleri ile yaratarak bu nesne değerleriyle uygulamada <b>paylaşımlı</b> kullanıp uygulamanızın kod kalitesini ve esnekliğini arttırmaktır. İşte bu türden uygulama içerisinde aynı parametrelere sahip bir kütüphane uygulamaya bir servis olarak sunuluyorsa bu türden servisler paylaşımlı servisler olarak adlandırılırlar. ( Shared Services ).

<a name="service-definition"></a>

### Servisleri Tanımlamak

Obullo da servisler servis klasörü içerisindeki aracı sınıflar tarafından yüklenirler. Böyle bir arayüze ihtiyaç duyulmasının nedeni servisleri bir klasör içerisinde gruplayarak geçerli çevre ortamı değiştiğinde ( local, test, production ) onları farklı davranışlara göre çalıştırabilmektir.

Önceden tanımlı servisler uygulama çalıştığı anda <kbd>app/classes/Service</kbd> klasöründen konteyner içerisine kayıt edilirler. Yeni bir servis yaratmak için <kbd>app/classes/Service</kbd> dizininde takip eden örnekte gösterildiği gibi bir sınıf yaratılması gerekir.


```php
namespace Service;

use Obullo\Container\Container;
use Obullo\Service\ServiceInterface;
use Obullo\Session\Session as SessionClass;

class Session implements ServiceInterface
{
    public function register(Container $c)
    {
        $c['session'] = function () use ($c) {
            $session = new SessionClass($c);
            $session->registerSaveHandler();
            $session->setName();
            $session->start();
            return $session;
        };
    }
}

// END Session service

/* End of file Session.php */
/* Location: .classes/Service/Session.php */
```

Yukarıdaki örnekte <b>session</b> sınıfına ait bir servis konfigürasyonu görülüyor. 

Bu tanımlamadan sonra artık <kbd>app/classes/Service/Session.php</kbd> dizininde tanımlı olan Session sınıfına konteyner içerisinden aşağıdaki gibi ulaşılabilir.

```php
$this->c['session']->method();
```

<a name="service-load"></a>

### Servisleri Yüklemek

Konteyner içerisine bir kez kaydedilen bir sınıf uygulama içerisine tekrar tekrar çağrıldığında sınıfa ait değişken değerleri hep aynı kalır.

```php
$this->c['session'];	 // yeni nesne
$this->c['session'];	 // eski nesne
$this->c['session'];	 // eski nesne
```

Bir servisin paylaşımlı <b>olmaması</b> demek onun her çağrıldığında yeni bir nesneye dönmesi demektir. Uygulama içerisinde <b>çok nadir</b> durumlarda bir servisin yeni değişkenler ile gelmesi istenebilir. Eğer servis yada sınıf yeni parametereler gönderilerek oluşturulması gerekiyorsa konteyner içerisinden <b>get('class', $params = array())</b> komutu kullanılarak nesnenin kayıtlı olduğu closure fonksiyonu yeniden çalıştırılır ve sınıfın yeni nesneye dönmesi sağlanır.

```php
$this->session = $this->c->get('session', ['test' => 1]);
```

Controller sınıfında <b>$c</b> nesnesi bu sınıfa önceden <kbd>$this->c</kbd> olarak kayıtlı geldiğinden Controller sınıfı içerisinde <b>$c</b> değişkeni hep <kbd>$this->c</kbd> olarak kullanılır. 

```php
$this->c['session'];
```

Konteyner içerisinden çağırılan bir kütüphanede yine Controller içerisine <kbd>$this->class</kbd> olarak kaydedilir.

```php
$this->session->method();
```

Servislerin ve kütüphanelerin Controller sınıfı içerisinde nasıl kullanıldığına dair bir örnek

```php
namespace Welcome;

class Welcome extends \Controller
{
    public function load()
    {
        $this->c['url'];
        $this->c['session'];
    }

    public function index()
    {
    	$this->session->set('test', 'Hello Services !');
    }
}

/* End of file welcome.php */
/* Location: .modules/welcome/welcome.php */
```

<a name="service-get"></a>

#### Servis Nesnesine Dönmek ( $this->c->get() )

Eğer bir nesnenin Controller sınıfına kendiliğinden kayıt edilmesini <b>önlemek</b> istiyorsanız get() fonksiyonunu kullanmanız gerekir. Get fonksiyonu konteyner içerisinde kayıtlı bir sınıfın paylaşımlı nesnesine döner.

```php
$this->session = $this->c->get('session');
$this->session->method();
```

> **Önemli:** Get fonksiyonu ile alınan bir servis yada kütüphane Controller sınıfı içerisine kaydedilmez.

Eğer <b>$params</b> parametresine <b>false</b> değeri gönderilirse sınıf yeni nesneye döner.


```php
$this->nesne = $this->c->get('nesne', false);
```

Eğer <b>$params</b> parametresine <b>array</b> türü gönderilirse kayıtlı closure fonksiyonu bu yeni parametreler ile yeni bir nesneye döner.

```php
$this->nesne = $this->c->get('nesne', ['example' => 'parameter']);
```

Eğer parametre gönderilmezse nesne ilk oluşturulan parametereler ile oluşturulan eski nesne değerlerine ( instance ) döner.

```php
$this->c->get('nesne', ['example' => 'old']);  // yeni nesne
$this->c->get('nesne');  // eski nesne
$this->c->get('nesne');  // eski nesne
$this->c->get('nesne', ['example' => 'new']); // yeni nesne
```

<a name="service-loading-a-class"></a>

### Servis Olmayan Bir Sınıfı Yüklemek

Eğer bir sınıf uygulamadaki kısa adı ile ( örneğin: session, cookie vb. ) <kbd>$c['class']</kbd> bu şekilde çağrıldı ise ilk önce uygulamada servis olarak kayıtlı olup olmadığına bakılır; eğer kayıtlı ise servisler içerisinden yüklenir. Eğer bu sınıf konteyner içerisinde yada servislerde mevcut olmayan bir sınıf ise; sınıf <b>Obullo\*</b> dizininden konteyner içerisine kaydedilerek geçerli sınıf nesnesine geri dönülür ve Controller içerisine 'class' ismi ile kaydedilir.

Örneğin <kbd>cookie</kbd> paketi bir servis olarak kayıtlı değildir ve bu yüzden <kbd>Obullo\Cookie\Cookie</kbd> dizininden çağrılarak konteyner içerisine kayıt edilir.

```php
$this->c['cookie'];
```

<a name="service-environments"></a>

### Servisleri Çevre Ortamına Duyarlı Hale Getirmek

Bildiğiniz gibi mevcut ortam değişkenleri <b>local, test, production</b> dır. Bu ortamların bütünü çevre ortamı olarak adlandırılır. 

> **Not:** Çevre ortamı konfigürasyonu hakkında detaylı bilgiyi Application paketi dökümentasyonundan elde edebilirsiniz.

Servisler servis dizini altına .php uzantılı bir dosya olarak konulduklarında çevre ortamına duyarsız çalışırlar. Bir servisin farklı ortamlarda farklı servis konfigürasyonlarının olması olasıdır.

##### Çevre ortamına duyarlı bir servis konfigürasyonu 3 kolay adımla yaratılabilir.

1. Servisi klasör olarak yaratın.
2. Servis klasörü içerisinde <b>Env</b> isimli bir klasör yaratın.
3. <b>Env</b> isimli klasör altında her bir ortam değişkeni için bir servis yaratın.

Gerçek bir örnek için Logger servisini inceleyebilirsiniz.

```php
- app
	- classes
		- Service
			- Logger
				- Env
					- Local.php
					- Production.php
					- Test.php

```

Böylelikle logger servisi çevre ortamı değiştiğinde her çevre ortamı için önceden yapılandırılmış servisler sayesinde farklı log yazıcıları kullanarak yazma işlemlerini gerçekleştirebilir.

<a name="service-providers"></a>

## Servis Sağlayıcıları

Bir servis sağlayıcısı yazımlıcılara uygulamada kullandıkları yinelenen farklı konfigürasyonlara ait parçaları uygulamanın farklı bölümlerinde güvenli bir şekilde tekrar kullanabilmelerine olanak tanır. Bağımsız olarak kullanılabilecekleri gibi bir servis konfigürasyonunun içerisinde de kullanılabilirler.

Uygulamada kullanılan servis sağlayıcısı bir <b>bağlantı yönetimi</b> ile ilgili ise farklı parametreler gönderilerek açılan bağlantıları yönetirler ve her yazılımcının aynı parametreler ile uygulamada birden fazla bağlantı açmasının önüne geçerler.

Yada uygulamada kullanılan servis sağlayıcısı bir <b>nesne yönetimi</b> ile ilgili ise farklı parametreler gönderilerek açılan yeni nesneleri yönetirler ve her yazılımcının aynı parametreler ile uygulamada birden fazla yeni nesne yaratmasının önüne geçerler.

Bir servis sağlayıcısı sınıfı yanlış yazılmış yada yapılandırılmış ise onu uygulamanızda kullandığınız bölümlerin hepsi yanlış çalışmaya başlar. Bu yüzden servis sağlayıcıları bir uygulama çalışırken en kritik rolü üstlenirler.

<a name="service-provider-definition"></a>

### Servis Sağlayıcılarını Tanımlamak

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

<a name="service-provider-load"></a>

### Servis Sağlayıcılarını Yüklemek

Bir servis sağlayıcısı <b>$c['app']</b> sınıfının <b>provider()</b> metodu çağrılarak yüklenir. Aşağıdaki örnekte cache servis sağlayıcısından konfigürasyonda varolan <b>default</b> bağlantı tanımlamasını kullanarak <b>get()</b> metodu ile bir bağlantı getirmesi talep ediliyor.


```php
$this->cache = $this->c['app']->provider('cache')->get(
    [
        'driver' => 'redis',
        'connection' => 'default'
    ]
);
```

Servis sağlayıcıları varolan bağlantıları yönetebilmek için aşağıdaki gibi <b>connections</b> anahtarına sahip bir konfigürasyon dosyasına ihtiyaç duyarlar. Aşağıda redis için <b>default</b> bağlantısına ait bir konfigürasyon örneği gösteriliyor.

```php
return array(

    'connections' => 
    [
        'default' => [
            'host' => $c['env']['REDIS_HOST'],
            'port' => 6379,
            'options' => [
                'persistent' => false,
                'auth' => $c['env']['REDIS_AUTH'],
                'timeout' => 30,
                'attempt' => 100,
                'serializer' => 'none',
                'database' => null,
                'prefix' => null,
            ]
        ],
        
        'second' => [

        ],

    ],

    'nodes' => [
        [
            'host' => '',
            'port' => 6379,
        ]
    ],

);

/* End of file redis.php */
/* Location: .app/config/env.local/cache/redis.php */
```

Eğer <b>second</b> bağlantısına ait bir bağlantı isteseydik o zaman servis sağlayıcımızı aşağıdaki gibi çağırmalıydık.

```php
$this->cache = $this->c['app']->provider('cache')->get(
    [
        'driver' => 'redis',
        'connection' => 'second'
    ]
);
```

Eğer Cache servis sağlayıcısından konfigürasyonda olmayan bir bağlantı talep etseydik aşağıdaki gibi <b>factory()</b> fonksiyonunu kullanmalıydık.

```php
$this->cache = $this->c['app']->provider('cache')->factory(
    [
        'driver' => 'redis',
        'options' => array(
        	'host' => '127.0.0.1',
	        'port' => 6379,
	        'options' => array(
	            'persistent' => false,
	            'auth' => '123456',
	            'timeout' => 30,
	            'attempt' => 100,
	            'serializer' => 'igbinary',
	            'database' => null,
	            'prefix' => null,
	        )
       )
    ]
);
```

Servis sağlayıcısı bir kez yüklendikten sonra artık cache metotlarına erişebilirsiniz.

```php
$this->cache->method();
```

<a name="service-providers-list"></a>

### Mevcut Servis Sağlayıcıları 

Obullo için yazılan servis sağlayıcıları <kbd>Obullo\Service\Providers</kbd> klasörü altında gruplanmıştır. Aşağıdaki tablo varolan servis sağlayıcılarının bir listesini gösteriyor.

<table>
    <thead>
        <tr>
            <th>Sağlayıcı</th>
            <th>Açıklama</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td><b>amqp</b></td>
            <td>Uygulamanızdaki queue/amqp.php konfigürasyonunu kullanarak AMQP bağlantılarını yönetir.</td>
        </tr>
        <tr>
            <td><b>cache</b></td>
            <td>Uygulamanızdaki cache.php konfigürasyonunu kullanarak sürücülere göre cache bağlantılarını yönetir.</td>
        </tr>
        <tr>
            <td><b>database</b></td>
            <td>Uygulamanızdaki database.php konfigürasyonunu kullanarak seçilen database sürücüsüne göre ilişkili database (RBDMS) nesnelerini yönetir.</td>
        </tr>
        <tr>
            <td><b>logger</b></td>
            <td>Uygulamanızdaki logger.php konfigürasyonunu kullanarak Logger servisini yapılandırmanıza yardımcı olur.</td>
        </tr>
        <tr>
            <td><b>mailer</b></td>
            <td>Uygulamanızdaki mailer/* konfigürasyonunu kullanarak mail gönderme isteklerini yönetmenize yardımcı olur.</td>
        </tr>
        <tr>
            <td><b>memcache</b></td>
            <td>Uygulamanızdaki cache/memcache.php konfigürasyonunu kullanarak memcache bağlantılarını yönetmenize yardımcı olur.</td>
        </tr>
        <tr>
            <td><b>memcached</b></td>
            <td>Uygulamanızdaki cache/memcached.php konfigürasyonunu kullanarak memcached bağlantılarını yönetmenize yardımcı olur.</td>
        </tr>
        <tr>
            <td><b>mongo</b></td>
            <td>Uygulamanızdaki mongo.php konfigürasyonunu kullanarak mongo db bağlantılarını yönetir.</td>
        </tr>
        <tr>
            <td><b>pdo</b></td>
            <td>Uygulamanızdaki database.php konfigürasyonunu kullanarak pdo bağlantılarını yönetmenize yardımcı olur.</td>
        </tr>
        <tr>
            <td><b>redis</b></td>
            <td>Uygulamanızdaki cache/redis.php konfigürasyonunu kullanarak redis bağlantılarını yönetmenize yardımcı olur.</td>
        </tr>
    </tbody>
</table>


> **Not:** Obullo\Service\Providers paketinden yukarıda anlatılan her bir servis sağlayıcısına ait detaylı dökümentasyona ulaşabilirsiniz.

<a name="service-providers-custom"></a>

### Kendi Servis Sağlayıcılarınızı Tanımlamak

Eğer kendi oluşturduğunuz servis sağlayıcınızı çalıştırmak istiyorsanız <kbd>.app/classes/Service/Providers</kbd> klasörü altında aşağıdaki örnekte gösterildiği gibi bir servis sağlayıcı oluşturmalısınız. Servis sağlayıcınıza özgü bir bağlantı ( Konnektör ) varsa onu da <kbd>.app/classes/Service/Providers/Connections/</kbd> klasörü altında yaratmanız gerekir. Bu örnekte biz kendimize özgü bir servis sağlayıcısı konnektörü olamdığını varsayarak konnektörü Obullo klasöründen çağırıyoruz.

```php
namespace Service\Providers;

use Obullo\Container\Container;
use Obullo\Service\ServiceInterface;
use Obullo\Service\ServiceProviderInterface;
use Obullo\Service\Providers\Connections\CacheConnectionProvider;

class CacheServiceProvider implements ServiceProviderInterface
{
    public $connector;
    
    public function register(Container $c)
    {
        $this->connector = new CacheConnectionProvider($c);
    }

    public function get($params = array())
    {
        return $this->connector->getConnection($params);
    }
}

// END CacheServiceProvider Class

/* End of file CacheServiceProvider.php */
/* Location: .app/classes/Service/Providers/CacheServiceProvider.php */
```

Servis sağlayıcısını aşağıdaki gibi <kbd>.app/providers.php</kbd> dosyası içerisine eklediğinizde artık servis sağlayıcınız uygulama içerisinden çalışmaya başlayacaktır.

```php
/*
|--------------------------------------------------------------------------
| Cache Service Provider
|--------------------------------------------------------------------------
*/
$c['app']->register(
    [
        'logger' => 'Obullo\Service\Providers\LoggerServiceProvider',
        'cache' => 'Service\Providers\CacheServiceProvider'
    ]
);

/* End of file providers.php */
/* Location: .app/providers.php */
```

<a name="application-doc"></a>

### Uygulama Sınıfı Belgelerine Bir Gözatın

Eğer konteyner sınıfını kavradıysanız Obullo çerçevesi hakkında temel olan çoğu şeyi öğrendiniz demektir fakat çerçeveye daha hakim olmak için [Application.md](/Application/Docs/tr/Application.md) dökümentasyonuna da bir gözatmanızı istiyoruz.

<a name="container-reference"></a>

### Konteyner Sınıfı Referansı

------

#### $c['class'];

Eğer bir sınıf uygulamadaki kısa adı ile ( örneğin: session, cookie vb. ) çağrıldı ise ilk önce uygulamada servis olarak kayıtlı olup olmadığına bakılır; eğer kayıtlı ise servisler içerisinden yüklenir. Eğer bu sınıf konteyner içerisinde yada servislerde mevcut olmayan bir sınıf ise bu durumda sınıf <b>Obullo\*</b> dizininden konteyner içerisine kaydedilerek geçerli sınıf nesnesine geri dönülür ve Controller içerisine 'class' ismi ile kaydedilir.

#### $c->get(string $class, mixed $params = false|array());

Konteyner içerisinde kayıtlı bir sınıfın paylaşımlı nesnesine döner ve nesne Controller sınıfı içerisine kaydedilmez. Eğer <b>$params</b> parametresine <b>false</b> yada <b>array</b> değeri gönderilirse closure fonksiyonuna parametre gönderilerek yeni bir nesne elde edilebilir.

#### $c->has(string $class);

Bir sınıfın uygulamadaki kısa adının konteyner içerisine kayıtlı olup olmadığını kontrol eder. Kayıtlı ise <b>true</b> değilse <b>false</b> değerine geri döner.

#### $c->loaded(string $class);

Bir sınıfın uygulamaya konteyner içerisinden önceden yüklenip yüklenmediğini kontrol eder. Yüklenmiş ise <b>true</b> değilse <b>false</b> değerine geri döner.

#### $c->isRegistered(string $provider)

Bir servis sağlayıcısı <kbd>app/providers.php</kbd> dosyasında kayıtlı ise <b>true</b> değilse <b>false</b> değerine geri döner.

#### $c->keys();

Tanımlı tüm sınıfların anahtar adlarına bir dizi içerisinde geri döner.
