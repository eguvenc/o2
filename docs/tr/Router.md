
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
        <li><a href="#route-group">Bir Route Grubu Oluşturmak</a></li>
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

<b>Birden fazla http istek kabul etmek</b> ( GET, POST, DELETE, PUT ve diğerleri )

```php
$c['router']->match(['get','post'], 'welcome/(.+)', 'home/index/$1');
```

yukarıdaki örnekte eğer bir URL "welcome/$arg/$arg .." değerini içeriyorsa gelen argümanlar "home/home/index" yani home dizini, home sınıfı index metoduna gönderilir.

<a name="route-types"></a>

#### İstek Türleri

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

**Note:** Splitting routes allows to us filter them.

<a name="route-group"></a>

#### Route Grubu Oluşturmak

If you prefer you can use regular expressions to define your routing rules. Any valid regular expression is allowed, as are back-references.

**Note:** If you use back-references you must use the dollar syntax rather than the double backslash syntax.

A typical RegEx route might look something like this:

```php
$c['router']->get('([0-9]+)/([a-z]+)', 'welcome/$1/$2');
```

In the above example, a URI similar to <kbd>example.com/1/test</kbd> call the <kbd>welcome</kbd> controller class index method with <kbd>1 - 2</kbd> arguments.


```php
$c['router']->get(
    'welcome/[0-9]+/[a-z]+', 'welcome/$1/$2', 
    function () use ($c) {
        $c['view']->load('dummy');  //  .modules/welcome/view/dummy.php
    }
);
```

In the above example, a URI similar to <kbd>example.com/welcome/123/test</kbd> call the <kbd>welcome</kbd> controller class index method with <kbd>123 - test</kbd> arguments.

And also your closure function run in router level.


```php
$c['router']->get(
    'welcome/{id}/{name}', null,
    function ($directory, $id, $name) use ($c) {
        $c['response']->show404($directory.'-'.$id.'-'.$name);
    }
)->where(array('id' => '([0-9]+)', 'name' => '([a-z]+)'));
```

In the above example, a URI similar to <kbd>example.com/welcome/123/test</kbd> call the <kbd>welcome</kbd> controller class and arguments belonging to our url scheme.

```php
$c['router']->get(
    '{id}/{name}/{any}', 'tutorials/hello_world/$1/$2/$3',
    function ($id, $name, $any) use ($c) {
        echo $id.'-'.$name.'-'.$any;
    }
)->where(array('id' => '([0-9]+)', 'name' => '([a-z]+)', 'any' => '(.+)'));
```

In the above example URI scheme <kbd>{id}/{name}/{any}</kbd> replaced with your regex then if correct uri matched a URI like <kbd>welcome/123/electronic/mp3_player/</kbd> rewrite your kroute as <kbd>tutorials/hello_world/123/electronic/mp3_player/</kbd> and sends arguments to your closure function.

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

In the above example URI scheme <kbd>shop/{id}/{name}</kbd> replaced with your regex then if correct uri matched a URI like <kbd>example.com/shop/123/mp3_player</kbd> sends arguments to your closure function.

### Sub-Domain Routing

Creating route group for shop.example.com domain.

```php
$c['router']->group(
    array('name' => 'shop', 'domain' => 'shop.example.com'), 
    function ($group) {
        $this->get('welcome/(.+)', 'tutorials/hello_world', null, $group);
        $this->get('product/{id}', 'product/list/$1', null, $group);
    }
);
```

Creating route group for account.example.com domain.

```php
$c['router']->group(
    array('name' => 'accounts', 'domain' => 'account.example.com'), 
    function ($group) {
        $this->get(
            '{id}/{name}/{any}', 'user/account/$1/$2/$3',
            function ($id, $name, $any) {
                echo $id.'-'.$name.'-'.$any;
            }
        )->where(array('id' => '([0-9]+)', 'name' => '([a-z]+)', 'any' => '(.+)'));
    }
);
```

### Katmanlar

You can define your custom route filters from filters.php

#### Bir Http Katmanının İşleyişi

In order to understand how a filter works, let’s break one down by look at one of the most important, the authentication filter:

```php
/*
|--------------------------------------------------------------------------
| Auth
|--------------------------------------------------------------------------
| Auth filter
*/
$c['router']->filter('auth', 'Http/Filters/AuhtFilter');
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

#### Grup Kuralları

Using a route pattern is perfect when you want to attach a filter to a very specific set of routes like above. However it’s often the case that your routes won’t fit into a nice pattern and so you would end up with multiple pattern definitions to cover all eventualities.

A better solution is to use Group Filters:

```php
$c['router']->group(
    array('name' => 'test', 'middleware' => array('auth')) 
    function ($group) {
        $this->attach('tutorials/hello_form', $group);
        $this->attach('tutorials/hello_world', $group);
    }
);
```

### Bir Gruba Katman Atamak


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

### Dosya Kısıtlama Örneği

```php
http://www.example.com/test/bad_segment
http://www.example.com/test/good_segment1
http://www.example.com/test/good_segment2

$this->attach('^(test/(?!bad_segment).*)$');
```

#### Filter Classes

Keep filters in classes make organising and maintaining your filters a lot easier.

Filter classes also use Container. This means that they will automatically be able to use dependency injection so you can very easily test that they are working correctly.

Open your filters.php file then put below the content.

```php
/*
|--------------------------------------------------------------------------
| Hello Filter
|--------------------------------------------------------------------------
| Example class filter
*/
$c['router']->filter('hello', 'Http/Filters/HelloFilter');
```

An example of a filter class could be:

```php

Class HelloFilter
{
    /**
     * Post
     * 
     * @var object
     */
    protected $post;

    /**
     * Constructor
     *
     * @param object $c container
     */
    public function __construct($c)
    {
        $this->post = $c['post'];
    }

    /**
     * Before the controller
     * 
     * @return void
     */
    public function before()
    {
        if ($this->request->isPost() AND $this->request->post('apikey') != '123456') {
            echo json_encode(
                array(
                'error' => 'Your api key is not valid'
                )
            );
            die;
        }
    }

    /**
     * After the controller
     * 
     * @return void
     */
    public function after()
    {
        // ..
    }

    /**
     * On load method of the controller
     * 
     * @return void
     */
    public function load()
    {
        // ..
    }

}
```

Then attach your filter in routes.php

```php
$c['router']->attach('tutorials/hello_world.*', array('auth'));
```

Filters allow you to very easily abstract complex route access logic into concise and easy to use nuggets of code. This allows you to define the logic once, but then apply it to many different routes.

#### Example Filter ( Language Filter )

Creating Locale filter

```php
/*
|--------------------------------------------------------------------------
| Redirect locale
|--------------------------------------------------------------------------
| Current: http://example.com/news/sports/
|
| If URL doesn't contain language abridgement 'en, tr, de, nl',
| it will be added to URL.
| 
| Then: http://example.com/en/news/sports
*/
$c['router']->filter('locale', 'Http\Filters\LocaleFilter');
```

Creating locale filter class.


```php
namespace Http\Filters;

/**
 * Locale filter
 *
 * @category  Route
 * @package   Filters
 * @author    Obullo Framework <obulloframework@gmail.com>
 * @copyright 2009-2015 Obullo
 * @license   http://opensource.org/licenses/MIT MIT license
 * @link      http://obullo.com/docs/router
 */
Class LocaleFilter
{
    /**
     * Cookie
     * 
     * @var object
     */
    protected $cookie;

    /**
     * Url
     * 
     * @var string
     */
    protected $url;

    /**
     * Constructor
     *
     * @param object $c container
     */
    public function __construct($c)
    {
        $this->url = $c->load('url');
        $this->cookie = $c->load('cookie');
    }

    /**
     * Before the controller
     * 
     * @return void
     */
    public function before()
    {
        $locale = $this->cookie->get('locale');
        $languages = $this->c['config']->load('translator')['languages'];

        if ( ! isset($languages[$locale])) {
            $locale = $this->translator->getLocale();
        }
        $this->url->redirect($locale. '/' . $this->uri->getUriString());
    }
}

// END LocaleFilter class

/* End of file LocaleFilter.php */
/* Location: .Http/Filters/LocaleFilter.php */
```

Then we attach filter to our route group.

```php
$c['router']->group(
    array('name' => 'locale', 'domain' => '^www.example.com$|^example.com$', 'middleware' => array('locale')),
    function () {

        $this->defaultPage('welcome');
        $this->get('(?:en|tr|de|nl)/(.*)', '$1', null, $group);  // Dispatch request for http://example.com/en/folder/class
        $this->get('(?:en|tr|de|nl)', 'welcome/index',  null, $group);  // if request http://example.com/en  -> redirect it to default controller

        $this->attach('/');         // Filter only works for below the urls
        $this->attach('welcome');
        $this->attach('sports/.*');
        $this->attach('support/.*');
    }
);
```

#### Using Regex For Filters

<b>Scenario:</b> We have sub domains like this <kbd>sports19.example.com</kbd> or <kbd>sports4.example.com</kbd> so we need to do maintenance page filter for <kbd>sports\d+.example.com/tutorials/hello_word</kbd> page.

Example:

```php
$c['router']->group(
    array('domain' => 'sports.*\d.example.com', 'middleware' => array('maintenance')),
    function () {

        $this->defaultPage('welcome');
        $this->attach('tutorials/hello_world.*');
    }
);
```

#### Tek Bir Route a Katman Atamak

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

#### Creating Maintenance Filters

Maintenance filters display maintenance page using configured maintenance function.

Open your filters.php file then put below the content.

```php
$c['router']->filter('maintenance', 'Http\Filters\MaintenanceFilter');
```

Then we can assign our domain to filter using attach method.

Open your routes.php file then put below the content.

```php
$c['router']->group(
    array('name' => 'general', 'domain' => $c['config']->xml()->route->all, 'filters' => array('maintenance')), 
    function ($group) {
        $this->defaultPage('welcome/index');
        $this->attach('tutorials/hello_world.*', $group); // attached to "sports" sub domain "/tutorials/hello_world/" url.
    }
);
```

Configure example for <b>All Website</b> and <b>all</b> urls.

```php
$c['router']->group(
    array('name' => 'general', 'domain' => $c['config']->xml()->route->all, 'filters' => array('maintenance')), 
    function ($group) {
        $this->defaultPage('welcome/index');
        $this->attach('.*', $group); // all urls of your domain
    }
);
```

**Note:** <b>$c['config']->xml()->route->all</b> fetches your config.xml "<app><all> .. </all<app/>" keys as <b>simpleXmlElement object</b>.


Then go to your console and type:

```php
php task route all down
```

Now your application show maintenance view for all pages.

Configure example for <b>reverse</b> urls.

```php
$c['router']->group(
    array('domain' => $c['config']->xml()->route->sports, 'filters' => array('maintenance', 'auth')), 
    function ($group) {
        $this->attach('((?!tutorials/hello_world).)*$', $group);  // all urls which not contains "tutorials/hello_world"
    }
);
```

Then go to your console and type:

```php
php task route all down
```

Now go to your console and type:

```php
php task route all up
```

Now your application "all" is up for your visitors.


#### Creating Https Filter

Open your filters.php file thn put below the content.

```php
/*
|--------------------------------------------------------------------------
| Https Filter
|--------------------------------------------------------------------------
| Force to https connection
*/
$c['router']->filter('https://', 'Http\Filters\'Https');

/* End of file filters.php */
/* Location: .filters.php */
```

And 


```php
namespace Http\Filters;

/**
 * Https filter
 *
 * @category  Route
 * @package   Filters
 * @author    Obullo Framework <obulloframework@gmail.com>
 * @copyright 2009-2015 Obullo
 * @license   http://opensource.org/licenses/MIT MIT license
 * @link      http://obullo.com/docs/router
 */
Class HttpsFilter
{
    /**
     * Container
     * 
     * @var object
     */
    protected $c;

    /**
     * Constructor
     *
     * @param object $c container
     */
    public function __construct($c)
    {
        $this->c = $c;
        $this->uri = $c['uri'];
        $this->url = $c->load('url');
        $this->router = $c['router'];
    }

    /**
     * Before the controller
     * 
     * @return void
     */
    public function before()
    {
        if ($this->c['request']->isSecure() == false) {
            $this->url->redirect('https://'.$this->router->getDomain() . $this->uri->getRequestUri());
        }
    }
}

// END HttpsFilter class

/* End of file HttpsFilter.php */
/* Location: .Http/Filters/HttpsFilter.php */
```

Then attach your filter using routes.php

```php
$c['router']->attach('tutorials/hello_world.*', array('https://'));

/* End of file routes.php */
/* Location: .routes.php */
```

Now we force <b>http://example.com/tutorials/hello_world</b> request to https:// secure connection.


### Route Reference

------

#### $c['router']->domain(string $domain);

Sets a your default domain.

#### $c['router']->defaultPage(string $pageController);

Sets your default controller.

#### $c['router']->error404(string $errorController);

Sets your error controller.

#### $c['router']->match(array $methods, string $match, string $rewrite, object $closure = null)

Girilen http istek metotlarına göre bir iz yaratır, istek metotları get,post,put ve delete metotlarıdır.

#### $c['router']->get(string $match, string $rewrite, object $closure = null)

Creates a http GET based route.

#### $c['router']->post(string $match, string $rewrite, object $closure = null)

Creates a http POST based route.

#### $c['router']->put(string $match, string $rewrite, object $closure = null)

Creates a http PUT based route.

#### $c['router']->delete(string $match, string $rewrite, object $closure = null)

Creates a http DELETE based route.

#### $c['router']->group(array $options, $closure);

Creates a route group.

#### $c['router']->where(array $replace);

Replaces your route schema with arguments.


### Middleware Reference

------

#### $c['router']->attach(string $route)

Geçerli grubun katmanlarını girilen ize tutturur.

#### $c['router']->match(['get','post'], '/')->middleware(array $middlewares);

En son yazılan http izine girilen katmanları tutturur.


### Function Reference

------

#### $this->router->getHost();

Gets current domain name.

#### $this->router->getDomain();

Returns domain name configured in routes.php

#### $this->router->fetchModule();

Gets the currently working module name.

#### $this->router->fetchDirectory();

Gets the currently working directory name.

#### $this->router->fetchClass();

Gets the currently working directory name.

#### $this->router->getAttachedRoutes();

Returns to registered middlewares.