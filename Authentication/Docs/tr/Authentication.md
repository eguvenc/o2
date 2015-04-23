
## O2 Yetki Doğrulama ( Authentication )

Yetki doğrulama paketi yetki adaptörleri ile birlikte çeşitli ortak senaryolar için size bir API sağlar. O2 yetki doğrulama yalnızca yetki doğrulama ( *authentication* ) ile ilgilidir ve yetkilendirme ( *authorization* ) ile ilgili herhangi bir şeyi içermez.

O2 yetki doğrulama; 

* Hafıza depoları, ( Storages ) 
* Adaptörler,
* Kullanıcı kimlikleri
* Çoklu ve tekil oturum açabilme
* Kullanıcı kimliklerini önbelleklenme
* Kullanıcı sorgularını özelleştirebilme ( User model class )
* Yetki doğrulama onaylandırma ( Verification )
* Oturum id sini yeniden yaratma, ( Session regenerate )
* Tarayıcı türünü doğrulama ( User agent validation )
* Hatırlatma çerezi ve beni hatırla ( Remember me token )

gibi özellikleri barındırır.

### Akış Şeması

------

Aşağıdaki akış şeması bir kullanıcının yetki doğrulama aşamalarından nasıl geçtiği ve yetki doğrulama servisinin nasıl çalıştığı hakkında size bir ön bilgi verecektir:

* [Şemayı görmek için buraya tıklayınız](/Authentication/Docs/images/flowchart.png?raw=true)

Şemada görüldüğü üzere <b>GenericUser</b> ve <b>AuthorizedUser</b> olarak iki farklı durumu olan bir kullanıcı sözkonusudur. GenericUser <b>yetkilendirilmemiş</b> AuhtorizedUser ise servis tarafından <b>yetkilendirilmiş</b> kullanıcıdır.

Akış şemasına göre GenericUser login butonuna bastığı anda ilk önce hafıza bloğuna bir sorgu yapılır ve daha önceden kullanıcının önbellekte yetkilendirilmiş kalıcı kimliği olup olmadığında bakılır eğer hafıza bloğunda kalıcı yetki doğrulama kaydı var ise kullanıcı kimliği buradan yok ise database adaptörüne sorgu yapılarak elde edilir.

Eğer kullanıcı kimliği database sorgusu yapılarak elde edilmişse elde edilen kimlik kartı performans için tekrar hafıza bloğuna yazılır.


### Sınıfları yüklemek

------

Yetki doğrulama paketi sınıflarına erişim <b>User</b> servisi üzerinden sağlanır, bu servis önceden <b>.app/classes/Service</b> dizininde <b>User.php</b> olarak konfigure edilmiştir. <b>User</b> sınıfı yetki doğrulama servisine ait olan <b>Login</b>, <b>Identity</b> ve <b>Activity</b> gibi sınıfları bu servis üzerinden kontrol eder, böylece paket içerisinde kullanılan tüm sınıf metodlarına tek bir servis üzerinden erişim sağlanmış olur.

User servisi bir kez çağrıldığı zaman bu servis içerisinden ilgili kütüphane metotları aşağıdaki gibi çalıştırılabilir.

```php
$this->c['user']->class->method();
```

Aşağıda verilen örnek prototipler size yetki doğrulama sınıfı metodlarına <b>user</b> servisi üzerinden nasıl erişim sağlandığı hakkında bir fikir verebilir.


<b>Config</b>, <b>Login</b>, <b>Identity</b> ve <b>Activity</b> sınıfları için birer örnek

```php
$this->user->config['variable'];
$this->user->login->method();
$this->user->identity->method();
$this->user->activity->method();
```

### Adaptörler

------

Yetki doğrulama adaptörleri uygulamaya esneklik kazandıran otu sorgulama arabirimleridir, yetki doğrulamanın bir veritabanı ile mi yada örnek olarak LDAP gibi bir protokol üzerinden mi yapılacağını belirleyen sınıflardır. Varsayılan arabirim türü <b>Database</b> (RDBMS or NoSQL) dir, farklı türde kimlik doğrulama arabirimleri bu sürümde henüz mevcut değildir.

Farklı adaptörlerin çok farklı seçenekler ve davranışları olması muhtemeldir , ama bazı temel şeyler kimlik doğrulama adaptörleri arasında ortaktır. Örneğin, kimlik doğrulama hizmeti sorgularını gerçekleştirmek ve sorgulardan dönen sonuçlar yetki doğrulama adaptörleri için ortak kullanılır.

### Hazıfa Depoları ( Storages )

------

Hazıfa deposu yetki doğrulama esnasında kullanıcı kimliğini ön belleğe alır ve tekrar tekrar oturum açıldığında database ile bağlantı kurmayarak uygulamanın performans kaybetmesini önler. Ayrıca yetki doğrulama onayı açıksa onaylama işlemi için geçici bir kimlik oluşturulur ve bu kimliğe ait bilgiler yine hafıza deposu aracılığıyla önbellekte tutulur.

**Not:** O2 Yetki doğrulama şu anda depolama için sadece <b>Redis</b> veritabanı ve <b>Cache</b> sürücüsünü desteklemektedir. Cache sürücüsü seçtiğinizde File, Memcache, Memcached, Apc gibi sürücüleri cache.php konfigurasyon dosyanızdan ayarlamanız gerekmektedir.

Redis veritabanını tercih ediyorsanız, Ubuntu altında redis kurulumu için <b>warmup</b> adı verilen dökümentasyon topluluğumuzun hazırladığı belgeden yararlanabilirsiniz. <a href="https://github.com/obullo/warmup/tree/master/Redis" target="_blank">Redis Kurulumu</a>.


### Redis Deposu

------

Yetki doğrulama sınıfı hafıza deposu için varsayılan olarak redis kullanır. Aşağıdaki resim kullanıcı kimliklerinin hafıza deposunda nasıl tutulduğunu göstermektedir.

![PhpRedisAdmin](/Authentication/Docs/images/redis.png?raw=true "PhpRedisAdmin")

Varsayılan hafıza sınıfı auth konfigürasyonundan değiştirilebilir.

```php
'cache' => array(

    'storage' => '\Obullo\Authentication\Storage\Redis',   // Storage driver uses cache package
    'provider' => array(
        'driver' => 'redis',
        'connection' => 'second'
    ),
)
```

### Cache Deposu

Eğer cache sürücülerini kullanmak istiyorsanız config dosyasından ayarları aşağıdaki gibi değiştirmeniz yeterli olacaktır.

```php
'cache' => array(

    'storage' => '\Obullo\Authentication\Storage\Cache',   // Storage driver uses cache package
    'provider' => array(
        'driver' => 'memcached',
        'connection' => 'default'
    ),
)
```

> Yukarıda görüldüğü gibi provider ayarlarından driver sekmesini sürücü ismi ile değiştirmeyi unutmamalısınız.


Redis dışında bir çözüm kullanıyorsanız yazmış olduğunuz kendi hafıza depolama sınfınızı auth konfigürasyon dosyasından değiştererek kullanabilirsiniz.

### Konfigürasyon

------

Yetki doğrulama paketine ait konfigürasyon <kbd>app/config/auth.php</kbd> dosyasında tutulmaktadır. Bu konfigürasyona ait bölümlerin ne anlama geldiği aşağıda geniş bir çerçevede anlatılmıştır.

#### Konfigürasyon değerleri tablosu

<table>
    <thead>
        <tr>
            <th>Anahtar</th>    
            <th>Açıklama</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td>cache[key]</td>
            <td>Bu değer auth paketinin kayıt olacağı anahtarın önekidir. Bu değeri her proje için farklı girmeniz projelerinizin karışmaması için tavsiye edilir. Örneğin bu değer "Auth:ProjectName" olarak girilebilir.</td>
        </tr>
        <tr>
            <td>cache[storage]</td>
            <td>Hazıfa deposu yetki doğrulama esnasında kullanıcı kimliğini ön belleğe alır ve tekrar tekrar oturum açıldığında database ile bağlantı kurmayarak uygulamanın performans kaybetmesini önler.Varsayılan depo Redis tir.</td>
        </tr>
        <tr>
            <td>cache[provider][driver]</td>
            <td>Hazıfa deposu içerisinde kullanılan servis sağlayıcısının hangi servis sağlayıcısına bağlanacağını belirler. Varsayılan değer "redis" değeridir. Bu konfigürasyon servis sağlayıcısı çağrıldığında <b>$c['service proviver x']->get(["connection" => "y"])</b> örneğinde <b>"x"</b> yerine gelen değerdir.</td>
        </tr>

        <tr>
            <td>cache[provider][connection]</td>
            <td>Hazıfa deposu içerisinde kullanılan servis sağlayıcısının hangi bağlantıyı kullanacağını belirler. Varsayılan değer "second" değeridir. Bu konfigürasyon servis sağlayıcısı çağrıldığında <b>$c['service proviver x']->get(["connection" => "y"])</b> örneğinde <b>"y"</b> yerine gelen değerdir.</td>
        </tr>
        <tr>
            <td>cache[block][permanent][lifetime]</td>
            <td>Oturum açıldıktan sonra kullanıcı kalıcı olarak onaylandı ise kullanıcı kimliği verileri <b>permanent</b> hafıza bloğuna kaydedilir. Kalıcı blokta ön belleğe alınan veriler kullanıcının web sitesi üzerinde hareketsiz kaldığı andan itibaren varsayılan olarak <b>3600</b> saniye sonra yok olur.</td>
        </tr>
        <tr>
            <td>cache[block][temporary][lifetime]</td>
            <td>Oturum açıldıktan sonra kullanıcı kimliği verileri <b>$this->user->identity->makeTemporary()</b> komutu ile <b>temporary</b> hafıza bloğuna taşınır. Geçici bloğa kaydedilmiş veriler <b>300</b> saniye sonrasında varsayılan olarak yok olur. Geçici blok yetki doğrulama onaylandırma durumları için tasarlanmıştır. Kimlik onayladı ise <b>$this->user->identity->makePermanent()</b> komutu ile kalıcı hale getirilmelidir.
            </td>
        </tr>
        <tr>
            <td>security[passwordNeedsRehash][cost]</td>
            <td>Bu değer Crypt/Password kütüphanesi tarafından şifre hash işlemi için kullanılır. Varsayılan değer 6 dır fakat maximum 8 ila 12 arasında olmalıdır aksi takdirde uygulamanız yetki doğrulama aşamasında performans sorunları yaşayabilir. 8 veya 10 değerleri orta donanımlı bilgisayarlar için 12 ise güçlü donanımlı ( çekirdek sayısı fazla ) bilgisayarlar için tavsiye edilir.</td>
        </tr>
        <tr>
            <td>login[rememberMe]</td>
            <td>Eğer kullanıcı beni hatırla özelliğini kullanarak giriş bilgilerini kalıcı olarak tarayıcısına kaydetmek istiyorsa  <b>__rm</b> isimli bir çerez ilk oturum açmadan sonra tarayıcısına kaydedilir. Bu çerezin sona erme süresi varsayılan olarak 6 aydır. Kullanıcı farklı zamanlarda uygulamanızı ziyaret ettiğinde eğer bu çerez ( remember token ) tarayıcısında kayıtlı ise <b>Authentication\Recaller->recallUser($token)</b> metodu çalışmaya başlar ve beni hatırla çerezi database de kayıtlı olan değer ile karşılaştırılır değerler birbiri ile aynı ise kullanıcı sisteme giriş yapmış olur. Güvenlik amacıyla her oturum açma (login) ve kapatma (logout) işlemlerinden sonra bu değer çereze ve veritabanına yeniden kaydedilir.</td>
        </tr>
        <tr>
            <td>session[regenerateSessionId]</td>
            <td>Session id nin önceden çalınabilme ihtimaline karşı uygulanan bir güvenlik yöntemlerinden bir tanesidir. Bu opsiyon aktif durumdaysa oturum açma işleminden önce session id yeniden yaratılır ve tarayıcıda kalan eski oturum id si artık işe yaramaz hale gelir.</td>
        </tr>
        <tr>
            <td>session[unique]</td>
            <td>Tekil oturum açma opsiyonu aktif olduğunda aynı kimlik bilgileri ile farklı aygıtlardan yalnızca bir kullanıcı oturum açabilir. Eklentiler klasöründeki kullandığınız eklentinin davranışına göre en son açılan oturum her zaman aktif kalırken eski oturumlar otomatik olarak sonlandırılır. Fakat bu fonksiyon <b>app/classes/Http/Middlewares</b> dizinindeki auth katmanı çalıştırıldığı zaman devreye girer. Katmanı çalıştırmak için onu <b>route</b> yapısına tutturmanız gerekmektedir. Katman içerisindeki unique session özelliği <b>Authentication/Addons</b> klasöründen çağrılarak bu sınıf içerisinden tetiklenir. Http katmanları hakkında daha geniş bilgiye <b>application</b> ve <b>router</b> paketi dökümentasyonlarını inceleyerek ulaşabilirsiniz.</td> 
        </tr>
    </tbody>
</table>


### Servis Yapılandırılması

------

Yetki doğrulama servisini kullanmadan önce servis dosyasını konfigüre etmeniz gerekir. Bu dosya database tablo ayarları yetki adaptörleri ve model gibi konfigurasyonları içerir. Bunu yapmadan önce eğer mysql benzeri ilişkili bir database kullanıyorsanız aşağıdaki sql kodunu çalıştırarak demo için bir tablo yaratın.

```sql
--
-- Table structure for table `users`
--
CREATE TABLE IF NOT EXISTS `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(100) NOT NULL,
  `password` varchar(80) NOT NULL,
  `remember_token` varchar(64) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `username` (`username`),
  KEY `remember_token` (`remember_token`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

--
-- Dumping data for table `users`
--
INSERT INTO `users` (`id`, `username`, `password`, `remember_token`) VALUES 
(1, 'user@example.com', '$2y$06$6k9aYbbOiVnqgvksFR4zXO.kNBTXFt3cl8xhvZLWj4Qi/IpkYXeP.', '');
```

Yukarıdaki sql kodu için kullanıcı adı <b>user@example.com</b> ve şifre <b>123456</b> dır.

Aşağıda görüldüğü gibi yetki doğrulama <b>User</b> servisi üzerinden yönetilir <kbd>app/classes/Service/User.php</kbd> dosyasını açarak servisi konfigüre edebilirsiniz.

```php
Class User implements ServiceInterface
{
    public function register(Container $c)
    {
        $c['user'] = function () use ($c) {

            $user = new AuthServiceProvider(
                $c,
                array(
                	'cache.key'		   => 'Auth',
                    'db.adapter'       => '\Obullo\Authentication\Adapter\Database',
                    'db.model'         => '\Obullo\Authentication\Model\User',
                    'db.provider'      => 'database',
                    'db.connection'    => 'default',
                    'db.tablename'     => 'users',    // Database column settings
                    'db.id'            => 'id',
                    'db.identifier'    => 'username',
                    'db.password'      => 'password',
                    'db.rememberToken' => 'remember_token'
                )
            );
            return $user;
        };
    }
}

// END User class

/* End of file User.php */
/* Location: .app/classes/Service/User.php */
```

**Adaptörler:** Yetki doğrulama adaptörleri yetki doğrulama servisinde <b>Database</b> (RDBMS or NoSQL) veya <b>dosya-tabanlı</b> gibi farklı türde kimlik doğrulama biçimleri olarak kullanılırlar.

**Model:** Model sınıfı yetki doğrulama sınıfına ait database işlemlerini içerir. Bu sınıfa genişleyerek bu sınıfı özelleştirebilirsiniz bunun için aşağıda database sorgularını özelleştirmek başlığına bakınız.

**Provider:** Database servis sağlayıcınızın ismidir. Database işlemlerinin hangi servis sağlayıcısının kullanması gerektiğini tanımlar.

**Connection:** Database servis sağlayıcısının hangi bağlantıyı seçmesi gerektiğini tanımlar.

**Tablo ayarları:** db.connection anahtarından sonraki diğer konfigurasyonlar database işlemleri için tablo ismi ve sütun isimlerini belirlemenize olanak sağlar. Bu konfigürasyonlar database işlemlerinde kullanılır.

### Yetki Doğrulama Onayı

Yetki doğrulama onayı kullanıcının kimliğini sisteme giriş yapmadan önce <b>email</b>, <b>sms</b> yada <b>mobil çağrı</b> gibi yöntemlerle onay işleminden geçirmek için kullanılan ekstra bir özelliktir.

Kullanıcı başarılı olarak giriş yaptıktan sonra kimliği kalıcı olarak ( varsayılan 3600 saniye ) önbelleklenir. Eğer kullanıcı onay adımından geçirilmek isteniyorsa kalıcı kimlikler <kbd>$this->user->identity->makeTemporary()</kbd> metodu ile geçici hale ( varsayılan 300 saniye ) getirilir. Geçici olan bir kimlik 300 saniye içerisinde kendiliğinden yokolur. 

Bu özelliği kullanmak istiyorsanız aşağıda daha detaylı bilgiler bulabilirsiniz.

### Geçiçi Kimlikler Hangi Amaçla Kullanılır ?

Geçici kimlikler genellikle yetki doğrulama onaylaması için kulanılırlar.

Kullanıcının geçici kimliğini onaylaması sizin ona <b>email</b>, <b>sms</b> yada <b>mobil çağrı</b> gibi yöntemlerinden herhangi biriyle göndermiş olacağınız onay kodu ile gerçekleşir. Eğer kullanıcı 300 saniye içerisinde ( bu konfigürasyon dosyasından ayarlanabilir bir değişkendir ) kullanıcı kendisine gönderilen onay kodunu onaylayamaz ise geçiçi kimlik kendiliğinden yok olur.

Eğer kullanıcı onay işlemini başarılı bir şekilde gerçekleştirir ise <kbd>$this->user->identity->makePermanent()</kbd> metodu ile kimliği kalıcı hale getirmeniz gereklidir.
Bir kimlik kalıcı yapıldığında kullanıcı tam olarak yetkilendirilmiş olur.

#### Geçici kimliğin oluşturulmasına bir örnek:

```php
$this->user->identity->makeTemporary();
```
Bu fonksiyonun oturum denemesi fonksiyonundan sonra kullanılması gerekmektedir. Bu fonksiyon kullanıldığında eğer oturum açma başarılı ise kalıcı olarak kaydedilen kimlik hafıza bloğunda geçici hale getirilir. Fonksiyonun kullanılmadığı durumlarda ise varsayılan olarak tüm kullanıcılar sistemde kalıcı oturum açmış olurlar.

Bu aşamadan sonra onaya düşen kullanıcı için bir onay kodu oluşturup ona göndermeniz gerekmektedir. Onay kodu onaylanırsa bu onaydan sonra aşağıdaki method ile kullanıcıyı kalıcı olarak yetkilendirebilirsiniz.

#### Onaylanmış kimliğin kalıcı hale getirilmesine bir örnek:

```php
$this->user->identity->makePermanent();
```

Yukarıdaki method geçici kimliği olan kullanıcıyı kalıcı kimlikli bir kullanıcı haline dönüştürür. Kalıcı kimliğine kavuşan kullanıcı artık sistemde tam yetkili konuma gelir. Kalıcılık kullanıcı kimliğinin önbelleklenmesi (cache) lenmesi demektir. Önbelleklenen kullanıcının kimliği tekrar oturum açıldığında database sorgusuna gidilmeden elde edilmiş olur. Kalıcı kimliğin önbelleklenme süresi konfigürasyon dosyasından ayarlanabilir bir değişkendir. Geçici veya kalıcı kimlik oluşturma fonksiyonları kullanılmamışsa sistem varsayılan olarak kimliği kalıcı olarak kaydedecektir.

#### Bir Kalıcı Oturum Açma Denemesi ( Varsayılan )

```php
$this->user->login->attempt(
    [
        $this->user->config['db.identifier'] => $this->request->post('email'), 
        $this->user->config['db.password'] => $this->request->post('password')
    ],
    $this->request->post('rememberMe')
);
```

#### Bir Geçici Oturum Açma Örneği

Oturum açmayı bir örnekle daha iyi kavrayabiliriz, membership adı altında bir dizin açalım ve login controller dosyamızı bu dizin içerisinde yaratalım. Geçici oturumun kalıcı oturumdan farkı <kbd>$this->user->identity->makeTemporary();</kbd> metodu ile oturum açıldıktan sonra kimliğin geçici hale getirilmesidir.

```php
+ app
+ assets
- modules
    - membership
        + view
        Login.php
```

```php
namespace Membership;

Class Login extends \Controller
{
    public function load()
    {
        $this->c['url'];
        $this->c['form'];
        $this->c['view'];
        $this->c['request'];
        $this->c['user'];
        $this->c['flash'];
    }

    /**
     * Index
     *
     * @event->subscribe('Event\Login\Attempt');
     *  
     * @return void
     */
    public function index()
    {
        if ($this->request->isPost()) {

            $this->c['validator']; // load validator
            $this->validator->setRules('email', 'Email', 'required|email|trim');
            $this->validator->setRules('password', 'Password', 'required|min(6)|trim');

            if (  ! $this->validator->isValid()) {
                $this->form->setErrors($this->validator);
            } else {

                $result = $this->user->login->attempt(
                    [
                        $this->user->config['db.identifier'] => $this->request->post('email'), 
                        $this->user->config['db.password'] => $this->request->post('password')
                    ],
                    $this->request->post('rememberMe')
                );
                if ($result->isValid()) {

                    $this->user->identity->makeTemporary();
                    $this->flash->success('Verification code has been sent.');

                    $this->url->redirect('membership/confirm_verification_code');

                } else {
                    $this->validator->setError($result->getArray());
                    $this->form->setErrors($this->validator);
                }
            }
        }
            
        echo $this->flash->output();          // form message
        print_r($this->form->outputArray());  // form errors

    }
}

/* End of file Login.php */
/* Location: .modules/membership/Login.php */
```

Yukarıdaki kodları çalıştırdığınıza geçici kimlik oluştu ise bir <b>membership/confirm_verification_code</b> sayfası oluşturun ve bu sayfada kullanıcı onay kodunu doğru girdi ise <kbd>$this->user->identity->makePermanent();</kbd> metodunu kullanarak kullanıcıyı yetkilendirin.


### AuthResult Sınıfı ve Oturum Açma Sonuçları

Oturum açma denemesi yapıldığında <b>AuthResult</b> sınıfı ile sonuçlar doğrulama filtresinden geçer ve oluşan hata kodları ve mesajlar bir dizi içerisine kaydedilir,  <kbd>$this->user->login->attempt()</kbd> metodu ise sonuçları alabilmemiz için AuthResult nesnesine geri dönmektedir.

```php
$result = $this->user->login->attempt(
    [
        $this->user->config['db.identifier'] => $this->request->post('email'), 
        $this->user->config['db.password'] => $this->request->post('password')
    ],
    $this->request->post('rememberMe')
);

if ($result->isValid()) {

    $row = $result->getResultRow();

    // Go ..

} else {

    print_r($result->getArray()); // get errors

    /* Array ( 
        [code] => -2 
        [messages] => Array ( 
            [0] => Supplied credentials invalid. 
        ) 
        [identifier] => user@example.com 
    ) 
    */
}
```

#### Hata ve Sonuç Kodları Tablosu

<table>
    <thead>
        <tr>
            <th>Kod</th>    
            <th>Sabit</th>    
            <th>Açıklama</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td>0</td>
            <td>AuthResult::FAILURE</td>
            <td>Genel başarısız yetki doğrulama.</td>
        </tr>
        <tr>
            <td>-1</td>
            <td>AuthResult::FAILURE_IDENTITY_AMBIGUOUS</td>
            <td>Kimlik belirsiz olması nedeniyle başarısız yetki doğrulama.( Sorgu sonucunda 1 den fazla kimlik bulunduğunu gösterir ).</td>
        </tr>
        <tr>
            <td>-2</td>
            <td>AuthResult::FAILURE_CREDENTIAL_INVALID</td>
            <td>Geçersiz kimlik bilgileri girildiğini gösterir.</td>
        </tr>
        <tr>
            <td>-3</td>
            <td>AuthResult::FAILURE_UNCATEGORIZED</td>
            <td>Kategorize edilemeyen bir hata oluştuğu anlamına gelir.</td>
        </tr>
        <tr>
            <td>-4</td>
            <td>AuthResult::TEMPORARY_AUTH_HAS_BEEN_CREATED</td>
            <td>Geçici kimlik bilgilerinin oluşturulduğuna dair bir bilgidir.</td>
        </tr>
        <tr>
            <td>-5</td>
            <td>AuthResult::FAILURE_UNVERIFIED</td>
            <td>Yetki doğrulama onayı aktif iken geçici kimlik bilgilerin henüz doğrulanmadığını gösterir.</td>
        </tr>
        <tr>
            <td>-6</td>
            <td>AuthResult::WARNING_ALREADY_LOGIN</td>
            <td>Kullanıcı kimliğinin zaten doğrunlanmış olduğunu gösterir.</td>
        </tr>
        <tr>
            <td>1</td>
            <td>AuthResult::SUCCESS</td>
            <td>Yetki doğrulama başarılıdır.</td>
        </tr>

    </tbody>
</table>


#### Yetki Doğrulama Kimlik Sınıfları 

Kimlikler içerisinde kendi fonksiyonlarınızı oluşturabilmeniz için kimlik sınıfları <b>app/classes/Auth</b> klasörü altında gruplanmıştır. Bu klasör o2 auth paketine genişler ve aşağıdaki dizindedir.

```php
- app
    - classes
        - Auth
            Identities
                - AuthorizedUser.php
                - GenericUser.php
        + Model
```

<b>AuthorizedUser</b> yetkili kullanıcıların kimliklerine ait metodları, <b>GenericUser</b> sınıfı ise yetkisiz yani Guest diye tanımladığımız kullanıcıların kimliklerine ait metodları içerir. Bu sınıflardaki <b>get</b> metotları kullanıcı kimliklerinden <b>okuma</b>, <b>set</b> metotları ise kimliklere <b>yazma</b> işlemlerini yürütürler. Bu sınıflara metodlar ekleyerek ihtiyaçlarınıza göre düzenleme yapabilirsiniz fakat <b>AuthorizedUserInterface</b> ve <b>GenericUserInterface</b> sınıfları içerisindeki tanımlı metodlardan birini bu sınıflar içerisinden silmemeniz gerekir.

#### Sınıf Açıklamaları

<table>
    <thead>
        <tr>
            <th>Class</th>    
            <th>Description</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td>Auth\Identities\GenericUser</td>
            <td>Ziyaretçi (Guest) kullanıcısına ait genel kimlik profilidir.</td>
        </tr>
        <tr>
            <td>Auth\Identities\AuthorizedUser</td>
            <td>Yetkilendirilmiş kullanıcıya ait kimlik profilidir.</td>
        </tr>
        <tr>
            <td>Auth\Model\User</td>
            <td>Database sorguları üzerinde değişiklik yapabilmenizi sağlayan ara yüzdür. Seçimlik olarak yaratılır ve yaratıldığında <kbd>\Obullo\Authentication\Model\User</kbd> sınıfına genişlemesi gerekir.</td>
        </tr>
    </tbody>
</table>


### User Config Sınıfı İşlevleri

------

<kbd>app/Classes/Sevice/User.php</kbd> dosyasında çağrılan AuthServiceProvider sınıfı içerisinden gönderilen parametreleri ve <b>auth.php</b> konfigürasyon dosyasındaki parametreler ile birleştirerek auth paketine ait konfigurasyon ile ilgili tüm dizileri tek bir elden yönetmeye yardımcı olur. Daha iyi anlamak için aşağıdaki örneğe bir gözatabiliriz.

```php
echo $this->user->config['db.identifier'];   // Çıktı username
echo $this->user->config['db.password'];     // Çıktı password
echo $this->user->config['cache.key'];       // Çıktı Auth

echo $this->user->config['cache']['storage'];  // Çıktı \Obullo\Authentication\Storage\Redis
```

### User Identity Sınıfı İşlevleri

------

Kullanıcı kimliği O2 paketi içerisindedir ve <b>app/Auth/Identities</b> içerisindeki AuthorizedUser sınıfına genişler. Bu sınıf aşağıdaki kimlik işlevlerini yönetir.

* Kimlikten veri okuma ve kimliğe veri kaydetme
* Kullanıcı kimliğinin olup olmadığı kontrolü
* Kullanıcı kimliğinin kalıcı olup olmadığı
* Kullanıcı kimliğini kalıcı veya geçici hale dönüştürme. ( makeTemporary, makePermanent )
* Kullanıcının oturumunu sonlandırma ( logout )
* Kullanıcı kimliğini tamamen yok etme ( destroy )
* Beni hatırla özelliği kullanılmışsa kullanıcı kimliğini çerezden kalıcı olarak silme forgetMe )

Aşağıda örnek bir kullanıcı kimliğini nasıl görüntüleyebileceğinizi gösteriliyor.

```php
print_r($this->user->identity->getArray()); // Çıktılar
/*
Array
(
    [__activity] => Array
        (
            [last] => 1413454236
        )

    [__isAuthenticated] => 1
    [__isTemporary] => 0
    [__isVerified] => 1
    [__lastTokenRefresh] => 1413454236
    [__rememberMe] => 0
    [__token] => 6ODDUT3FtmmXEZ70.86f40e86
    [__tokenFrequency] => 3
    [__time] => 1414244130.719945
    [id] => 1
    [password] => $2y$10$0ICQkMUZBEAUMuyRYDlXe.PaOT4LGlbj6lUWXg6w3GCOMbZLzM7bm
    [remember_token] => bqhiKfIWETlSRo7wB2UByb1Oyo2fpb86
    [username] => user@example.com
)
*/
```

Yukarıda görüldüğü gibi çift underscore karakteri ile başlayan anaharlar yetki doğrulama paketi tarafından kullanılan (rezerve anaharlar) diğerleri ise size ait verilerin kaydedildiği anahtarlardır. Diğer bir anahtar <b>__activity</b> ise yetkisi doğrulanmış kullanıcılar ile igili sayısal yada meta verileri için ayrılmış olan size ait bir anahtardır.


### User Activity Sınıfı İşlevleri

------

Kullanıcı aktivite sınıfı yetkilendirilmiş kullancılara ait meta verilerini kaydeder. Son aktivite zamanı ve diğer eklemek istediğiniz harici anlık veriler bu sınıfı aracılığıyla activity key içerisinde tutulur.

#### Örnek bir aktivite verisi

```php
$this->user->activity->set('sid', $this->session->get('session_id'));
$this->user->activity->set('date', time());

// __activity a:3:{s:3:"sid";s:26:"f0usdabogp203n5df4srf9qrg1";s:4:"date";i:1413539421;}
```

### Olaylar ( Events )

------

Yetki doğrulama paketine ait olaylar <b>app/classes/Event/Login</b> klasörü altında dinlenir. Bu sınıf içerisindeki en önemli olaylardan biri <b>Attempt()</b> olayıdır. Bu olay <b>Login</b> sınıfı içerisindeki <b>attempt()</b> metodu içerisinde <b>login.attempt.before</b> ve <b>login.attempt.after</b> isimleriyle ile ilan edilmiştir. 

Aşağıdaki örnekte gösterilen <b>Attempt</b> sınıfı subscribe metodu <b>login.attempt.after</b> olayını dinleyerek oturum denemeleri anını ve bu andan sonra oluşan sonuçları kontrol edebilmenizi sağlar. 

Takip eden örneğe bir göz atalım.

```php
namespace Event\Login;

use Obullo\Container\Container;
use Obullo\Authentication\AuthResult;
use Obullo\Event\EventListenerInterface;

class Attempt implements EventListenerInterface
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
    public function __construct(Container $c)
    {
        $this->c = $c;
    }

    /**
     * Before login attempt
     * 
     * @param array $credentials user login credentials
     * 
     * @return void
     */
    public function before($credentials = array())
    {
        // ..
    }

    /**
     * After login attempts
     *
     * @param object $authResult AuthResult object
     * 
     * @return void
     */
    public function after(AuthResult $authResult)
    {
        if ( ! $authResult->isValid()) {

            // Store attemtps
            // ...
        
            // $row = $authResult->getResultRow();  // Get query results

        }
        return $authResult;
    }

    /**
     * Register the listeners for the subscriber.
     * 
     * @param object $event event class
     * 
     * @return void
     */
    public function subscribe($event)
    {
        $event->listen('login.attempt.before', 'Event\Login\Attempt@before');
        $event->listen('login.attempt.after', 'Event\Login\Attempt@after');
    }

}

// END Attempt class

/* End of file Attempt.php */
/* Location: .Event/Login/Attempt.php */
```

Yukarıdaki örnekte <b>after()</b> metodunu kullanarak oturum açma denemesinin başarılı olup olmaması durumuna göre oturum açma işlevine eklemeler yapabilir yetki doğrulama sonuçlarınına göre uygulamanızın davranışlarını özelleştirebilirsiniz.


### Database Sorgularını Özelleştirmek

------

O2 yetki doğrulama paketi kullanıcıya ait database fonksiyonlarını servis içerisinden <kbd>Obullo\Authentication\Model\User</kbd> sınıfından çağırmaktadır. Eğer mevcut database sorgularında değişlik yapmak istiyorsanız bu sınıfa genişlemek için önce auth konfigürasyon dosyasından db.model anahtarını <kbd>\Auth\Model\User</kbd> olarak değiştirmeniz gerekmektedir.

Daha sonra <b>app/classes/Auth/Model</b> klasörünü içerisine <b>User.php</b> dosyasını yaratarak aşağıdaki gibi User model sınıfı içerisinden <b>Obullo\Authentication\Model\User</b> sınıfına genişlemeniz gerekmektedir. Bunu yaparken <b>UserInterface</b> içerisindeki yazım kurallarına bir göz atın.

Aşağıda O2 yetki doğrulama paketi içerisindeki <kbd>\Obullo\Authentication\Model\UserInterface</kbd> sınıfı görülüyor.

```php
namespace Obullo\Authentication\Model;

use Obullo\Container\Container;
use Auth\Identities\GenericUser;
use Obullo\ServiceProviders\ServiceProviderInterface;

interface UserInterface
{
    public function __construct(Container $c, ServiceProviderInterface $provider);
    public function execQuery(GenericUser $user);
    public function execRecallerQuery($token);
    public function updateRememberToken($token, GenericUser $user);
}
```

Önce User.php service dosyasından <b>db.model</b> anahtarını <kbd>\Auth\Model\User</kbd> olarak değiştirin.

```php
class User implements ServiceInterface
{
    public function register(Container $c)
    {
        $c['user'] = function () use ($c) {
            $user = new AuthServiceProvider(
                $c,
                array(
                    'cache.key'        => 'Auth',
                    'db.adapter'       => '\Obullo\Authentication\Adapter\Database',
                    'db.model'         => '\Auth\Model\User', // Değiştirilen bölüm
                    'db.provider'      => 'database',
                    'db.connection'    => 'default',
                    'db.tablename'     => 'users', // Database column settings
                    'db.id'            => 'id',
                    'db.identifier'    => 'username',
                    'db.password'      => 'password',
                    'db.rememberToken' => 'remember_token'
                )
            );
            return $user;
        };
    }
}

// END User class

/* End of file User.php */
/* Location: .app/classes/Service/User.php */
```
Yukarıda gösterilen auth servis konfigürasyonundaki <b>db.model</b> anahtarını <kbd>\Auth\Model\User</kbd> olarak güncellediyseniz, aşağıda sizin için bir model örneği yaptık bu örneği değiştererek ihtiyaçlarınıza göre kullanabilirsiniz. Bunun için <b>Obullo\Authentication\Model\User</b> sınıfına bakın ve ezmek ( override ) istediğiniz method yada değişkenleri sınıfınız içerisine dahil edin.

```php
namespace Auth\Model;

use Obullo\Container\Container;
use Auth\Identities\GenericUser;
use Auth\Identities\AuthorizedUser;
use Obullo\Authentication\Model\UserInterface;
use Obullo\Authentication\Model\User as ModelUser;

class User extends ModelUser implements UserInterface
{
     /**
     * Constructor
     * 
     * @param object $c        container
     * @param object $provider ServiceProviderInterface
     */
    public function __construct(Container $c, ServiceProviderInterface $provider)
    {
        parent::__construct($c, $provider);
    }
    
    /**
     * Execute sql query
     *
     * @param object $user GenericUser object to get user's identifier
     * 
     * @return mixed boolean|object
     */
    public function execQuery(GenericUser $user)
    {
        return parent::execQuery($user);
    }

}

// END User.php File
/* End of file User.php

/* Location: .app/classes/Auth/Model/User.php */
```

### Rezerve edilmiş anahtarlar :

Yetki doğrulama paketi kendi anahtarlarını oluştururup bunları hafıza deposunu kaydederken 2 adet underscore önekini kullanır. Yetki doğrulama paketine ait olan bu anahtarlar yazma işlemlerinde çakışma olmaması için bu "__" önek kullanılarak ayırt edilir.

<table>
    <thead>
        <tr>
            <th>Anahtar</th>    
            <th>Açıklama</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td>__activity</td>
            <td>Online kullanıcı aktivite verilerini içerir: Son aktivite zamanı ve diğer eklemek istediğiniz veriler gibi.</td>
        </tr>
        <tr>
            <td>__isAuthenticated</td>
            <td>Eğer kullanıcı yetkisi doğrulanmış ise bu anahtar <b>1</b> aksi durumda <b>0</b> değerini içerir.</td>
        </tr>
        <tr>
            <td>__isTemporary</td>
            <td>Eğer yetki doğrulama onayı için <kbd>$this->user->identity->makeTemporary()</kbd> metodu login attempt metodu sonrasında kullanılmışsa bu anahtar <b>1</b> aksi durumda <b>0</b> değerini içerir. Eğer yetki doğrulama onayı kullanıyorsanız kullanıcıyı kendi onay yönteminiz ile onayladıktan sonra <kbd>$this->user->identity->makePermanent()</kbd> metodunu kullanarak doğrulanan kullanıcı yetkisini kalıcı hale getirmeniz gerekir.</td>
        </tr>
        <tr>
            <td>__isVerified</td>
            <td>Yetki doğrulama onayı kullanıyorsanız kullanıcıyı onayladığınızda bu anahtarın değeri <b>1</b> aksi durumda <b>0</b> olur.</td>
        </tr>
        <tr>
            <td>__rememberMe</td>
            <td>Kullanıcı giriş yaparken beni hatırla özelliğini kullandıysa bu değer <b>1</b> değerini aksi durumda <b>0</b> değerini içerir.</td>
        </tr>
        <tr>
            <td>__time</td>
            <td>Kimliğin ilk oluşturulma zamanıdır. Microtime olarak oluşturulur ve unix time formatında kaydedilir.</td>
        </tr>

    </tbody>
</table>


#### Config Sınıfı Referansı

------

> User servisinde AuthServiceProvider sınıfı içerisinden gönderilen parametreleri auth konfigürasyon dosyasındaki parametreler ile birleştirerek tüm konfigurasyonu tek bir elden yönetmeye yardımcı olur. Konfigürasyon değişkenlerine ArrayAccess bileşenleri ile erişilir.

##### $this->user->login->config['variable'];

Konfigürasyon dosyası veya user servisi parametrelerine döner.


#### Login Sınıfı Referansı

------

>Login sınıfı yetkisi doğrulanmamış (GenericUser) yada doğrulanmış (AuthorizedUser) kullanıcıya ait oturum işlemlerini yönetmenizi sağlar.

##### $this->user->login->attempt(array $credentials, $rememberMe = false);

Bu fonksiyon kullanıcı oturumunu açmayı dener ve AuthResult nesnesine döner.

##### $this->user->login->validate(array $credentials);

Yetki doğrulama yapmadan kullanıcı Guest kimliği bilgilerine doğrulama işlemi yapar.Bilgiler doğruysa true değerine yanlış ise false değerine döner.

##### $this->user->login->validateCredentials(AuthorizedUser $user, array $credentials);

AuthorizedUser kimliğine sahip kullanıcı bilgilerini dışarıdan gelen yeni bilgiler ile karşılaştırarak doğrulama yapar.

##### $this->user->login->getUserSessions();

Geçerli kullanıcının önbelleğe kaydedilmiş oturumlarına bir dizi içerisinde geri döner. Her açılan oturuma bir login id verilir ve kullanıcılar farklı tarayıcılarda veya aygıtlarda birden fazla oturum açmış olabilirler.


#### AuthResult Sınıfı Referansı

------

>AuthResult sınıfı login doğrulamasından sonra geri dönen sonuçları elde etmeyi ve hata kodlarını yönetmeyi sağlar.

##### $result->isValid();

Login attempt methodundan geri dönen hata kodu <b>0</b> değerinden büyük ise <b>true</b> küçük ise <b>false</b> değerine döner. Başarılı oturum açma işlermlerinde hata kodu <b>1</b> değerine döner diğer durumlarda negatif değerlere döner.

##### $result->getCode();

Login denemesinden sonra geçerli hata koduna geri döner.

##### $result->getIdentifier();

Login denemesinden sonra geçerli kullanıcı kimliğine göre döner. ( id, username, email gibi. )

##### $result->getMessages();

Login denemesinden sonra hata mesajlarına geri döner.

##### $result->setCode(int $code);

Login denemesinden varsayılan sonuca hata kodu ekler.

##### $result->setMessage(string $message);

Login denemesinden sonra sonuçlara bir hata mesajı ekler.

##### $result->getArray();

Login denemesinden sonra tüm sonuçları bir dizi içerisinde verir.

##### $result->getResultRow();

Login denemesinden sonra geçerli veritabanı sorgu sonucu yada önbellek verilerine geri döner.


#### Identity Sınıfı Referansı

------

>Identity sınıfı yetkisi doğrulanmış kullanıcıya ait kimliği yönetmenizi sağlar.

##### $this->user->identity->check();

Kullanıcının yetkisinin olup olmadığını kontrol eder. Yetkili ise <b>true</b> değilse <b>false</b> değerine döner.

##### $this->user->identity->guest();

Kullanıcının yetkisi olmayan kullanıcı yani ziyaretçi olup olmadığını kontrol eder. Ziyaretçi ise <b>true</b> değilse <b>false</b> değerine döner.

##### $this->user->identity->exists();

Kimliğin önbellekte olup olmadığını kotrol eder. Varsa <b>true</b> yoksa <b>false</b>değerine döner.

##### $this->user->identity->makeTemporary();

Başarılı giriş yapmış kullanıcıya ait kimliği konfigurasyon dosyasından belirlenmiş sona erme ( expire ) süresine göre geçici hale getirir. Süre sona erdiğinde kimlik hafıza deposundan silinir.

##### $this->user->identity->makePermanent();

Başarılı giriş yapmış kullanıcıya ait geçici kimliği konfigurasyon dosyasından belirlenmiş kalıcı süreye ( lifetime ) göre kalıcı hale getirir. Süre sona erdiğinde veritabanına tekrar sql sorgusu yapılarak kimlik tekrar hafızaya yazılır.

##### $this->user->identity->isVerified();

Onaya tabi olan yetki doğrulamada başarılı oturum açma işleminden sonra kullanıcının onaylanıp onaylanmadığını gösterir. Kullanıcı onaylı ise <b>1</b> değerine değilse <b>0</b> değerine döner.

##### $this->user->identity->isTemporary();

Kullanıcının kimliğinin geçici olup olmadığını gösterir. <b>1</b> yada </b>0</b> değerine döner.

##### $this->user->identity->updateTemporary(string $key, mixed $val);

Geçici olarak oluşturulmuş kimlik bilgilerini güncellemenize olanak tanır.

##### $this->user->identity->logout();

Oturumu kapatır ve __isAuthenticated anahtarı önbellekte <b>0</b> değeri ile günceller. Bu method önbellekteki kullanıcı kimliğini bütünü ile silmez sadece kullanıcıyı oturumu kapattı olarak kaydeder.

##### $this->user->identity->destroy();

Önbellekteki kimliği bütünüyle yok eder.

##### $this->user->identity->forgetMe();

Beni hatırla çerezinin bütünüyle tarayıcıdan siler.

##### $this->user->identity->refreshRememberToken();

Beni hatırla çerezini yenileyerek veritabanı ve çerezlere kaydeder.


#### Identity "Get" Metotları

------

>Identity get metotları hafıza deposu içerisinden yetkisi doğrulanmış kullanıcıya ait kimlik verilerine ulaşmanızı sağlar.

##### $this->user->identity->getIdentifier();

Kullanıcın tekil tanımlayıcı sına geri döner. Tanımlayıcı genellikle kullanıcı adı yada id değeridir.

##### $this->user->identity->getPassword();

Kullanıcın hash edilmiş şifresine geri döner.

##### $this->user->identity->getRememberMe();

Eğer kullanıcı beni hatırla özelliğini kullanıyorsa <b>1</b> değerine aksi durumda <b>0</b> değerine döner.

##### $this->user->identity->getTime();

Kimliğin ilk yaratılma zamanını verir. ( Php Unix microtime ).

##### $this->user->identity->getRememberMe();

Kullanıcı beni hatırla özelliğini kullandı ise <b>1</b> değerine kullanmadı ise <b>0</b> değerine döner.

##### $this->user->identity->getPasswordNeedsReHash();

Kullanıcı giriş yaptıktan sonra eğer şifresi yenilenmesi gerekiyorsa hash edilmiş <b>yeni şifreye</b> gerekmiyorsa <b>false</b> değerine döner.

##### $this->user->identity->getRememberToken();

Beni hatırla çerezine döner.

##### $this->user->identity->getArray()

Kullanıcının tüm kimlik değerlerine bir dizi içerisinde geri döner.

>Kendi metotlarınızı <kbd>app/classes/Auth/Identities/AuthorizedUser</kbd> sınıfı içerisine eklemeniz önerilir.


#### Identity "Set" Metotları

------

>Identity set metotları hafıza deposu içerisinden yetkisi doğrulanmış kullanıcıya ait kimlik verilerine yazmanızı sağlar.

##### $this->user->identity->variable = 'value'

Kimlik dizisine yeni bir değer ekler.

##### unset($this->user->identity->variable)

Kimlik dizisinde varolan değeri siler.

##### $this->user->identity->setArray(array $attributes)

Tüm kullanıcı kimliği dizisinin üzerine girilen diziyi yazar.


#### Activity Sınıfı Referansı

------

>Aktivite verileri, son aktivite zamanı gibi anlık değişen kullanıcı verilerini önbellekte tutabilmeyi sağlayan sınıftır.

##### $this->user->activity->set($key, $val);

Aktivite dizinine bir anahtar ve değerini ekler.

##### $this->user->activity->get($key);

Aktivite dizininde anahtarla eşleşen değere geri döner.

##### $this->user->activity->remove($key);

Daha önce set edilen değeri temizler.

##### $this->user->activity->destroy();

Tüm aktivite verilerini önbellekten temizler.
