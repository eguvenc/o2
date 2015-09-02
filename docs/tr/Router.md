
## Router Sınıfı

Router sınıfı uygulamanızda index.php dosyasına gelen istekleri <kbd>app/routes.php</kbd> dosyanızdaki route tanımlamalarına göre url yönlendirme, http katmanı çalıştırma, http isteklerini filtreleme gibi işlevleri yerine getirir.

<ul>

<li>
    <a href="#configuration">Konfigürasyon</a>
    <ul>
        <li><a href="#index.php">Index.php</a></li>
        <li><a href="#default-page">Varsayılan Açılış Sayfası</a></li>
        <li><a href="#404-errors">404 Hata Yönetimi</a></li>
    </ul>
</li>


<li>
    <a href="#running">Çalıştırma</a>
    <ul>
        <li><a href="#loading-service">Servisi Yüklemek</a></li>
        <li><a href="#url-rewriting">Url Yönlendirme</a></li>
        <li><a href="#modules">Modüller</a></li>
    </ul>
</li>

<li>
    <a href="#routing">Route Kuralları Oluşturmak</a>
    <ul>
        <li><a href="#route-types">İstek Türleri</a></li>
        <li><a href="#regex">Düzenli İfadeler</a></li>
        <li><a href="#closures">İsimsiz Fonksiyonlar</a></li>
        <li><a href="#parameters">Parametreler</a></li>
        <li><a href="#route-groups">Route Grupları</a></li>
        <li><a href="#sub-domains">Alt Alan Adları ve Gruplar</a></li>
        <li><a href="#regex-sub-domains">Alt Alan Adları ve Düzenli İfadeler</a></li>
    </ul>
</li>

<li>
    <a href="#middlewares">Http Katmanları</a>
    <ul>
        <li><a href="#route-types">-</a></li>
    </ul>
</li>



</ul>

<a name="configuration"></a>

### Konfigürasyon

Router sınıfı url yönlendirmelerini çalıştırabilmek için geçerli <b>kök domain</b> adresini bilmek zorundadır. Domain adresini aşağıdaki gibi tanımlayabilir,

```php
$c['router']->domain('example.com');
```

ya da ana konfigürasyon dosyasından gelmesini sağlayabilirsiniz.

```php
$c['router']->domain($c['config']['url']['webhost']); 
```

Kök domain adresinizi başında <b>"www."</b> öneki olmadan girin.

```php
$c['router']->domain('myproject.com'); 
```

<a name="index.php"></a>

#### Index.php dosyası

Bu dosyanın tarayıcıda gözükmemesini istiyorsanız bir <kbd>.htaccess</kbd> dosyası içerisine aşağıdaki kuralları yazmanız yeterli olacaktır.

```php
RewriteEngine on
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteCond $1 !^(index\.php|assets|robots\.txt)
RewriteRule ^(.*)$ ./index.php/$1 [L,QSA]
```
<a name="default-page"></a>

#### Varsayılan Açılış Sayfası

Url değerine hiçbirşey girilmediğinde varsayılan açılış sayfası konfigüre edilmişse uygulama ilk açılışta bu sayfaya yönlendirilir. Routes.php içerisinde en tepede ilan edilmelidir.

```php
$c['router']->defaultPage('main/home');
```

Eğer varsayılan sayfa konfigüre edilmemişse <kbd>welcome/welcome</kbd> sayfası görüntülenir.


<a name="404-errors"></a>

#### 404 Hata Yönetimi

Eğer 404 sayfa bulanamadı hatalarını sizin belirlediğiniz bir modül tarafından yönetilmesini istiyorsanız error404 metodunu kullanabilirsiniz.

```php
$c['router']->defaultPage('welcome');
$c['router']->error404('errors/page_not_found');
```

BU tanımlamadan sonra herhangi bir 404 hatası oluştuğunda uygulama <kbd>errors/</kbd> modülü altında <kbd>page_not_found</kbd> kontrolör dosyasını çalıştırır.

<a name="running"></a>

### Çalıştırma

Servis konteyner içerisinden çağırıldığında tanımlı olan router metotlarına ulaşılmış olur. 

<a name="loading-service"></a>

#### Servisi Yüklemek

```php
$this->c['router']->method();
```

<a name="url-rewriting"></a>

#### Url Yönlendirme

Tipik olarak bir URL girdisi ile ve ona uyan dizin arasında (<kbd>dizin/sınıf/metot/argümanlar</kbd>)  birebir ilşiki vardır. Bir URI içerisindeki bölümler aşağıdaki kalıbı izler.

```php
example.com/dizin/sınıf/metot/id
```

Aşağıdaki URL takip eden dizin yapısındaki dosyayı çalıştırır.

```php
example.com/index.php/shop/product/1
```

Birebir ilişkili kontrolör sınıfınının görünümü

```php
- modules
  - shop
      Product.php
```

Fakat bazı durumlarda bu birebir ilişki yerine farklı <kbd>dizin/sınıf/method</kbd> ilişkisi yeniden kurgulanmak istenebilir. Örnek vermek gerekirse mevcut URL adreslerinizin aşağıdaki gibi olduğunu varsayalım.

```php
example.com/shop/product/1/
example.com/shop/product/2/
```

Normalde URL nin 2. bölümü sınıf ismi için rezerve edilmiştir (show), fakat yukarıdaki örnekte <b>shop</b> bölümünü silip <b>product</b> değerleri ile gönderilen URL biçimine dönüştürmek (url rewriting) için bir route kuralı tanımlamanız gerekir.


![Akış Şeması](images/router-flowchart.png?raw=true)


Birinci bölümü <b>product</b> olan bir URL ve ikinci bölümü herhangi bir değer alabilen bölüm <b>shop</b> dizinine yönlendirilir product sınıfı çalıştırılarak sonraki gönderilen değerler argüman olarak çalıştırılır.  

> **Not:**  Varsayılan metot her zaman <b>index</b> metodudur fakat açılış sayfasında bu metodu yazmanıza gerek kalmaz. Eğer argüman göndermek zorundaysanız (example.com/product/index/1 gibi.) bu durum da index metodunu yazmanız gerekir.

<a name="modules"></a>

#### Modüller

Alt klasörleri olan ve ana dizinleri kapsayan dizinlere modül adı verilir. Bir modüle ulaşmak için modül adı URL adresinden girilmelidir.

```php
example.com/admin/membership/login/index
```

Aşağıdaki örnekte <b>shop</b> klasörü bir dizindir, admin klasörü ise bir modüldür ve diğer dizinleri kapsar.

```php
- modules
    - shop
      + view
        Product.php
    - admin
        - membership
            + view
              - Login.php
              - Logout.php
        + dashboard
    + views

```

Modülün çözümlenebilmesi için kontrolörler içerisindeki <b>namespace</b> değeri aşağıdaki olmalıdır.

```php
namespace Admin\Membership;

class Login extends \Controller
{
    public function index()
    {
        // ..
    }
}
```

<a name="routing"></a>

### Route Kuralları Oluşturmak

Uygulama içerisindeki tüm route kuralları <kbd>app/routes.php</kbd> dosyası içerisinde tutulur.

<b>GET kuralı</b> - example.com/welcome/ örnek url adresine gelen http sget isteklerini girilen değere yönlendirir.

```php
$c['router']->get('welcome(.*)', 'home/index/$1');
```

Route kuralları <kdd>düzenli ifadeler</kdd> (regex) yada <kbd>/wildcards</kbd> kullanılarak tanılanabilir.

<b>POST kuralı</b> - example.com/welcome/ örnek url adresine gelen http post isteklerini girilen değere yönlendirir.

```php
$c['router']->post('welcome/(.+)', 'home/index/$1');
```

<b>Birden fazla http isteğini kabul etmek</b> ( GET, POST, DELETE, PUT ve diğerleri )

```php
$c['router']->match(['get','post'], 'welcome/(.+)', 'home/index/$1');
```

yukarıdaki örnekte eğer bir URL "welcome/$arg/$arg .." değerini içeriyorsa gelen argümanlar "home/home/index/$arg" yani home dizini içerisinde home sınıfı index metoduna gönderilir.

<a name="route-types"></a>

#### İstek Türleri

Route kuralları yazıldığında aynı zamanda http isteklerini istek tipine göre filtrelemeyi sağlar. Aşağıdaki tablo route kuralları için mevcut http metotlarını gösteriyor.

<table>
  <thead>
    <tr>
    <th>Metot</th>
    <th>Açıklama</th>
    <th>Örnek</th>
    </tr>
  </thead>
  <tbody>
    <tr>
    <td>post</td>
    <td>Bir route kuralının sadece POST isteğinde çalışmasını sağlar.</td>
    <td>$c['router']->post($url, $rewrite)</td>
    </tr>
    <tr>
    <td>get</td>
    <td>Bir route kuralının sadece GET isteğinde çalışmasını sağlar.</td>
    <td>$c['router']->get($url, $rewrite)</td>
    </tr>
    <tr>
    <td>put</td>
    <td>Bir route kuralının sadece PUT isteğinde çalışmasını sağlar.</td>
    <td>$c['router']->put($url, $rewrite)</td>
    </tr>
    <tr>
    <td>delete</td>
    <td>Bir route kuralının sadece DELETE isteğinde çalışmasını sağlar.</td>
    <td>$c['router']->delete($url, $rewrite)</td>
    </tr>
    <tr>
    <td>match</td>
    <td>Bir route kuralının sadece girilen istek tiplerinde çalışmasını sağlar.</td>
    <td>$c['router']->match(['get','post'], $url, $rewrite)</td>
    </tr>
  </tbody>
</table>

<a name="regex"></a>

#### Düzenli İfadeler

Eğer regex yani düzenli ifadeler kullanmayı tercih ediyorsanız route kuralları içerisinde herhangi bir düzenli ifadeyi referans çağırımlı (back-references) olarak kullanabilirsiniz.

> **Not:** Eğer referans çağırımı kullanıyorsanız çift backslash kullanmak yerine dolar $ işareti kullanmanız gerekir.

Tipik bir referanslı regex örneği.

```php
$c['router']->get('([0-9]+)/([a-z]+)', 'welcome/$1/$2');
```

Yukarıdaki örnekte <kbd>example.com/1/test</kbd> adresine benzer bir URL <kbd>Welcome/welcome</kbd> kontrolör sınıfı index metodu parametresine <kbd>1 - 2</kbd> argümanlarını gönderir.

<a name="closures"></a>

#### İsimsiz Fonksiyonlar

Route kuralları içerisinde isimsiz fonksiyonlar da kullanabilmek mümkündür.

```php
$c['router']->get(
    'welcome/[0-9]+/[a-z]+', 'welcome/$1/$2', 
    function () use ($c) {
        $c['view']->load('dummy');  //  .modules/welcome/view/dummy.php
    }
);
```

Bu örnekte, <kbd>example.com/welcome/123/test</kbd> adresine benzer bir URL <kbd>Welcome/welcome</kbd>  kontrolör sınıfı index metodu parametresine <kbd>123 - test</kbd> argümanlarını gönderir.

<a name="parameters"></a>

#### Parametreler

Eğer girilen bölümleri fonksiyon içerisinden belirli kriterlere göre parametreler ile almak istiyorsanız süslü parentezler { } kullanın.

```php
$c['router']->get(
    'welcome/{id}/{name}', null,
    function ($directory, $id, $name) use ($c) {
        $c['response']->show404($directory.'-'.$id.'-'.$name);
    }
)->where(['id' => '([0-9]+)', 'name' => '([a-z]+)']);
```

Yukarıdaki örnekte <kbd>/welcome/index/123/test</kbd> adresine benzer bir URL <kbd>where()</kbd> fonksiyonu içerisine girilen kriterlerle uyuştuğunda isimsiz fonksiyon içerisine girilen fonksiyonu çalıştırır.

```php
welcome/index/123/test
```

```php
$c['router']->get(
    '{id}/{name}/{any}', 'home/home/index/$1/$2/$3',
    function ($id, $name, $any) use ($c) {
        echo $id.'-'.$name.'-'.$any;
    }
)->where(array('id' => '([0-9]+)', 'name' => '([a-z]+)', 'any' => '(.+)'));
```

Bu örnekte ise <kbd>{id}/{name}/{any}</kbd> olarak girilen URI şeması <kbd>product/index/123/electronic/mp3_player/</kbd> adresine benzer bir URL ile uyuştuğunda girdiğiniz düzenli ifade ile değiştirilir ve rewrite değerine <kbd>home/home/index/123/electronic/mp3_player/</kbd> olarak girdiğiniz URL argümanları isimsiz fonksiyona parametre olarak gönderilir.

Kısacası yukarıdaki route kuralının çalışabilmesi için aşağıdaki gibi bir URL çağırılması gerekir.

```php
welcome/index/123/test/parameters/
```

Gelişmiş bir örnek:

```php
$c['router']->get(
    'shop/{id}/{name}', null,
    function ($directory, $id, $name) use ($c) {
        
        $db = $c['app']->provider('database')->get(['connection' => 'default']);
        $db->prepare('SELECT * FROM products WHERE id = ?');
        $db->bindValue(1, $id, PARAM_INT);
        $db->execute();

        if ($db->row() == false) {
            $c['response']->showError(
                sprintf(
                  'The product %s not found',
                  $name
                )
            );
        }
    }
)->where(array('id' => '([0-9]+)', 'name' => '([a-z]+)'));
```

Bu örnekte ise <kbd>shop/{id}/{name}</kbd> olarak girilen URI şeması eğer <kbd>/shop/123/mp3_player</kbd> adresine benzer bir URL ile eşleşirse where() metodu içerisine girdiğiniz düzenli ifade ile değiştirilir ve tarayıcıdan girilen parametreler isimsiz fonksiyona gönderilir. Eğer parametre olarak alınan ID değeri veritabanı içerisinde sorgulanır ve bulunamazsa bir hata mesajı gösterilir.

<a name="route-groups"></a>

#### Route Grupları

Route grupları bir kurallar bütününü topluca yönetmenizi grup kuralları belirli <b>alt domainler</b> için çalıştırılabildiği gibi belirli <b>http katmanlarına</b> da tayin edilebilirler. Örneğin tanımladığınız route grubunda belirlediğiniz http katmanlarının çalışmasını istiyorsanız grup tanımlamalarına katman isimlerini girdikten sonra <kbd>$this->attach()</kbd> metodu ile katmanı istediğiniz URL adreslerine tuturmanız gerekir. Birden fazla katman middleware dizisi içine girilebilir.

```php
$c['router']->group(
    ['name' => 'test', 'middleware' => array('methodNotAllowed')],
    function () {

        $this->attach('welcome');
        $this->attach('welcome/test');
    }
);
```

Bu tanımlamadan sonra eğer buna benzer bir URL <kbd>/welcome</kbd> çağırırsanız <b>methodNotAllowed</b> katmanı çalışır ve aşağıdaki hata ile karşılaşırsınız.

```php
Http Error 405 Get method not allowed.
```

> **Not:** Route gurubu seçeneklerine isim (name) değeri girmek zorunludur.

<a name="sub-domains"></a>

#### Alt Alan Adları ve Gruplar

Eğer bir gurubu belirli bir alt alan adına tayin ederseniz grup içerisindeki route kuralları yalnızca bu alan adı için geçerli olur. Aşağıdaki örnekte <kbd>shop.example.com</kbd> alan adı için bir grup tanımladık.

```php
$c['router']->group(
    array('name' => 'shop', 'domain' => 'shop.example.com'), 
    function () {

        $this->defaultPage('welcome');

        $this->get('welcome/(.+)', 'home/index', null);
        $this->get('product/{id}', 'product/list/$1', null);
    }
);
```

Tarayıcınızdan bu URL yi çağırdığınızda bu alt alan adı için tanımlanan route kuralları çalışmaya başlar.

```php
http://shop.example.com/product/123
```

Aşağıda <kbd>account.example.com</kbd> adlı bir alt alan adı için kurallar tanımladık.

```php
$c['router']->group(
    array('name' => 'accounts', 'domain' => 'account.example.com'), 
    function () {

        $this->get(
            '{id}/{name}/{any}', 'user/account/$1/$2/$3',
            function ($id, $name, $any) {
                echo $id.'-'.$name.'-'.$any;
            }
        )->where(array('id' => '([0-9]+)', 'name' => '([a-z]+)', 'any' => '(.+)'));
    }
);
```

Tarayıcınızdan aşağıdaki gibi bir URL çağırdığınızda bu alt alan adı için yazılan kurallar çalışmış olur.


```php
http://account.example.com/123/john/test
```

<a name="regex-sub-domains"></a>

#### Alt Alan Adları ve Düzenli İfadeler

Alt alan adlarınızda eğer <kbd>sports19.example.com</kbd>, <kbd>sports20.example.com</kbd>, <kbd>sports21.example.com</kbd> gibi değişen sayılar mevcut ise alan adı kısmında düzenli ifadeler kullanarak route grubuna alan adınızı tayin edebilirsiniz.

```php
$c['router']->group(
    array('name' => 'sports', 'domain' => 'sports.*\d.example.com', 'middleware' => array('maintenance')),
    function () {

        $this->defaultPage('welcome');
        $this->attach('.*');
    }
);
```

<a name="middlewares"></a>

### Http Katmanları

You can define your custom route filters from filters.php

In order to understand how a filter works, let’s break one down by look at one of the most important, the authentication filter:

```php

```

#### Bir Kurala Katman Atamak

Once you have the filter set up, you need to attach it to a route in order for it to take effect.

To attach a filter, simply pass it as an argument in the array of the second argument of a Route method definition:

```php
$c['router']->attach('tutorials/hello_world', array('auth'));
```

Using attach method after routes

```php
$c['router']->get(
    'welcome/(.*)', null,
    function () use ($c) {
        $c['view']->load('dummy');
    }
)->middleware('auth');
```

#### Bir Gruba Katman Atamak


```php
$c['router']->group(
    array('name' => 'shop', 'domain' => 'shop.example.com', 'middleware' => array('hello')), 
    function ($group) {
        $this->get('welcome/.+', 'tutorials/hello_world', null);
        $this->get('product/{id}', 'product/list/$1', null);

        $this->attach('.*'); // attach to all urls 
    }
);
```

#### Bir Grup İçinden Katman Atamak

Aşağıdaki örnek tek bir iz için katmanlar tayin etmenizi sağlar.

$router->match(['get', 'post'], 'hello$', 'welcome/index')->middleware(['Https']);

Eğer çok fazla güvenli adresleriniz varsa onları aşağıdaki gibi tanımlamak daha mantıklı olacaktır.


```php
$c['router']->group(
    ['name' => 'Secure', 'domain' => 'framework', 'middleware' => array('Https')],
    function () {

        $this->match(['get', 'post'], 'orders/pay')->middleware('Csrf');
        $this->match(['post'], 'orders/pay/post')->middleware('Csrf');
        
        $this->get('orders/bank_transfer');
        $this->get('hello$', 'welcome/index');
    }
);
```

> **Not:** middleware(); fonksiyonu her bir route isteğine bir katman eklemenizi sağlar fakat gruba tayin edilen aynı isimde zaten genel bir katman var ise bu durumda route isteğine birer birer katman atamanız anlamsız olur böyle bir durumda ilgili katman uygulamaya yanlışlıkla iki kez eklenmiş olur. Bu yüzden birer birer atanabilecek katman isimleri grup opsiyonu içerisinde kullanılmamalıdır.

### Dosya Kısıtlama Örneği

```php
http://www.example.com/test/bad_segment
http://www.example.com/test/good_segment1
http://www.example.com/test/good_segment2

$this->attach('^(test/(?!bad_segment).*)$');
```

#### Translation Katmanı

```php
$c['router']->group(
    array('name' => 'locale', 'domain' => '^www.example.com$|^example.com$', 'middleware' => array('locale')),
    function () {

        $this->defaultPage('welcome');
        $this->get('(?:en|tr|de|nl)/(.*)', '$1', null);  // Dispatch request for http://example.com/en/folder/class
        $this->get('(?:en|tr|de|nl)', 'welcome/index',  null);  // if request http://example.com/en  -> redirect it to default controller

        $this->attach('/');         // Filter only works for below the urls
        $this->attach('welcome');
        $this->attach('sports/.*');
        $this->attach('support/.*');
    }
);
```


#### Creating Maintenance Filters


#### Creating Https Filter


.... coming soon.


#### Routes.php Referansı

------

##### $c['router']->domain(string $domain);

Sets a your default domain.

##### $c['router']->defaultPage(string $uri);

Sets your default controller.

##### $c['router']->error404(string $uri);

Sets your error controller.

##### $c['router']->match(array $methods, string $match, string $rewrite, $closure = null)

Girilen http istek metotlarına göre bir iz yaratır, istek metotları get,post,put ve delete metotlarıdır.

##### $c['router']->get(string $match, string $rewrite, $closure = null)

Creates a http GET based route.

##### $c['router']->post(string $match, string $rewrite, $closure = null)

Creates a http POST based route.

##### $c['router']->put(string $match, string $rewrite, $closure = null)

Creates a http PUT based route.

##### $c['router']->delete(string $match, string $rewrite, $closure = null)

Creates a http DELETE based route.

##### $c['router']->group(array $options, $closure);

Creates a route group.

##### $c['router']->where(array $replace);

Replaces your route schema with arguments.


#### Middleware Referansı

------

##### $c['router']->attach(string $route)

Geçerli grubun katmanlarını girilen ize tutturur.

##### $c['router']->match(['get','post'], '/')->middleware(array $middlewares);

En son yazılan http izine girilen katmanları tutturur.


#### Sınıf Referansı

------

##### $this->router->getHost();

Gets current domain name.

##### $this->router->getDomain();

Returns domain name configured in routes.php

##### $this->router->fetchModule();

Gets the currently working module name.

##### $this->router->fetchDirectory();

Gets the currently working directory name.

##### $this->router->fetchClass();

Gets the currently working directory name.