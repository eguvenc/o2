
## Çok Katmanlı Programlama ( Layers yada HMVC )

Çok katmanlı programlama tekniği hiyerarşik kontrolör programlama kalıbından türetilmiş ( bknz. <a href="http://www.javaworld.com/article/2076128/design-patterns/hmvc--the-layered-pattern-for-developing-strong-client-tiers.html" target="_blank">Java Hmvc.</a> ) uygulamanızı ölçeklenebilir hale getirmek için kullanılan bir tasarım kalıbıdır. Çok katmanlı mimari MVC katmanlarını bir üst-alt hiyerarşisi içerisinde çözümler. Uygulama içerisinde tekrarlayan bu model yapılandırılmış bir client-tier mimarisi sağlar.

![Layers](/Layer/Docs/images/layers.png?raw=true "Layered Programming")

Her bir katman basit kontrolör sınıflarıdır. Layer sınıfı tarafından tekralanabilir olarak çağrılabilen katmanlar uygulamayı parçalayarak farklı işlevsel özellikleri bileşen yada web servisleri haline getirirler.

### Katmanlı Mimariyi Kullanmak

Katmanlı mimari sunum ( presentation ) katmanınının yazılım geliştirme sürecini etkileyici bir şekilde yönetir. Bu mimariyi kullanmanın faydalarını aşağıdaki gibi sıralayabiliriz.

* Arayüz Tutarlılığı: Katmanlı programlama görünen varlıkları ( views ) kesin parçalara ayırır ve her bölüm kendisinden sorumlu olduğu fonksiyonu çalıştırır ( view controller ) böylece her katman bir layout yada widget hissi verir.
* Bakımı Kolay Uygulamalar: Parçalara bölünen kullanıcı arayüzü bileşenleri MVC tasarım desenine bağlı kaldıkları için bakım kolaylığı sağlarlar.
* Mantıksal Uygulamalar: Katmanlar birbirleri ile etkişim içerisinde olabilecekleri gibi uygulama üzerinde hakimiyet ve önbelleklenebilme özellikleri ile genişleyebilir mantıksal uygulamalar yaratmayı sağlarlar. Bölümsel olarak birbirinden ayrılan katmanlar bir web servis gibi de çalışabilirler.

### Görünen Varlıkları ( views ) Katmanlar İle Oluşturmak

Aşağıdaki figürde görüldüğü gibi katmanlı mimaride görünen varlıklar parçalara ayrılarak bileşen haline getirilir. 

![Layers](/Layer/Docs/images/ui-components.png?raw=true "HMVC")


Bileşenler birbirlerinden bağımsız parçalardır ve birbirleri etkileşim içinde olabilirler. Her bir bileşen kendisi için tayin edilen bir kontrolör sınıfı tarafından yönetilir ve layer paketi aracılığı ile çağrılırlar. Ayrıca birbirinden ayrılıp bağımsız hale gelen bileşenlere dışarıda ajax yada http istekleri de gönderilebilir. Yani bir modül yada web servisi haline getirilen bu parçalara dışarıdan bir http isteği ( Curl vb. ) olmadan ulaşılabileceği gibi bir http yada ajax isteği gönderilerek de ulaşılabilir.

Örneğin bir yönetim paneline ait bir menü (navigation bar) bir katman aracılığı ile yönetilebilir. Bir kez yapılması gereken veritabanı sorguları katman aracılığı ile her bir kullanıcı için önbelleklenebilir.

### Katman Sınıfı

Layer class creates your layers then manage layer traffic and cache mechanism using with an unique id. Layer has cache service dependecy that is located in <b>app/classes/service/cache.php</b>

#### Sınıfı Yüklemek

```php
$this->c['layer']->method();
```
Konteyner nesnesi ile yüklenmesi gerekir. Layer sınıfı <kbd>app/components.php</kbd> dosyası içerisinde komponent olarak tanımlıdır.

> **Not:** Kontrolör sınıfı içerisinden bu sınıfa $this->layer yöntemi ile de ulaşılabilir.

#### Bir Katmanı Çağırmak

```php
$this->layer->get('controller/method/args', $data = array(), $expiration = 0);
```



#### A Layer request creates the random connection string ( Layer ID ) as the following steps.

*  The request method gets the uri and serialized string of your data parameters.
*  Then it builds a Layer ID with <b>unsigned Crc32 hash</b>.
*  Finally Layer ID added to end of your uri.
*  "Cache Service" use Layer ID as a <b>cache key</b> in <b>caching</b> mechanism.


### Cache Usage

```php
$this->layer->get('views/header', array('user_id' => 5), 7200);
```
Above the example will do cache for user_id = 5 parameter. ( If you use cache option you need to configure your cache driver. ( Redis, Memcache, Apc .. ) ).

## View Layers

View Layers returns to <b>raw</b> output. Framework keeps view type layers in views folder.

#### Folder Structure

```php
+ app
+ o2
- public
      - welcome
          - controller
              welcome.php
          + view
      - views
          - controller
              header.php
          - view
              header.php
```

<b>Public</b> folder are <b>visible</b> from your visitors. It contains controller folder and each layers is accessible via <b>http</b> requests.

An Example View Controller ( Header Controller )

```php
/**
 * $app header
 *
 * @var Header Controller
 */
$app = new Controller(
    function ($c) {
        $c->load('url');
        $c->load('view');
    }
);

$app->func(
    'index',
    function () {
        $navbar = array(
            'home'    => 'Home',
            'about'   => 'About', 
            'contact' => 'Contact',
            'membership/login'   => 'Login',
            'membership/signup'  => 'Signup',
        );
        foreach ($navbar as $key => $value) {
            $li.= '<li>'.$this->url->anchor($key, $value, " $active ").'</li>';
        }
        echo $this->view->load(
            'header',
            function () use ($li) {
                $this->assign('li', $li);
            },
            false
        );
    }
);

/* End of file header.php */
/* Location: .public/views/controller/header.php */
```

Above the example header controller manage your navigation bar 


## Nested Layers

You can call "layers in layers" with theirs views we call this way as nested.

```php
/**
 * $app hello_world
 * 
 * @var Controller
 */
$app = new Controller(
    function ($c) {
        $c->load('layer');
    }
);

$app->func(
    'index', 
    function () use ($c) {
        echo $this->layer->get('welcome/welcome_dummy/1/2/3');
    }
);

/* End of file hello_world.php */
/* Location: .public/tutorials/controller/hello_world.php */
```

Above the example we call the welcome layer from hello world controller.

```php
/**
 * $app welcome_dummy
 * 
 * @var Controller
 */
$app = new Controller(
    function ($c) {
        $c->load('view');
        $c->load('layer');
    }
);

$app->func(
    'index', 
    function ($arg1 = '', $arg2 = '', $arg3 = '') {
        
        echo $this->layer->get('views/header');
        echo $this->layer->get('views/footer');

        echo $this->view->nested($this)->load('dummy', false);
    }
);

/* End of file welcome_dummy.php */
/* Location: .public/tutorials/controller/welcome_dummy.php */
```

### Function Reference

------

#### $this->layer->post(string $uri, $data = array | int, expiration = 0);  

Creates $_POST request request to "public" folder.

#### $this->layer->get(string $uri, $data = array | int, expiration = 0);

Creates $_GET request to "public" folder.

#### $this->layer->method(string 'jsons/$uri', $data = array | int, expiration = 0);

Creates $_GET or $_POST request to <b>"public/jsons"</b> folder.

#### $this->layer->id();

Returns to current layer Id using your json encoded hash of your uri and method parameters.


## Layer/Flush Class

Layer flush class allows to remove cached layers from your cache storage.

### Initializing the Flush Class

------

```php
$this->layer->flush->method();
```
Once loaded, the Layer object will be available using: <dfn>$this->layer->flush->method()</dfn>


### Layer/Flush Class Function Reference

------

#### $this->layer->flush->uri(string $uri, $data = array);

Deletes cache from memory using uri and parameters.

#### $this->layer->flush->id(integer $layerId);

Deletes cache from memory by layer id.