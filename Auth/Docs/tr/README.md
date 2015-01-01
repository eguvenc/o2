
## O2 Authentication

Auth paketi yetkilendirme adaptörleri ile birlikte çeşitli ortak senaryolar için size bir API sağlar. O2 Auth yalnızca yetkilendirme ( <b>authentication</b> ) ile ilgilir ve yetki ( authorization ) ile ilgili herhangi bir şeyi içermez. Yetkiler ile ilgili daha fazla bilgi için lütfen <b>Permissions</b> paketine bakınız. 

O2 Auth; hafıza depoları, adaptörler, güvenlik çerezi, oturum id sini yeniden yaratma, hatırlatma çerezi gibi mevcut özellikleri ile size esnek, hızlı ve güvenli bir yetkilendirme servisi sağlar.

O2 Auth redis sürücüsü kullandığınızda size online kullanıcı kimliklerini görüntüleme ve bu kimliklere ulaşarak çeşitli istatistik verileri oluşturabilmenize olanak sağlar.

## Adaptörler

Auth adaptörleri yetkilentirme servisinde esneklik için <b>Database</b> (RDBMS or NoSQL) veya <b>dosya tabanlı</b> gibi farklı türde kimlik doğrulama biçimleri olarak kullanılırlar.

Farklı adaptörlerin çok farklı seçenekler ve davranışları olması muhtemeldir , ama bazı temel şeyler kimlik doğrulama adaptörleri arasında ortaktır. Örneğin, kimlik doğrulama hizmeti sorgularını gerçekleştirmek, kimlik doğrulama bilgilerinin onayı ve dönen sonuçlar Auth adaptörleri için ortak kullanılır.

## Hazıfa Depoları ( Storage )

Hazıfa depoları ( memory storage ) 

**Not:** O2 Auth şu anda sadece <b>Redis</b> sürücüsünü desteklemektedir. Ubuntu altında nasıl redis kurabileceğinizi <b>warmup</b> adı ile adlandırılan kurulum dökümentasyonları topluluğunun hazırladığı belgeye bu bağlantıdan erişebilirsiniz. <a href="https://github.com/obullo/warmup/tree/master/Redis">Redis Installation</a>.

## Akış Şeması

Aşağıdaki akış şeması bir kullanıcının yetkilendirme aşamalarından nasıl geçtiği ve yetkilendirme servisinin nasıl çalıştığı hakkında size bir ön bilgi verecektir:

* [Şemayı görmek için buraya tıklayınız](/Auth/Docs/images/flowchart.png?raw=true)

Şemada görüldüğü üzere <b>GenericUser</b> ve <b>AuthorizedUser</b> olarak iki farklı durumu olan bir kullanıcı sözkonusudur. GenericUser yetkilendirilmemiş AuhtorizedUser servis tarafından yetkilendirilmiş kullanıcıdır.

GenericUser login butonuna bastığı anda performans için ilk olarak memory bloğunda daha önceden kullanıcının yetkilendirilmiş kalıcı kimliği olup olmadığında bakılır eğer memory bloğunda kalıcı yetkilendirme kaydı var ise kullanıcı kimliği buradan yok ise database adaptöründen sql query yapılarak elde edilir.

Eğer kullanıcı kimliği database sorgusu yapılarak elde edilmişse elde edilen kimlik kartı performans için tekrar memory bloğuna yazılır.

Buradan sonraki işlemleri anlayabilmemiz için önce yetkilendirme onaylamasının ne olduğunu anlamamız gerekir.

<b>Yetkilendirme onaylaması</b>, kullanıcı başarılı olarak giriş yaptıktan sonra kullanıcı kimliğinin onay için bekletilmesi aşamasıdır. Onay durumu açık ise kullanıcı kimliği memory bloğuna geçiçi olarak kaydedilir. Kullanıcın geçici kimliğini onaylaması sizin ona <b>email</b>, <b>sms</b> yada <b>mobil çağrı</b> gibi yöntemlerinden herhangi biriyle göndermiş olacağınız onay kodu ile gerçekleşir. Eğer kullanıcı 300 saniye içerisinde ( bu config dosyasından ayarlanabilir bir değişkendir ) kullanıcı kendisine gönderilen onay kodunu onaylayamaz ise geçiçi kimlik kendiliğinden yok olur.

Eğer kullanıcı onay işlemini başarılı bir şekilde gerçekleştirir ise <b>temporary</b> memory bloğuna kaydedilmiş geçici kimlik artık herhangi bir database sorgusu ve password hash işlemi olmadadan memory bloğuna <b>permanent</b> yani kalıcı olarak yazılır ve kullanıcı yetkilendirilmesi başarılı bir şekilde gerçekleşmiş olur.

Kullanıcın onaya düşmesi yani yetkilendirme onaylama varsayılan olarak kapalıdır. Aşağıdaki method login attemp fonksiyonun üzerinde kullanılırsa yetkilendirme onaylama özelliği açık hale gelecektir.

**Note:** Bu paket programlanırken geçici kimlik "__temporary" kalıcı kimlik ise "__permanent" simgesi ile ifade edilmiş aynı zamanda hafıza depolarında bu ifadeler kullanılmıştır.

#### Yetkilendirme onayının açılmasına bir örnek:

```php
$this->user->login->enableVerification();
```

Bu aşamadan sonra onaya düşen kullanıcı için bir onay kodu oluşturup bunu ona göndermeniz gerekmektedir. Onay kodu onaylanırsa bu onaydan sonra aşağıdaki method ile kullanıcıyı kalıcı olarak yetkilendirebilirsiniz.

#### Onaylanmış kimliğin kalıcı hale getirilmesine bir örnek:

```php
$this->user->login->authenticateVerifiedIdentity();
```

Yukarıdaki method geçici kimliği olan kullanıcıyı kalıcı kimlikli bir kullanıcı haline dönüştürür. Kalıcı kimliğine kavuşan kullanıcı artık sistemde yetkili konuma gelir.

Diğer bir durum yetkilendirme onayının kapalı olması yani varsayılan durumdur. Onaylamanın kapalı olması durumunda yani sisteminizde yetkilendirme öncesi onay gibi bir özellik kullanmıyor iseniz yukarıdaki onaylama metodlarının hiçbirini kullanmak zorunda kalmazsınız.

Şema üzerinden gidersek yetkilendirme onayının kapalı olması durumunda varsayılan işlemler devam eder ve kullanıcı kalıcı (__permanent) olarak memory bloğuna yazılır. Kalıcılık kullanıcı kimliğinin önbelleklenmesi (cache) lenmesi demektir. Önbelleklenen kullanıcının kimliği tekrar oturum açıldığında database sorgusuna gidilmeden sağlanmış olur. Kalıcı önbelleklenme süresi config dosyasından ayarlanabilir bir değişkendir.


## Redis Deposu

Auth sınıfı hafıza deposu için varsayılan olarak redis kullanır. Aşağıdaki resim kullanıcı kimliklerinin hafıza deposunda nasıl tutulduğunu göstermektedir.

![PhpRedisAdmin](/Auth/Docs/images/redis.png?raw=true "PhpRedisAdmin")

Vardayılan hafıza sınıfı auth konfigürasyonundan değiştirilebilir.

```php
<?php

'memory' => array( 
        'key' => 'Auth',
        'storage' => '\Obullo\Auth\Storage\Redis',
        'block' => array(

        )
    ),
```

Redis dışında bir çözüm kullanıyorsanız kendi memory depolama sınfınızı auth konfigürasyon dosyasından değiştererek kullanabilirsiniz.

### Auth paketinin kullandığı rezerve edilmiş anahtarlar :

Auth paketi kendi anahatarlarını ( keys ) oluştururup bunları hafıza deposunu kaydederken 2 adet underscore "__" önekini kullanır. Auth paketine ait olan bu anahtarlar yazma işlemlerinde çakışma olmaması için "__" öneki kullanılarak ayırt edilir.

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
            <td>Eğer kullanıcı yetkilendirilmişse bu anahtar <b>1</b> aksi durumda <b>0</b> değerini içerir.</td>
        </tr>
        <tr>
            <td>__isTemporary</td>
            <td>Eğer yetkilendirme onayı için <kbd>$this->user->login->enableVerification()</kbd> metodu login attempt metodu öncesinde kullanılmışsa bu anahtar <b>1</b> aksi durumda <b>0</b> değerini içerir. Eğer yetkilendirme onayı kullanıyorsanız <kbd>$this->user->login->authenticateVerifiedIdentity()</kbd> metodunu kullanarak kullanıcıyı kalıcı olarak yetkilendirmeniz gerekir.</td>
        </tr>
        <tr>
            <td>__isVerified</td>
            <td>Yetkilendirme onayı kullanıyorsanız kullanıcıyı onayladığınızda bu anahtarın değeri <b>1</b> aksi durumda <b>0</b> olur.</td>
        </tr>
        <tr>
            <td>__lastTokenRefresh</td>
            <td>Güvenlik çerezindeki token değerinin en son ne zaman güncellendiğini takip eden zaman damgasıdır. Güvenlik çerezi ( Security Token ) varsayılan olarak kendisini her bir 1 dakika da bir yeniler oluşturulan damga kullanıcı tarayıcısına ve önbelleğe (storage) kaydedilir. Kullanıcı sistemi kullanırken sayfa yenilemelerinde ön bellekteki güvenlik damgası ( token ) kullanıcının tarayıcısına kaydedilen çerezin değeri ile eşleşmez ise kullanıcı sistemden dışarı atılır. Böylelikle session hijacking gibi güvenlik tehditlerinin önüne geçilmiş olunur. Yenileme zamanı auth konfigüre dosyasından ayarlanabilir bir değerdir. Eğer daha güçlü bir koruma istiyorsanız bu bu süreyi 30 saniyeye düşürebilirsiniz.</td>
        </tr>
        <tr>
            <td>__rememberMe</td>
            <td>Kullanıcı giriş yaparken beni hatırla özelliğini kullandıysa bu değer <b>1</b> değerini aksi durumda <b>0</b> değerini içerir.</td>
        </tr>
        <tr>
            <td>__token</td>
            <td>Güvenlik çerezinin ( Security Token ) güncel değerini içerir.</td>
        </tr>
        <tr>
            <td>__type</td>
            <td>Yetkilendirme tiplerini içerir. Bu tipler sırasıyla şöyledir: <b>Guest, Unverified, Authorized, Unauthorized</b>.</td>
        </tr>
        <tr>
            <td>__time</td>
            <td>Kimliğin ilk oluşturulma zamanıdır. Microtime olarak oluşturulur ve unix time formatında kaydedilir.</td>
        </tr>

    </tbody>
</table>

Oluşturulan kullanıcı kimliğinin tümüne aşağıdaki yöntemle ulaşabilirsiniz.

```php
<?php
print_r($this->user->identity->getArray()); // Gives
/*
Array
(
    [__activity] => Array
        (
            [last] => 1413454236
        )

    [__isAuthenticated] => 1
    [__isTemporary] => 0
    [__lastTokenRefresh] => 1413454236
    [__rememberMe] => 0
    [__token] => 6ODDUT3FtmmXEZ70.86f40e86
    [__type] => Authorized
    [__time] => 1414244130.719945
    [id] => 1
    [password] => $2y$10$0ICQkMUZBEAUMuyRYDlXe.PaOT4LGlbj6lUWXg6w3GCOMbZLzM7bm
    [remember_token] => bqhiKfIWETlSRo7wB2UByb1Oyo2fpb86
    [username] => user@example.com
)
*/
```

## Paket Konfigürasyonu

Auth paketine ait konfigürasyon <kbd>app/config/auth.php</kbd> dosyasında tutulmaktadır. Bu konfigürasyona ait bölümlerin ne anlama geldiği aşağıda geniş bir çerçevede anlatılmıştır.

### Konfigürasyon değerleri tablosu

<table>
    <thead>
        <tr>
            <th>Anahtar</th>    
            <th>Açıklama</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td>adapter</td>
            <td>Adapter is used to authenticate against a particular type of authentication service, such as Database (RDBMS or NoSQL), or file-based.</td>
        </tr>
        <tr>
            <td>memory[key]</td>
            <td>This is Redis key, using same key may cause collison with your other projects. It should be replace with your "projectameAuth" ( e.g. frontendAuth, backendAuth ).</td>
        </tr>
        <tr>
            <td>memory[storage]</td>
            <td>Auth class uses a memory container to speed up your application default driver is Redis.</td>
        </tr>
        <tr>
            <td>memory[block][permanent][lifetime]</td>
            <td>Before the login action if verification is disabled by the <kbd>$this->user->login->disableVerification()</kbd> method user identity data is stored into <b>permanent</b> memory block otherwise it will be stored in temporary block. Permanent block expires after <b>3600</b> seconds by default. To speed up your login this feature prevents more than one login queries within the specified time period.</td>
        </tr>

        <tr>
            <td>memory[block][temporary][lifetime]</td>
            <td>Before the login action if verification is enabled by the <kbd>$this->user->login->enableVerification()</kbd> method user identity data is stored into <b>temporary</b> memory block otherwise it will be stored in permanent block. Temporary block expires after <b>300</b> seconds by default. 

            The temporary data is designed for the user <b>verification</b> protocols ( verification by <b>phone call</b>, verification by <b>SMS</b> etc.).
            <br />
            If the verification code you have generated is confirmed by the user within the specific time, the user information in the temporary data is updated and the user becomes authorized with the method <kbd>$this->user->login->authenticateVerifiedIdentity()</kbd>. Otherwise, while you do not use this method the temporary identity information will be <b>lost</b>.
            </td>
        </tr>

        <tr>
            <td>security[cookie]</td>
            <td>This precaution is taken for preventing user information and session id from being stolen. Randomly generated security token with the information special to browser information of the user is saved to memory container and these tokens are renewed within a certain periods (1 minute by default). When the token is renewed, the verification function runs and if the token in the memory is not equal to the token in the browser of the user, the session of the user is expired. The expiration time of a security token is recommended to be the same with the time of the rememberMe cookie (6 months by default).</td>
        </tr>

        <tr>
            <td>security[passwordNeedsRehash][cost]</td>
            <td>It is the length of the password hash and it should not exceed the 10, otherwise it may cause performance problems in your application.<b>Note:</b> If user password needs to be rehashed for the security purposes, run this method  <kbd>$this->user->identity->getPasswordNeedsReHash()</kbd> . If renewed, the method returns new hash password in the array format and the user password field in your database must be updated with the returned value. </td>
        </tr>

        <tr>
            <td>login[rememberMe]</td>
            <td>If the user wants their information to be kept in the browser permanently, a cookie with the name <b>__rm</b> is created and saved to a browser(The default expiration time of the cookie is 6 months).When the user comes different times, if this cookie exists in the user's browser and the user id is not defined in the session, this value is saved to the key <b>$_SESSION['__isAuthenticated\Identifier']</b>. The user information is recalled with the method <b>Auth\Recaller->recallUser($rememberToken)</b> and the users starts to be active in the site. This value is updated in the both database and cookie on every login and logout.</td>
        </tr>

        <tr>
            <td>login[session][regenerateSessionId]</td>
            <td>This is a security preacution for session id not to be stolen, if this option is active session id is updated on every login and the user information on the session is not removed.</td>
        </tr>

        <tr>
            <td>login[session][deleteOldSessionAfterRegenerate]</td>
            <td>If this option is active, during a login operation after session is regenerated, all the created information in the user's session is removed.</td>
        </tr>

        <tr>
            <td>activity[singleSignOff]</td>
            <td>Single sign-off is the property whereby a single action of signing out terminates access to multiple sessions. If this option is active, all sessions of the user expired and only the last session remains active.</td>
        </tr>
    </tbody>
</table>


## Auth paketine ait sınıflara tek bir yerden erişim

Auth paketi sınıflarına erişim user servisi tarafından sağlanmaktadır bu servis önceden <b>.app/classes/Service</b> dizininde <b>User.php</b> olarak konfigure edilmiştir. Uygulamanınızın sürdürülebilirliği açısından bu servis üzerinde database provider haricinde değişiklilik yapmamanız önerilir. <b>User</b> sınıfı auth servisine ait olan <b>UserLogin</b>, <b>UserIdentity</b> ve <b>UserActivity</b> gibi sınıfları bu servis üzerinden kontrol eder, böylece auth paketi içerisinde kullanılan tüm public sınıf metodlarına tek bir sınıf üzerinden erişim sağlanmış olur.

Örneğin UserLogin sınıfı metodlarına user servisi üzerinden aşağıdaki şekilde erişim sağlanır.

```php
$this->user->login->method();
```

UserIdentity için bir örnek

```php
$this->user->identity->method();
```

ve UserActivity için

```php
$this->user->activity->method();
```

### Bir Oturum Açma Denemesi

```php
<?php
$this->user->login->disableVerification();  // default disabled
$this->user->login->attempt(
    array(
        Auth\Constant::IDENTIFIER => $this->post['email'], 
        Auth\Constant::PASSWORD => $this->post['password']
    ),
    $this->post['rememberMe']
);
```

### Bir Oturum Açma Örneği

Membership adı altında bir dizin açalım be login controller dosyamızı bu dizin içerisinde koyalım.

```php
+ app
+ assets
- controllers 
    - membership
        + view
        login.php
```

```php
<?php

namespace Membership;

use Auth\Constant,
    Event\User;

Class Login extends \Controller
{
    public function load()
    {
        $this->c->load('url');
        $this->c->load('form');
        $this->c->load('view');
        $this->c->load('request');
        $this->c->load('service/user');
        $this->c->load('flash/session as flash');
        $this->c->load('event')->subscribe(new User($this->c));   // Listen user events
    }

    public function index()
    {
        if ($this->request->isPost()) {

            $this->c->load('validator'); // load validator
            $this->validator->setRules('email', 'Email', 'required|email|trim');
            $this->validator->setRules('password', 'Password', 'required|min(6)|trim');

            if (  ! $this->validator->isValid()) {
                $this->form->setErrors($this->validator);
            } else {

                // $this->user->login->enableVerification();

                $result = $this->user->login->attempt(
                    array(
                        Constant::IDENTIFIER => $this->validator->value('email'), 
                        Constant::PASSWORD => $this->validator->value('password')
                    ),
                    $this->request->post('rememberMe')
                );
                if ($result->isValid()) {
                    $this->flash->success('You have authenticated successfully.');
                    $this->url->redirect('membership/login');
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

/* End of file login.php */
/* Location: .controllers/membership/login.php */
```

### Oturum Açma Sonuçları

Oturum açma denemesi yapıldığında AuthResult sınıfı ile sonuçlar doğrulama filtresinden geçer ve oluşan hata kodları ve mesajlar bir dizi içerisine kaydedilir.

```php
<?php
$result = $this->user->login->attempt(
    array(
        Auth\Constant::IDENTIFIER => $this->request->post('email'), 
        Auth\Constant::PASSWORD => $this->request('password')
    ),
    $this->post['rememberMe']
);

if ($result->isValid()) {

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
            <td>Genel başarısız yetkilendirme.</td>
        </tr>
        <tr>
            <td>-1</td>
            <td>AuthResult::FAILURE_IDENTITY_AMBIGUOUS</td>
            <td>Kimlik belirsiz olması nedeniyle başarısız yetkilendirme.( Sorgu sonucunda 1 den fazla kimlik bulunduğunu gösterir ).</td>
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
            <td>Yetkilendirilme onayı aktif iken geçici kimlik bilgilerin henüz doğrulanmadığını gösterir.</td>
        </tr>
        <tr>
            <td>1</td>
            <td>AuthResult::SUCCESS</td>
            <td>Yetkilendirilme başarılıdır.</td>
        </tr>

    </tbody>
</table>


### Auth Sabitleri ve Kimlik Sınıfları 

Uygulamanın esnek çalışması için kimlik classları ve sabit (constant) tanımlamaları <b>app/classes/Auth</b> klasörü altında gruplanmıştır. Bu klasör o2 auth paketi ile senkron çalışır ve aşağıdaki dizindedir.

```php
- app
    - classes
        - Auth
            Identities
                - AuthorizedUser
                - GenericUser
        + Provider
        Constant.php
```

AuthorizedUser yetkili kullanıcıların kimliklerine ait metodları, GenericUser sınıfı ise yetkisiz yani Guest diye tanımladığımız kullanıcıların kimliklerine ait metodları içerir. Bu sınıflar <b>get</b> metodu kullanıcı kimliklerinden <b>okuma</b> ve <b>set</b> metodu ile de kimliklere <b>yazma</b> işlemlerini yürütülerer. Bu sınıflara metodlar ekleyerek ihtiyaçlarınıza göre düzenleme yapabilirsiniz fakat <b>Obullo\Auth\Identities\IdentityInterface</b> sınıfı içerisindeki tanımlı metodlardan birini bu sınıflar içerisinden silmemeniz gerekir.

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
            <td>Auth\Constant</td>
            <td>Auth paketine ait öntanımlı tablename, id, password gibi sabitleri içerir.</td>
        </tr>
        <tr>
            <td>Auth\Identities\GenericUser</td>
            <td>Ziyaretçi (Guest) kullanıcısına ait genel kimlik profilidir.</td>
        </tr>
        <tr>
            <td>Auth\Identities\AuthorizedUser</td>
            <td>Yetkilendirilmiş kullanıcıya ait kimlik profilidir.</td>
        </tr>
        <tr>
            <td>Auth\Provider\UserProvider</td>
            <td>Database sorgularını yöneten ve sorgular üzerinde değişiklik yapabileceğiniz ara yüzdür.</td>
        </tr>
    </tbody>
</table>


## UserIdentity Sınıfı

------

The class Identity manages the identity information and does the operations below:

* Reads data from the identity and saves the data to identity  
* Checks if the user has identity
* Checks if the identity is authorized
* Checks if the identity is permanent or not
* Makes the identity passive ( logout )
* Expires the identity ( destroy )
* Remembers the identity ( remeberMe ), removes the identity from the cookie ( forgetMe )


## UserActivity Sınıfı

------

The classs Activity acts as a container to manage the meta data of the logged in users. The instant information like the last action of the user is on which page is sent to the identity data from this container. In order for information to be written on the memory, the update() method needs to be run once at the bottom. When the user logs in <b>sid</b> (session id) value is sent to the inside of the activity data by default.

#### Adding activity data and update.

```php
<?php
$this->user->activity->set('date', time());
$this->user->activity->update();

// __activity a:3:{s:3:"sid";s:26:"f0usdabogp203n5df4srf9qrg1";s:4:"date";i:1413539421;}
```


### Extending to UserProvider

O2 Auth paketi kullanıcıya ait database fonksiyonlarını servis içerisinden Obullo\Auth\AuthUserProvider sınfından çağırmaktadır. Bu sınıfa genişlemek için önce Service/User sınıfından provider ı Auth\UserProvider olarak değiştirmeniz gerekmektedir.


```php

namespace Service;

use Obullo\Auth\UserService,
    Service\ServiceInterface;

Class User implements ServiceInterface
{
    /**
     * Registry
     *
     * @param object $c container
     * 
     * @return void
     */
    public function register($c)
    {            
        $c['user'] = function () use ($c) {
            return new UserService($c, $c->load('return service/provider/db'));
        };;
    }
}
```

Bunun için önce <b>app/classes/Auth/Provider</b> klasörünü yaratın. Daha sonra Database user provider aşağıdaki gibi yaratarak AbstractUserProvider sınıfına genişlemeniz gerekmektedir. Bunu yaparken UserProviderInterface içerisindeki  kurallara bir göz atın.

Aşağıdaki örnek olarak verimiştir aşağıdaki sınıfı ihtiyaçlarınıza göre değiştirebilirsiniz. Bunun içib Obullo\Auth\AbstractUserProvider sınıfına bakın ve override etmek istediğiniz method yada değişkenleri UserProvider sınıfı içersine dail edin.


```php

<?php

namespace Auth\Provider;

use Obullo\Auth\UserProviderInterface,
    Obullo\Auth\AuthUserProvider,
    Auth\Identities\GenericUser,
    Auth\Identities\AuthorizedUser,
    Auth\Constant;

/**
 * O2 Auth - User Database Provider
 *
 * @category  Auth
 * @package   Provider
 * @author    Obullo Framework <obulloframework@gmail.com>
 * @copyright 2009-2014 Obullo
 * @license   http://opensource.org/licenses/MIT MIT license
 * @link      http://obullo.com/package/auth
 */
Class UserProvider extends AuthUserProvider implements UserProviderInterface
{
    /**
     * Constructor
     * 
     * @param object $c  container
     * @param object $db database
     */
    public function __construct($c, $db)
    {
        parent::__construct($c, $db);

        $this->tablename = 'users';                     // Db users tablename
        $this->rememberTokenColumn = 'remember_token';  // RememberMe token column name

        $this->userSQL = 'SELECT * FROM %s WHERE BINARY %s = ?';      // Login attempt SQL
        $this->recalledUserSQL = 'SELECT * FROM %s WHERE %s = ?';     // Recalled user for remember me SQL
        $this->rememberTokenUpdateSQL = 'UPDATE %s SET %s = ? WHERE BINARY %s = ?';  // RememberMe token update SQL
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
        // return parent::execQuery($user);
        
        $this->db->prepare($this->userSQL, array($this->tablename, Constant::IDENTIFIER));
        $this->db->bindValue(1, $user->getIdentifier(), PARAM_STR);
        $this->db->execute();

        return $this->db->row();  // returns to false if fail
    }

}

// END UserProvider.php File
/* End of file UserProvider.php

/* Location: .app/classes/Auth/Provider/UserProvider.php */
```


### Events

By default we have two active user event in event/user class. Auth class use these methods when you use service/user class.

If you want you can release afterLogin and afterLogout events using fire method.

```php
<?php

namespace Event;

use Obullo\Auth\AuthResult,
    Obullo\Auth\User\UserIdentity;

Class User
{
    /**
     * Handle user login attempts
     *
     * @param object $authResult AuthResult object
     * 
     * @return void
     */
    public function onLoginAttempt(AuthResult $authResult)
    {
        if ( ! $authResult->isValid()) {

            // Store attemtps

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
        $event->listen('login.attempt', 'Event\User.onLoginAttempt');
    }

}

// END User class

/* End of file User.php */
/* Location: .Event/User.php */
```



### Login Reference

------

```php
<?php
$this->c->load('service/user');
$this->user->login->method();
```

### $this->user->login->enableVerification();

If verification enabled, after successfull login memory storage creates a temporary identity. After a while temporary auth delete by storage if its not verified by the user.

### $this->user->login->disableVerification();

Disabled verification option.

### $this->user->login->attemp(array $credentials, $rememberMe = false);

Do login attempt and if login success gives authority and identity to the user.

### $this->user->login->authenticateVerifiedIdentity();

After verification, method authenticate temporary identity and removes old temporary data.

### $this->user->login->validate(array $credentials);

Validate a user's credentials without authentication.

### $this->user->login->validateCredentials(AuthorizedUser $user, array $credentials);

Validate a user against the given credentials.

$this->user->login->getAdapter();

Returns user service adapter object.

$this->user->login->getStorage();

Returns to user service storage object.

### Identity Reference

------

```php
<?php
$this->c->load('service/user');
$this->user->identity->method();
```

### $this->user->identity->exists();

Checks identity block available in memory. If yes returns to <b>true</b> otherwise <b>false</b>.

### $this->user->identity->check();

if user authenticated returns to <b>true</b> otherwise <b>false</b>.

### $this->user->identity->isVerified();

if user is verified () after successfull login returns to <b>true</b> otherwise <b>false</b>.

### $this->user->identity->guest();

Checks if the user is guest, if so, it returns to <b>true</b> otherwise <b>false</b>.

### $this->user->identity->isTemporary();

Returns to <b>1</b> if user authenticated on temporary memory block otherwise <b>0</b>.

### $this->user->identity->logout();

Logs out user, sets __isAuthenticated key to <b>0</b>. This method <kbd>does not destroy</kbd> the user <kbd>sessions</kbd>. It will just set authority of user to <b>0</b>.

**Note:** When you use logout method, user logins will work on memory storage if cached auth exists.

### $this->user->identity->destroy();

Destroys all identity stored in memory. 

### $this->user->identity->forgetMe();

Removes the rememberMe cookie.

### $this->user->identity->refreshRememberToken(GenericUser $genericUser);

Regenerates rememberMe token in <b>database</b>.

**Note:** When you use destroy method, user identity will removed from storage then new user login will do query to database for one time.


### Identity "Set" Methods

------

### $this->user->identity->variable = 'value'

Set a value to identity array.

### unset($this->user->identity->variable)

Remove value from identity array.

### $this->user->identity->setRoles(int|string|array $roles);

Set user roles to identity data.

### $this->user->identity->setArray(array $attributes)

Reset identity attributes with new values.


### Identity "Get" Methods

------

### $this->user->identity->getIdentifier();

Get the unique identifier for the user.

### $this->user->identity->getPassword();

Returns to hashed password of the user.

### $this->user->identity->getType();

Get user type who has successfull memory token using by their session identifier. User types : <b>UNVERIFIED, AUTHORIZED</b>.

### $this->user->identity->getRememberMe();

Get rememberMe option if user choosed returns to <b>1</b> otherwise <b>0</b>.

### $this->user->identity->getPasswordNeedsReHash();

Checks the password needs rehash if yes returns to <b>array</b> that contains new hashed password otherwise <b>false</b>.

### $this->user->identity->getTime();

Returns to creation time of identity. ( Php Unix microtime ).

### $this->user->identity->getArray()

Returns to all user identity data ( attributes of user ).

### $this->user->identity->getToken();

Returns to security token.

### $this->user->identity->getRoles();

Gets role(s) of the user.


**Note:** You can define your own methods into <kbd>app/classes/Auth/Identities/AuthorizedUser</kbd> class.


### Activity Reference

------


Activity data contains online user activity data: lastActivity time or and any analytics data you want to add.

```php
<?php
$this->c->load('service/user');
$this->user->activity->method();
```

### $this->user->activity->set($key, $val);

Add item to activity data array.

### $this->user->activity->get($key);

Fetches an item from activity data array.

### $this->user->activity->update();

Updates all activity data if $this->user->activity->set(); method used before on this method.

### $this->user->activity->remove();

Removes all activity data from auth container.