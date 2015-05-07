
## Http Katmanları ( Middlewares ) ( Http Filtreleri )

Http katmanı Rack protokolünün php ye uyarlanmış bir versiyonudur. Bknz. <a href="http://en.wikipedia.org/wiki/Rack_%28web_server_interface%29">http://en.wikipedia.org/wiki/Rack_%28web_server_interface%29</a>

Http katmanları eski adıyla http filtreleridir. Uygulama içerisindeki katmanlar uygulamayı etkilemek, analiz etmek, uygulama ortamını yada request ve response nesnelerini uygulama çalışmasından sonra veya önce araya girerek etkilemek için kullanılırlar.

Katmanlar <b>application</b> paketi içerisinde <b>middleware</b> sınıfına genişleyen basit php sınıflarıdır. Bir katman route yapısında tutturulabilir yada bağımsız olarak uygulamanın her yerinde çalışabilir.

### Katman Mimarisi

Uygulamayı gezegenimizin çekirdeği gibi düşünürsek çekirdeğe doğru gittğimizde dünyanın her bir katmanını bir http katmanı olarak kabul etmeliyiz. Bu çerçevede uygulama index.php dosyasındaki run metodu ile çalıştığında en dışdaki katman ilk olarak çağrılır. Eğer bu katmandan bir <b>next</b> komutu cevabı elde edilirse bu katman opsiyonel olarak ona en yakın olan bir sonraki katmanı çağırır. Bu aşamalar dünyanın çekirdeğine inilip en içteki katman uygulamayı çalıştırana kadar bir döngü içerisinde kademeli olarak devam eder.

Her katmanda bir <b>load()</b> ve <b>call()</b> metodu olmak zorundadır. Load metodu controller içerisindeki yükleme seviyesine ait katmanları oluştururken call metodu ise controller içerisindeki metodun çalıştırılmasına ait katmanları oluşurulur. Böylelikle load ve call çalıştırılma seviyelerinden önceki ve sonraki işlemler kontrol altına alınmış olur.

Uygulamaya $c['app']->middleware() komutu ile yeni bir katman eklediğimizde eklenen katman en dıştaki yeni katman olur ve varsa bir önceki dış katmanı yada uygulamanın kendisini kuşatır.


### Http Katmanları Nasıl Kullanılır ?

Uygulama sınıfı içerisindeki <b>$c['app']->middleware('katman');</b> metodu uygulamaya dinamik olarak bir katman ekler. Yeni katman bir önceki katmanı kuşatır, eğer bir önceki katman yoksa uygulamanın kendisini kuşatılmış olur.


Örnek bir katman ile katmanlara merhaba diyelim.

Eğer hello katmanını global olarak uygulamanın her yerinde çalıştırmak istiyorsak onu aşağıdaki gibi <b>app/middlewares.php</b> dosyası içerisine eklememiz gerekir.

```php
$c['app']->middleware(new Http\Middlewares\Hello);
```

Http\Middlewares\Hello.php dosyasının içeriği


```php
namespace Http\Middlewares;

use Obullo\Application\Middleware;

class Hello extends Middleware
{
    public function load()
    {
        $this->c['response']->write('<pre>Hello im a <b>before</b> middleware of load() method.</pre>');

        $this->next->load();

        $this->c['response']->write('<pre>Hello im a <b>after</b> middleware of load() method.</pre>');
    }

    public function call()
    {
        $this->c['response']->write('<pre>Hello im a <b>before</b> middleware of index method.</pre>');

        $this->next->call();

        $this->c['response']->write('<pre>Hello im a <b>after</b> middleware of index method.</pre>');
    }
}

/* Location: .Http/Middlewares/Hello.php */
```

Şimdi uygulamanızın ilk açılış sayfasına gidip çıktıları kontrol edin. Normal şartlarda yukarıdakileri yaptı iseniz 4 adet hello çıktısı almanız gerekir.


### Katmanları Route Yapısına Tutturmak

Eğer katmanların sadece belirli url adreslerine özgü olmasını istiyorsanız <b>app/routes.php</b> dosyasında bir route grubu oluşturup katmanları bu gruba atamanız gerekir. Bunun için grup konfigürasyonu içerisinde middleware anahtarı kullanarak mevcut gruba birden fazla katman ekleyebilirsiniz. Unutulmaması gereken en önemli nokta katmanları <b>$this->attach();</b> metodu içerisinde düzenli ifadelerden yaralanarak ( regular expressions ) katmanın çalışması gereken url adresine tutturulması kısmıdır.

Bunun için size aşağıda bir örnek hazırladık.

```php
$c['router']->group(
    [
        'name' => 'GenericUsers',
        'domain' => $c['config']['domain']['mydomain.com'],
        'middleware' => ['Maintenance']
    ],
    function () {
        $this->defaultPage('welcome');
        $this->attach('(.*)'); // Attach middleware to all pages of this group
    }
);
```

Yukarıdaki örnekte maintenance katmanı $this->attach('(.*)'); metodu ile geçerli domain e ait grubun tüm sayfalarına atanmış oldu.

Proje ana dizininde iken konsolunuza <kbd>php task domain down --name=root</kbd> komutunu kullanarak maintenance filtresinin çalışıp çalışmadığını kontrol edebilirsiniz. <kbd>php task domain up --name=root</kbd> komutu ile tekrar web siteniz maintenance modundan çıkıp gezilebilir hale gelecektir.


Yetkilendirme kontrolü ile igili diğer bir örneği yine sizin için hazırladık.

```php
$c['router']->group(
    [
        'name' => 'AuthorizedUsers',
        'domain' => $c['config']['domain']['mydomain.com'], 
        'middleware' => ['Auth', 'Guest']
    ],
    function () {
        $this->defaultPage('welcome');
        $this->attach('welcome/restricted'); // Attach middleware just for this url
    }
);
```

Yukarıdaki örnekte de <b>Guest</b> katmanı kullanılarak <b>welcome/restricted</b> sayfasına oturum açmamış kullanıcıların girmesi engellenmiştir. Guest katmanı diğer katmanlar gibi <b>app/classes/Http/Middlewares/</b> klasörü içerisinde yer alır ve call() seviyesinde <b>$this->user->identity->guest()</b> metodu ile kullanıcının yetkisi olup olmadığını kontrol eder.

Kontrol sonucunda yetkisi olmayan kullanıcılar login sayfasına yönlendirilirler.

### Uygulama Katmanları

Uygulamaya katmanları <b>Obullo/Application/Middlewares</b> klasörü içerisinde yeralan daha önceden uygulama ihtiyaçlarına göre hazırlanmış katman özellikleridir ( Traits ). Uygulama katmanları <kbd>app/classes/Http/Middlewares</kbd> dizinindeki katmanlar içerisinden <b>use</b> komutu ile çağrılırlar.

Aşağıdaki Maintenance katmanına bir gözatalım.

```php
namespace Http\Middlewares;

use Obullo\Container\Container;
use Obullo\Application\Middleware;
use Obullo\Application\Middlewares\UnderMaintenanceTrait;

class Maintenance extends Middleware
{
    use UnderMaintenanceTrait;

    public function load()
    {
        $this->domainIsDown();

        $this->next->load();
    }

    public function call()
    {
        $this->next->call();
    }
}
```

Yukarıda maintenance katmanında görüldüğü gibi <b>use</b> komutu ile UnderMaintenanceTrait eklentisi çağırılarak <b>$this->domainIsDown();</b> metoduna genişledik. Sizde uygulamanıza özgü eklentileri filtreler içerisinden bu yöntemle çağırabilirsiniz.

## Katmanlar

#### Maintenance Katmanı

> Maintenance eklentisi uygulamanıza ait domain adreslerini bakıma alma özelliği sunan popüler bir eklentidir. 

##### Konfigürasyon

Eğer tanımlı değilse <kbd>app/config/$env/domain.php</kbd> dosyası içerisinden uygulamanıza ait domainleri ve bu domainlere ait regex ( düzenli ) ifadeleri belirleyin.

```php

return array(

    'root' => array(
        'maintenance' => 'up',
        'regex' => null,
    ),
    'mydomain.com' => array(
        'maintenance' => 'up',
        'regex' => '^mydomain.com$',
    ),
    'sub.domain.com' => array(
        'maintenance' => 'up',
        'regex' => '^sub.domain.com$',
    ),
);

/* End of file */
/* Location: ./var/www/framework/app/config/env/local/domain.php */
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

>**Not:** Dosyadaki ilk anahtar olan **root** anahtarını değiştirmemeniz gerekir bu anahtar kök anahtardır ve uygulamanın tümünü kapatıp açmak için kullanılır.

##### Kurulum

```php
php task middleware add --name=Maintenance
```

##### Kaldırma

```php
php task middleware remove --name=Maintenance
```

Eğer app/routes.php içinde bu katmanı kullandıysanız middleware dizileri içinden silin.

##### Çalıştırma

Uygulamanızı bakıma almak için aşağıdaki komutu çalıştırın.

```php
php task domain down --name=root
```

Uygulamanızı bakımdan çıkarmak için aşağıdaki komutu çalıştırın.


```php
php task domain up --name=root
```

#### Auth Katmanı

> Başarılı oturum açmış ( yetkinlendirilmiş ) kullanıcılara ait katmandır. 

##### Konfigürasyon

Eğer tanımlı değilse <kbd>app/config/$env/domain.php</kbd> dosyası içerisinden uygulamanıza ait domainleri ve bu domainlere ait regex ( düzenli ) ifadeleri belirleyin.

##### Çalıştırma

Uygulamanıza giriş yapmış kullanıcılara ait bir katman oluşması için belirli bir route grubu yaratıp Auth katmanını middleware anahtarı içerisine aşağıdaki gibi eklemeniz gerekir.
Son olarak route grubu içerinsinde <b>$this->attach()</b> metodunu kullanarak yetkili kullanıcılara ait sayfaları bir düzenli ifade ile belirleyin.


```php
$c['router']->group(
    [
        'name' => 'AuthorizedUsers',
        'domain' => $c['config']['domain']['mydomain.com'], 
        'middleware' => array('Auth','Guest')
    ],
    function () {

        $this->defaultPage('welcome');
        $this->attach('accounts/.*');
    }
);
```

Yukarıdaki örnekte <b>modules/accounts</b> klasörü içerisindeki tüm sayfalarda <b>Auth</b> ve <b>Guest</b> katmanları çalışır.

#### Guest Katmanı

> Oturum açmamış ( yetkinlendirilmemiş ) kullanıcılara ait bir katman oluşturur. Bu katman auth paketini çağırarak kullanıcının sisteme yetkisi olup olmadığını kontrol eder ve yetkisi olmayan kullanıcıları sistem dışına yönlendirir. Genellikle route yapısında Auth katmanı ile birlikte kullanılır.

##### Konfigürasyon

Eğer tanımlı değilse <kbd>app/config/$env/domain.php</kbd> dosyası içerisinden uygulamanıza ait domainleri ve bu domainlere ait regex ( düzenli ) ifadeleri belirleyin.
<kbd>app/classes/Service/User.php</kbd> dosyası auth servis sağlayıcısından <b>url.login</b> anahtarının login dizinine göre konfigüre edin.

```php
class User implements ServiceInterface
{
    public function register(Container $c)
    {
        $c['user'] = function () use ($c) {

            $user = new AuthServiceProvider(
                $c,
                [
                    'cache.key'        => 'Auth',
                    'url.login'        => '/membership/login',
                    .
                    .
                    .
                ]
            );
            return $user;
        };
    }
}
```

##### Çalıştırma

Bir route grubu yaratıp Guest katmanını middleware anahtarı içerisine aşağıdaki gibi eklemeniz gerekir. Son olarak route grubu içerinsinde <b>$this->attach()</b> metodunu kullanarak yetkili kullanıcılara ait sayfaları bir düzenli ifade ile belirlendiğinde katman çalışmaya başlar.


```php
$c['router']->group(
    [
        'name' => 'AuthorizedUsers',
        'domain' => $c['config']['domain']['mydomain.com'], 
        'middleware' => array('Auth','Guest')
    ],
    function () {

        $this->defaultPage('welcome');
        $this->attach('accounts/.*');
    }
);
```

Yukarıdaki örnekte <b>modules/accounts</b> klasörü içerisindeki tüm sayfalarda <b>Auth</b> ve <b>Guest</b> katmanları çalışır.


#### MethodNotAllowed Katmanı

> Uygulamaya gelen Http isteklerine göre metot türlerini filtrelemeyi sağlar. Belirlenen http metotları ( get, post, put, delete ) dışında bir istek gelirse isteği HTTP Error 405 Method not allowed sayfası ile engeller.

##### Konfigürasyon

Framework çekirdeğinde çalışan bir filtredir herhangi bir kurulum ve konfigürasyon gerektirmez. Dipnotlar ile kullanılabilmesi için <kbd>app/config/$env/config.php</kbd> dosyasından <b>annotations > enabled</b> anahtarının <b>true</b> olması gerekir.

##### Çalıştırma

Dipnotlar ( annotations ) ile controller sınıfı içerisinden veya route yapısı içerisinden çalıştırılabilir.

###### Dipnotlar ile Controller sınıfı içerisinden çalıştırma

```php
/**
 * Index
 *
 * @middleware->method("get", "post");
 * 
 * @return void
 */
public function index()
{
    // ..
}


/* End of file welcome.php */
/* Location: .modules/welcome/controller/welcome.php */
```

<kbd>http://project/hello</kbd> sayfasına post ve delete haricinde örneğin bir get isteği geldiğinde bu istek engellenecektir.

###### Route yapısı içerisinden çalıştırma

```php
$c['router']->group(
    ['name' => 'GenericUsers','domain' => $c['config']['domain']['mydomain.com'], 'middleware' => array()],
    function () {

        $this->defaultPage('welcome');

        $this->match(['post', 'delete'], 'hello$', 'welcome/index');
    }
);
```

Yukarıdaki örnekte <kbd>/hello</kbd> adresine yalnızca <b>POST</b> ve <b>DELETE</b> http istek yöntemleriyle erişilebilir.

Şimdi <b>/hello</b> adresini ziyaret ettiğinizde bir "HTTP Error 405 Method Not Allowed" hatası almamız gerekir.

#### Request Katmanı

> Uygulamaya gelen Http isteklerinin tümünü evrensel olarak filtrelemeyi sağlayan çekirdek katmandır.

##### Konfigürasyon

Framework çekirdeğinde çalışan bir filtredir herhangi bir kurulum ve konfigürasyon gerektirmez fakat gelen istekleri doğru filtreleyebilmesi için önemlilik sırasına göre en en başta çalışması gerekir. Bu nedenle diğer katmanlardan önce yani <kbd>app/middlewares.php</kbd> dosyası içerisinde <b>en son satırda</b> tanımlanmış olmalıdır.

> **Not:** Http katmanlarında önemlilik sırası en yüksek olan katman en son tanımlanandır.

```php
$c['app']->middleware(new Http\Middlewares\Request);

/* End of file middlewares.php */
/* Location: .middlewares.php */
```

##### Çalıştırma

<kbd>app/classes/Http/Request.php</kbd> dosyasını açın ve kullanmak istediğiniz <b>Trait</b> sınıflarını katmanınıza dahil edin.

```php
class Request extends Middleware
{
    use BenchmarkTrait;

    public function load()
    {
        $this->next->load();
    }

    public function call()
    {
        $this->benchmarkStart();

        $this->next->call();

        $this->benchmarkEnd();
    }
}
```

#### Https Katmanı

> Uygulamada belirli adreslere gelen <b>http://</b> isteklerini <b>https://</b> protokolüne yönlendirir.

##### Kurulum

```php
php task middleware add --name=Https
```

##### Kaldırma

```php
php task middleware remove --name=Https
```

Eğer route yapınızda bu katmanı kullandıysanız app/routes.php dosyasından ayrıca silin.
Eğerbu katmana ait anotasyonlar kontrolör sınıfları üzerinde kullanıldıysa bir <b>Search - Replace</b> operasyonu ile ilgili anotasyonları silin.

##### Çalıştırma

Aşağıdaki örnek tek bir route için https katmanı tayin etmenizi sağlar.

```php
$c['router']->get('hello$', 'welcome/index')->middleware(['Https']);
```

Fakat uygulamada birden fazla güvenli adresiniz varsa onları aşağıdaki gibi bir grup içinde tanımlamak daha doğru olacaktır.

```php
$c['router']->group(
    ['name' => 'Secure', 'domain' => 'framework', 'middleware' => array('Https')],
    function () {

        $this->get('orders/pay');
        $this->get('orders/bank_transfer');
        $this->get('hello$', 'welcome/index');
        $this->attach('.*');
    }
);
```

#### Translation Katmanı

> Uygulamaya gelen http isteklerinin tümü için <b>locale</b> anahtarlı çereze varsayılan yerel dili yada url den gönderilen dili kaydeder.

##### Konfigürasyon

Uygulamanın tüm isteklerinde evrensel olarak çalışan bir katmandır. <kbd>app/middlewares.php</kbd> dosyası içerisinde tanımlanması gerekir.

> **Not:** Http katmanlarında önemlilik sırası en yüksek olan katman en son tanımlanandır.

```php
/*
|--------------------------------------------------------------------------
| Translations
|--------------------------------------------------------------------------
*/
$c['app']->middleware(new Http\Middlewares\Translation);

/*
|--------------------------------------------------------------------------
| Request
|--------------------------------------------------------------------------
*/
$c['app']->middleware(new Http\Middlewares\Request);

/* End of file middlewares.php */
/* Location: .app/middlewares.php */
```

Ayrıca translation paketinin konfigürasyon dosyası <kbd>app/config/translator.php</kbd> dosyasını konfigüre etmeyi unutmayın.

```php
return array(

    'locale' => [
        'default'  => 'en',  // Default selected.
    ],

    'fallback' => [
        'enabled' => false,
        'locale' => 'es',
    ],

    'uri' => [
        'segment'       => true, // Uri segment number e.g. http://example.com/en/home
        'segmentNumber' => 0       
    ],

    'cookie' => [
        'name'   =>'locale',               // Translation value cookie name
        'domain' => $c['env']['COOKIE_DOMAIN.null'], // Set to .your-domain.com for site-wide cookies
        'expire' => (365 * 24 * 60 * 60),  // 365 day
        'secure' => false,                 // Cookie will only be set if a secure HTTPS connection exists.
        'httpOnly' => false,               // When true the cookie will be made accessible only 
        'path' => '/',                     // through the HTTP protocol
    ],

    'languages' => [
                        'en' => 'english', // Available Languages
                        'de' => 'deutsch',
                        'es' => 'spanish',
                        'tr' => 'turkish',
                        'fr' => 'french',
                    ],

    'debug' => false, // Puts 'translate:' texts everywhere


/* End of file translator.php */
/* Location: ./app/config/translator.php */
```

##### Kurulum

```php
php task middleware add --name=Translation
```

##### Kaldırma

```php
php task middleware remove --name=Translation
```

Ayrıca <kbd>app/middlewares.php</kbd> dosyası içerisinden katmanı silin.
Varsa <kbd>app/routes.php</kbd> dosyasından ilgili route ları kaldırın.


##### Çalıştırma

Yerel dilin doğru seçilebilmesi için herbir route grubunuza aşağıdaki gibi desktelenen dilleri içeren (?:en|tr|de) gibi bir yazım kuralı eklemeniz gerekir.

```php
$c['router']->group(
    [
        'name' => 'GenericUsers',
        'domain' => $c['config']['domain']['mydomain.com'],
        'middleware' => array('Maintenance')
    ],
    function () {

        $this->defaultPage('welcome');

        $this->get('(?:en|tr|de|nl)/(.*)', '$1');  // For dynamic url requests http://example.com/en/welcome
        $this->get('(?:en|tr|de|nl)', 'welcome');  // For default page request http://example.com/en

        $this->attach('.*');
    }
);
```

Uygulamanızı <kbd>http://myproject/en/welcome</kbd> gibi ziyaret ettiğinizde yerel dil <b>locale</b> adlı çereze <b>en</b> olarak kaydedilecektir.

Artık geçerli yerel dili <kbd>$this->c['translator']->getLocale()</kbd> fonksiyonu ile çağırabilirsiniz.


#### RewriteLocale Katmanı

> Bu katman uygulamaya <b>http://example.com/welcome</b> olarak gelen istekleri mevcut yerel dili ekleyerek <b>http://example.com/en/welcome</b> adresine yönlendirir.

##### Kurulum

```php
php task middleware add --name=RewriteLocale
```

##### Kaldırma

```php
php task middleware remove --name=RewriteLocale
```

Eğer route yapınızda bu katmanı kullandıysanız app/routes.php dosyasından ayrıca silin.

##### Çalıştırma

Aşağıdaki örnek genel ziyaretçiler route grubu için RewriteLocale katmanını çalıştırır.

```php
$c['router']->group(
    [
        'name' => 'GenericUsers', 
        'domain' => $c['config']['domain']['mydomain.com'], 
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

#### Csrf Katmanı

> Csrf katmanı Cross Request Forgery güvenlik tehdidine karşı uygulamanızdaki formlarda oluşturduğunuz güvenlik algoritmasını http POST istekleri geldiğinde sunucu tarafında doğrular, doğrulama başarılı olmazsa katman içerisinden kullanıcı hata sayfasına yönlendirilir.

Cross Request Forgery güvenlik tehdidi hakkında daha detaylı bilgi için <a href="http://shiflett.org/articles/cross-site-request-forgeries">bu makalaye</a> gözatabilirsiniz.

##### Konfigürasyon

<kbd>app/config/security.php</kbd> dosyasından csrf protection değerini true olarak değiştirin.

```php
return array(
            
    'csrf' => [                      
        'protection' => true,
     ],                                 

);

/* End of file config.php */
/* Location: .app/config/security.php */

```

Eğer Form/Element paketini kullanmıyorsanız uygulamanızdaki tüm form taglarına aşağıdaki gibi güvenlik değeri oluşturmanız gerekir.

```html
<form action="/buy" method="post">
<input type="hidden" name="<?php echo $this->c['csrf']->getTokenName() ?>" 
value="<?php echo $this->c['csrf']->getToken(); ?>" />
</form>
``` 

Eğer form element paketini kullanıyorsanız open metodu sizin için csrf değerini kendiliğinden oluşturur.


```php
echo $this->formElement->open('/buy', array('method' => 'post'));
```

Csrf hidden input alanı olmayan bir form istiyorsanız en son parametreyi false göndererek input alanının otomatik eklenmesini engelleyebilirsiniz.

```php
echo $this->formElement->open('/dummy', array('method' => 'post'), array(), $protection = false);
```

##### Kurulum

```php
php task middleware add --name=Csrf
```

##### Kaldırma

```php
php task middleware remove --name=Csrf
```

Katmanı ayrıca <kbd>app/middlewares.php</kbd> dosyasından kaldırmanız gerekir.

##### Çalıştırma

Csrf doğrulama katmanının uygulamanın her yerinde çalışmasını istiyorsanız katmanı <kbd>app/middlewares.php</kbd> dosyasına ekleyin.

```php
/*
|--------------------------------------------------------------------------
| Csrf
|--------------------------------------------------------------------------
*/
$c['app']->middleware(new Http\Middlewares\Csrf);
/*
|--------------------------------------------------------------------------
| Request
|--------------------------------------------------------------------------
*/
$c['app']->middleware(new Http\Middlewares\Request);

/* End of file middlewares.php */
/* Location: .app/middlewares.php */
```

Katman evrensel olarak eklendiğinde tüm http POST isteklerinde çalışır. Fakat çalışmasını <b>istemediğiniz</b> metotlarda katmanı aşağıdaki gibi anotasyonlar ( annotations ) yardımı ile kaldırabilirsiniz.

```php
/**
 * Update
 *
 * @middleware->remove("Csrf");
 * 
 * @return void
 */
public function update()
{
    if ($this->c['request']->isPost()) {

        // Form verilerini işle
    }
}
```

Eğer Csrf katmanının uygulamanın sadece belirli yerlerinde doğrulama istiyorsanız aşağıdaki gibi katman ismini <kbd>app/routes.php</kbd> dosyasına ekleyin.

```php
$c['router']->group(
    [
        'name' => 'Membership', 
        'domain' => $c['config']['domain']['mydomain.com'], 
        'middleware' => array('Maintenance')
    ],
    function () use ($c) {

        $this->match(['get', 'post'], 'accounts/orders/post')->middleware("Csrf");
        $this->match(['get', 'post'], 'accounts/orders/delete')->middleware("Csrf");

        $this->get('accounts/orders/list');

        $this->attach('.*');
    }
);
```

Bu örnekte sadece üye hesapları modülü altında siparişler sınıfına ait post metodunda csrf katmanını çalıştırmış olduk.