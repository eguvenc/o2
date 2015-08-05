
## Auth Katmanları

Auth katmanları uygulamanız içerisinde <kbd>app/classes/Http/Middlewares/</kbd> klasörü altında bulunan <b>Auth.php</b> ve <b>Guest.php</b> dosyalarıdır. Auth katmanı uygulamaya giriş yapmış olan kullanıcıları kontrol ederken Guest katmanı ise uygulamaya giriş yetkisi olmayan kullanıcıları kontrol eder. Auth ve Guest katmanlarının çalışabilmesi için route yapınızda middleware anahtarına ilgili modül için birkez tutturulmaları gerekir.

### Auth Katmanı

> Başarılı oturum açmış ( yetkinlendirilmiş ) kullanıcılara ait katmandır. 

<a name="auth-configuration"></a>

#### Konfigürasyon

Eğer tanımlı değilse <kbd>config/$env/domain.php</kbd> dosyası içerisinden uygulamanıza ait domainleri ve bu domainlere ait regex ( düzenli ) ifadeleri belirleyin.

### Çalıştırma

Uygulamanıza giriş yapmış kullanıcılara ait bir katman oluşması için belirli bir route grubu yaratıp Auth katmanını middleware anahtarı içerisine aşağıdaki gibi eklemeniz gerekir.
Son olarak route grubu içerisinde <b>$this->attach()</b> metodunu kullanarak yetkili kullanıcılara ait sayfaları bir düzenli ifade ile belirleyin.


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

Yukarıdaki örnekte <b>modules/accounts</b> klasörü içerisindeki tüm sayfalarda <b>Auth</b> ve <b>Guest</b> katmanları çalışır. Attach metodu içerisinde düzenli ifadeler kullanabilirsiniz.

### Guest Katmanı

> Oturum açmamış ( yetkinlendirilmemiş ) kullanıcılara ait bir katman oluşturur. Bu katman auth paketini çağırarak kullanıcının sisteme yetkisi olup olmadığını kontrol eder ve yetkisi olmayan kullanıcıları sistem dışına yönlendirir. Route yapısında Auth katmanı ile birlikte kullanılır.

#### Konfigürasyon

Eğer tanımlı değilse <kbd>config/$env/domain.php</kbd> dosyası içerisinden uygulamanıza ait domainleri ve bu domainlere ait regex ( düzenli ) ifadeleri belirleyin.
<kbd>app/classes/Service/User.php</kbd> dosyası auth servis sağlayıcısından <b>url.login</b> anahtarını login dizinine göre konfigüre edin.

```php
class User implements ServiceInterface
{
    public function register(Container $c)
    {
        $c['user'] = function ($params = ['table' => 'users']) use ($c) {

            $parameters = [
                'cache.key'     => 'Auth',
                'db.adapter'    => '\Obullo\Authentication\Adapter\Database',
                'db.model'      => '\Obullo\Authentication\Model\User', 
                'db.provider'   => 'database',
                'db.connection' => 'default',
                'db.tablename'  => $params['table'],
            ];
            $manager = new AuthManager($c);
            $manager->setParameters($parameters);

            return $manager;
        };
    }
}
```

Guest katmanına bir örnek.


```php
namespace Http\Middlewares;

use Obullo\Application\Middleware;
use Obullo\Authentication\AuthConfig;

class Guest extends Middleware
{
    protected $user;

    public function __construct()
    {
        $this->user = $this->c->get(
            'user',
            [
                'table' => AuthConfig::session('db.tablename')
            ]
        );
    }

    public function call()
    {
        if ($this->user->identity->guest()) {

            $this->flash->info('Your session has been expired.');
            $this->url->redirect(AuthConfig::get('url.login'));
        }
        $this->next->call();
    }
    
}
/* Location: .app/classes/Http/Middlewares/Guest.php */
```


#### Çalıştırma

Bir route grubu yaratıp Guest katmanını middleware anahtarı içerisine aşağıdaki gibi eklemeniz gerekir. Son olarak route grubu içerisinde <b>$this->attach()</b> metodunu kullanarak yetkili kullanıcılara ait sayfalar bir düzenli ifade ile belirlendiğinde katman çalışmaya başlar.


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


### Tekil Oturum Açma Özelliği

Tekil oturum açma özelliği opsiyonel olarak kullanılır. Http Auth katmanı içerisinde bu özellik çağrıldığında birden fazla aygıtta yada birbirinden farklı tarayıcılarda oturum açıldığında açılan tüm önceki oturumlar sonlanır ve en son açılan oturum aktif kalır.

UniqueLogin özelliği opsiyoneldir ve <kbd>config/auth.php</kbd> konfigürasyon dosyasından kapatılıp açılabilir.

```php

return array(

    'middleware' => [
        'uniqueLogin' => true
    ]
);

/* Location: .config/auth.php */
```

 UniqueLoginTrait sınıfı Auth http katmanı içerisinden çağrılarak kullanılır. Tekil oturum açma özelliğinin tam olarak çalışabilmesi için Auth katmanı içerisinde <kbd>$this->uniqueLoginCheck()</kbd> metodunun aşağıdaki gibi kullanılıyor olması gerekir.

```php
namespace Http\Middlewares;

use Obullo\Application\Middleware;
use Obullo\Authentication\AuthConfig;
use Obullo\Authentication\Middleware\UniqueLoginTrait;

class Auth extends Middleware
{
    use UniqueLoginTrait;

    protected $user;

    public function __construct()
    {
        $this->user = $this->c->get(
            'user',
            [
                'table' => AuthConfig::session('db.tablename')
            ]
        );
    }

    public function call()
    {
        if ($this->user->identity->check()) {
    
            $this->uniqueLoginCheck();  // Terminate multiple logins
            
            // $this->user->activity->set('last', time());

        }
        $this->next->call();
    }
    
}

/* Location: .app/classes/Http/Middlewares/Auth.php */
```

User servisi katman içerisinde aşağıdaki çağırılarak auth sınfı check metodu ile kullanıcının yetkisi kontrol edilir. Eğer kullanıcının yetkisi varsa uniqueLoginCheck metodu ile oturumun tekil olup olmadığı kontrol edilir oturum tekil değilse kullanıcının diğer oturumları yok edilir ve en son giriş yapılan oturum açık kalır.


> **Not:** UniqueLogin özelliği opsiyoneldir ve <kbd>config/auth.php</kbd> konfigürasyon dosyasından kapatılıp açılabilir.

```php
namespace Http\Middlewares;

use Obullo\Application\Middleware;
use Obullo\Authentication\AuthConfig;
use Obullo\Authentication\Middleware\UniqueLoginTrait;

class Auth extends Middleware
{
    use UniqueLoginTrait;

    protected $user;

    public function __construct()
    {
        $this->user = $this->c->get(
            'user',
            [
                'table' => AuthConfig::session('db.tablename')
            ]
        );
    }

    public function call()
    {
        if ($this->user->identity->check()) {
            $this->uniqueLoginCheck();  // Çoklu açılan oturumları yok et
        }
        $this->next->call();
    }   
}

/* Location: .app/classes/Http/Middlewares/Auth.php */
```

### User Servisi Neden Tablo İsmine Göre Yaratılıyor ?

Auth sorgularının esnek olması için her defasında login controller içerisinde konfigüre edilen tablo parametresi AuthConfig sınıfı içerisinden session verisine kaydedilir. Yine AuthConfig sınıfından geçerli tablo ismi alınarak User servisi bu tablo ismine göre dinamik olarak yaratılır.

```php
$this->user = $this->c->get('user', ['table' => AuthConfig::session('db.tablename')]);
```

Böylelikle projenizde bir başka üyelik tablosu için yetkilendirme yapmak istediğinizde sadece bu tablo ismi için aşağıdaki gibi yeni bir fonksiyon ve yeni bir view dosyası yaratarak farklı bir tablo için yetkilendirme yapabilirsiniz.

```php
namespace Membership;

use Obullo\Authentication\AuthConfig;

class Login extends \Controller
{
    const USERS  = 'users';
    const ADMINS = 'admins';

    /**
     * Users login 
     * 
     * @event->when("post")->subscribe('Event\Login\Attempt');
     *
     * @return void
     */
    public function index()
    {
        $this->user = $this->c->get('user', ['table' => static::USERS]);

        if ($this->request->isPost()) {

            $this->validator->setRules('email', 'Email', 'required|email|trim');
            $this->validator->setRules('password', 'Password', 'required|min(6)|trim');

            if (! $this->validator->isValid()) {
                $this->form->setErrors($this->validator);
            } else {

                $authResult = $this->user->login->attempt(
                    [
                        AuthConfig::get('db.identifier') => $this->validator->getValue('email'), 
                        AuthConfig::get('db.password')   => $this->validator->getValue('password'),
                    ],
                    $this->request->post('rememberMe')
                );

                if ($authResult->isValid()) {
                    $this->flash->success('You have authenticated successfully.')
                        ->url->redirect('membership/restricted');
                } else {
                    $this->form->setResults($authResult->getArray());
                }
            }
        }
        $this->view->load('login');
    }

    /**
     * Admin login
     * 
     * @event->when("post")->subscribe('Event\Login\Attempt');
     *
     * @return void
     */
    public function admin()
    {
        $this->user = $this->c->get('user', ['table' => static::ADMINS]);

        if ($this->request->isPost()) {

            $this->validator->setRules('email', 'Email', 'required|email|trim');
            $this->validator->setRules('password', 'Password', 'required|min(6)|trim');

            if (! $this->validator->isValid()) {
                
                ...

            } else {

                //  login attemtp
            }
        }
        $this->view->load('admin');
    }
}

}
```

Yukarıdaki düzenlemeden sonra projenizde iki farklı üye girişi kapısı yaratılmış olur.


Standart kullanıcılar aşağıdaki gibi bir adresten giriş yaparken

```php
http://example.com/membership/login/index
```

Yönetici seviyesindeki kullanıcılar aşağıdaki gibi farklı bir adresten giriş yapabilirler.

```php
http://example.com/membership/login/admin
```