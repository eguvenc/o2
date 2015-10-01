
# Obullo Container

Obullo Container PHP 5.4 ve üzeri sürümler için ortam tabanlı bağımlılık enjeksiyon sınıfıdır. Uygulamanızda bileşenler ve servis sağlayıcıları oluşturabilmeyi sağlar.

### Kurulum

Demo dosyası altında örnek uygulamayı çalıştırabilmek için composer.json dosyasınıza <kbd>app/classes</kbd> dizini için aşağıdaki gibi bir yükleyici tanımlayın.

```php
{
    "autoload": {
        "psr-4": {
            "": "app/classes"
        }
    }
}
```

Container paketini yükleyin.

```php
composer require obullo/container
```

Yükleyicilerin sorunsuz çalışabilmesi için composer autoload önbelleğini temizleyin.

```php
composer dump-autoload
```

### Demo Projeyi Çalıştırmak

<kbd>demo/app</kbd> ve <kbd>demo/index.php</kbd> dosyalarını aşağıdaki gibi proje ana dizinine kopyalayın.

```php
- myproject
    - app
        - classes
            - Service
                - Logger
                    Local.php
                    Production.php
                + Provider
                Foo.php
    - vendor
        - obullo
            + container
    composer.json
    index.php
```

```php
http://myproject/index.php
```

Proje ana dizinindeki index.php dosyanızı çalıştırın.

### Nasıl Çalışıyor ?

Konteyner bir servis yükleyici kullanarak servis klasöründeki tüm servisleri tanımlayabilir yada servisleri tek tek el ile tanımlayabilirsiniz.

#### Servis Yükleyicisi

Php servis yükleyici sınıfını kullanarak php biçimindeki servis dosyalarını otomatik yükleyebilirsiniz.

```php
$loader = new \Obullo\Container\Loader\PhpServiceLoader;
$loader->registerPath('app/classes/');
$loader->registerFolder('Service');
```

Otomatik yükleyici kullanıldığında <kbd>Service</kbd> klasörü olarak tanımladığınız klasör içerisinden dosya biçimindeki servis isimleri taranarak bir dizi içerisine toplanırlar ve loader sınıfı ile konteyner a enjekte edilirler.

```php
$c = new \Obullo\Container\Container($loader);
```

Eğer dosya biçiminde servisler kullanmak istemiyorsanız yada dinamik olarak bir servis tanımlamanız gerekiyorsa servisleri el ile de tek tek tanımlayabilirsiniz.

```php
$c['myclass'] = function () {
    return new MyClass;
}
```

Tanımlanan servisler aşağıdaki gibi çağırılırlar.

```php
$c['foot'];  // foo servisi
$c['myclass'];  // myclass servisi
```

Eğer yükleyici kullanmak istemezseniz konteyner içerisine hiçbirşey göndermeyin.

```php
$c = new \Obullo\Container\Container;
```

#### Dosya Tabanlı Servisler Yaratmak

Demo uygulamanız içerisindeki <kbd>app/classes/Service</kbd> dizini içerisine servis dosyalarınızı kaydedin. Aşağıda Foo.php adlı servis dosyası örneği gösteriliyor.

```php
namespace Service;

use Obullo\Container\ServiceInterface;
use Obullo\Container\ContainerInterface;

class FooClass {
    function bar()
    {
        return "Hello foo service !";
    }
}
class FooManager {
    function getClass()
    {
        return new FooClass;
    }
}
class Foo implements ServiceInterface
{
    public function register(ContainerInterface $c)
    {
        $c['foo'] = function () use ($c) {
            $manager = new FooManager($c);
            return $manager->getClass();
        };
    }
}
```

Yukarıdaki örnekte <kbd>FooManager</kbd> sınıfı <kbd>Foo</kbd> servisi içerisinden gerçek <kbd>Foo</kbd> sınıfını kontrol etmeyi sağlar.

#### Ortam Değişkenini Belirlemek

Dosya biçimindeki ortam tabanlı servislerin çalışabilmesi için setEnv metodu ile konteyner sınıfına ortam değişkeninin gönderilmesi gerekir.

```php
$c->setEnv('local');
```

#### Servisleri Çözümlemek

Tanımlanan servisler konteyner içerisinden ArrayAccess yönetimi ile çözümlenerek uygulamanızda kullanılabilir hale gelirler.

```php
$foo = $c['foo'];
echo $foo->bar();  // Hello foo service !
```

Obullo konteyner Get metodunu da destekler.

```php
$foo = $c->get('foo');
echo $foo->bar();  // Hello foo service !
```

Eğer paylaşımlı olan bir servisin yeni değişken değerleriyle (new instance) ilan edilmesini istiyorsanız raw komutunu kullanın. 

```php
$foo = $c['foo'];  // old foo
$foo = $c['foo'];  // old foo

$foo = $c->raw('foo');  // new foo
$newFoo = $foo();

echo $newFoo->bar();  // Hello foo service !
```

#### Ortam Tabanlı Bir Servisi Çözümlemek

Bazı servis dosyalarının farklı ortam değişkenlerinde farklı davranışlar sergilemesi istenebilir. Eğer böyle bir durum söz konusu servisinizi bir klasör altında herbir ortam için ayrı tanımlamanız gerekir.

Aşağıdaki örnekte <kbd>local</kbd> ve <kbd>production</kbd> ortamlarında farklı konfigürasyon parametrelerini kullanan logger örneği gösteriliyor.

```php
- app
    - classes
        - Service
            - Logger
                Local.php
                Production.php
```

Yukarıdaki gibi Logger isimili bir dizin altında logger servisini tanımlayın. Çevre ortamı local iken servis parmetrelerini çıktılayın. 

```php
$logger = $c['logger'];
$logger->debug("Test");

print_r($logger->getParameters());

// 'Logger service example configuration for local environment !'
```

<kbd>index.php</kbd> dosyanızıdan çevre ortamını <kbd>production</kbd> olarak değiştirin.

```php
$c->setEnv('production');
```
ve servis parmetrelerini çıktılayın. 

```php
print_r($logger->getParameters());

// 'Logger service example configuration for production environment !'
```

### Bir Uygulama Sınıfı Yaratın !

Eğer Obullo çerçevesi kullanıyorsanız aşağıdaki örneği yapmanıza gerek kalmaz eğer başka bir uygulama kullanıyorsanız servis sağlayıcıları ve bileşenler oluşturabilmek için aşağıdaki gibi bir uygulama sınıfı yaratın.

```php
class Application {

    protected $c;
    protected $registered = array();
    protected $connections = array();
    protected static $dependencies = array('config', 'test');

    public function __construct($c)
    {
        $this->c = $c;
    }
    public function register($providers)
    {
        foreach ((array)$providers as $name => $namespace) {
            $this->registered[$name] = $namespace;
        }
        return $this;
    }
    public function provider($name)
    {
        $msg = "%s provider is not registered, "
        $msg.= "please register it using \$c['app']->register() method.";
        $name = strtolower($name);
        if (! isset($this->registered[$name])) {
            throw new \RuntimeException(
                sprintf(
                    $msg,
                    ucfirst($name)
                )
            );
        }
        $Class = $this->registered[$name];
        $connector = Obullo\Container\ServiceProviderConnector::getInstance();
        $connector->setContainer($this->c);
        $connector->setClass($Class);
        return $connector;
    }
    public function component(array $namespaces)
    {   
        foreach ($namespaces as $name => $Class) {
            $this->c[$name] = function () use ($Class, $name) {
                $Class = '\\'.ltrim($Class, '\\');
                $reflector = new ReflectionClass($Class);
                if (! $reflector->hasMethod('__construct')) {
                    return $reflector->newInstance();
                } else {
                    $deps = $this->getDependencies($reflector, $name);
                    return $reflector->newInstanceArgs($deps);
                }
            };
        }
    }
    protected function getDependencies(\ReflectionClass $reflector, $component)
    {
        $parameters = $reflector->getConstructor()->getParameters();
        $params = array();
        foreach ($parameters as $parameter) {
            $d = $parameter->getName();
            if ($d == 'c' || $d == 'container') {
                $params[] = $this->c;
            } else {
                $isComponent = in_array($d, static::$dependencies);
                if ($isComponent) {
                    $params[] = $this->c[$d];
                } else {

                    if ($isComponent) {
                        throw new RuntimeException(
                            sprintf(
                                'Dependency is missing for "%s" package. <pre>%s $%s</pre>',
                                $component,
                                $parameter->getClass()->name,
                                $d
                            )
                        );
                    }
                }
            }
        }
        return $params;
    }
}

//  Registering Your Application Class

$c['app'] = function () use ($c) {
    return new Application($c);
};
```

Uygulama sınıfız artık hazır şimdi servis sağlayıcıları ve bilşenlerinizi oluşturabilirsiniz.

### Servis Sağlayıcıları

Bir servis sağlayıcısı yazımlıcılara uygulamada kullandıkları yinelenen farklı konfigürasyonlara ait parçaları uygulamanın farklı bölümlerinde güvenli bir şekilde tekrar kullanabilmelerine olanak tanır. Bağımsız olarak kullanılabilecekleri gibi bir servis konfigürasyonunun içerisinde de kullanılabilirler.

Uygulamada kullanılan servis sağlayıcısı bir <b>bağlantı yönetimi</b> ile ilgili ise farklı parametreler gönderilerek açılan bağlantıları yönetirler ve her yazılımcının aynı parametreler ile uygulamada birden fazla bağlantı açmasının önüne geçerler.

Bir servis sağlayıcısı sınıfı yanlış yazılmış yada yapılandırılmış ise onu uygulamanızda kullandığınız bölümlerin hepsi yanlış çalışmaya başlar. Bu yüzden servis sağlayıcıları bir uygulama çalışırken en kritik rolü üstlenirler ve değişmez olmaları önerilir.

Daha fazla bilgi için Obullo <a href="https://github.com/obullo/service" target="_blank">servis</a> paketine gözatabilirsiniz.

#### Servis Sağlayıcılarını Tanımlamak

Demo uygulamanızda kendi servis sağlayıcılarınızı <kbd>app/classes/Service/Provider</kbd> klasörü altında tutabilirsiniz.

```php
$c['app']->register(
    [
        'redis' => 'Service\Provider\Redis',
        'memcached' => 'Service\Provider\Memcached',
   ]
);
```

Servis sağlayıcılar aşağıdaki gibi app sınıfı içerisinden tanımlanırlar.


```php
$c['app']->register(
    [
        'database' => 'Obullo\Service\Provider\Database',
        // 'database' => 'Obullo\Service\Provider\DoctrineDBAL',
        // 'qb' => 'Obullo\Service\Provider\DoctrineQueryBuilder',
        'cache' => 'Obullo\Service\Provider\Cache',
        'redis' => 'Obullo\Service\Provider\Redis',
        'memcached' => 'Obullo\Service\Provider\Memcached',
        // 'memcache' => 'Obullo\Service\Provider\Memcache',
        'amqp' => 'Obullo\Service\Provider\Amqp',
        // 'amqp' => 'Obullo\Service\Provider\AmqpLib',
        'mongo' => 'Obullo\Service\Provider\Mongo',
    ]
);

// Location: .app/providers.php
```

#### Bir Servis Sağlayıcısını Çözümlemek

Servis sağlayıcıları bir uygulamada aşağıdaki gibi application <kbd>provider</kbd> metodu iler çağırılırlar. Aşağıdaki örnekte default veritabanı konfigürasyonuna ait bağlantı elde ediliyor.

```php
$redis = $c['app']->provider('redis')->get(['connection' => 'default']);

var_dump($redis);
```

Bu örnekte ise redis sınıfına ait second adlı bağlantı alınıyor.

```php
$redis = $c['app']->provider('redis')->get(['connection' => 'second']);

var_dump($redis);
```

Memcached servis sağlayıcısı için aşağıda bir başka örnek gösteriliyor.

```php
$memcached = $c['app']->provider('memcached')->get(['connection' => 'default']);

var_dump($memcached);
```


```php
$memcached = $c['app']->provider('memcached')->get(['connection' => 'default']);

var_dump($memcached);
```

Daha fazla bilgi için Obullo <a href="https://github.com/obullo/service" target="_blank">servis</a> paketini inceleyin.

### Bileşenler

Bileşenler uygulama içerisinde sıklıkla kullanılan ve birbirlerine bağımlılıkları olan sınıflardır. Bileşenler servisler gibi çözümlenir ve konteyner içerisine kaydedilirler. Servislerde olduğu gibi herhangi bir sınıf bileşen haline getirilebilir.

#### Bileşenleri Tanımlamak

```php
$c['app']->component(
    [
        'config' => 'Component\Config',
        'test' => 'Component\Test',
    ]
);
```

Varsa bileşen bağımlılıklarını Application sınıfı içerisinde tanımlayın.

```php
class Application {
    protected static $dependencies = array('config', 'test');

```

Eğer bir bileşenin <kbd>__construct()</kbd> metodu parametreleri, uygulama sınıfı <kbd>$dependencies</kbd> değişkeni içerisinde tanımlı bileşenlerden biri ile uyuşuyorsa bu sınıfın bağımlılıkları otomatik olarak bu sınıfa enjekte edilir. Konteyner sınıfının bir sınıfa enjekte edilebilmesi için parametreyi <kbd>$c</kbd> olarak tanımlamanız yeterli olur.

```php
namespace Component;

class Test {

    /**
     * Container
     * 
     * @var object
     */
    protected $c;

    /**
     * Config

     * @var object
     */
    protected $config;

    /**
     * We inject container to every components if you use 
     * parameter name as "c" or "container".
     * 
     * @param object $c      container
     * @param object $config config component
     * 
     * @return void
     */
    public function __construct($c, $config)
    {
        $this->c = $c;
        $this->config = $config;
    }
}
```

#### Bir bileşeni çözümlemek

Bileşenler servisler gibi çözümlenir ve konteyner içerisine kaydedilirler. Örneğin demo projenizde tanımlı olan config bileşenini elde etmek için konteyner içerisinden sadece bileşen adını <kbd>config</kbd> olarak yazmanız gerekir.

```php
$config = $c['config'];
var_dump($config);
```

Bağımlılıkları test etmek için demo uygulamanızda test bileşeni getConfig() metodunun çalıştırın.

```php
$c['test']->getConfig();  

// "Hello im test component and my dependecies are 
// "Component/Config" and "obullo/Container/Container" classes";
```

Container ile artık servisler ve servis sağlayıcılarını destekleyen uygulamalar yaratmaya hazırsınız.