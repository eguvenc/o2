
## O2 Yetkilendirme ( Authentication )

Yetkilendirme paketi yetkilendirme adaptörleri ile birlikte çeşitli ortak senaryolar için size bir API sağlar. O2 yetkilendirme yalnızca yetkilendirme ( <b>authentication</b> ) ile ilgilidir ve yetki ( authorization ) ile ilgili herhangi bir şeyi içermez. Yetkiler ile ilgili daha fazla bilgi için lütfen <b>Permissions</b> paketine bakınız. 

O2 yetkiledirme; 

* Hafıza depoları, ( Storages ) 
* Adaptörler,
* Olaylar ( Events )
* Kullanıcı kimlikleri
* Kullanıcı sorguları arayüzü ( UserProvider class )
* Yetkilendirme onaylandırma ( Verification )
* Güvenlik çerezi doğrulama önlemi, ( Security token validation )
* Oturum id sini yeniden yaratma, ( Session regenerate )
* Tarayıcı türünü doğrulama ( User agent validation )
* Hatırlatma çerezi ve beni hatırla ( Remember me token )

gibi mevcut özellikleri ile size esnek, hızlı ve güvenli bir yetkilendirme servisi sağlar ayrıca depolama birimi olarak <b>redis</b> kullandığınızda size online kullanıcı kimliklerini görüntüleme ve bu kimliklere ulaşarak çeşitli istatistik verileri oluşturabilmenize de imkan tanır.

## Sınıfı yüklemek

Yetkilendirme paketi sınıflarına erişim user servisi üzerinden sağlanır, bu servis önceden <b>.app/classes/Service</b> dizininde <b>User.php</b> olarak konfigure edilmiştir. Uygulamanınızın sürdürülebilirliği açısından bu servis üzerinde database provider haricinde değişiklilik yapmamanız önerilir. <b>User</b> sınıfı yetkilendirme servisine ait olan <b>UserLogin</b>, <b>UserIdentity</b> ve <b>UserActivity</b> gibi sınıfları bu servis üzerinden kontrol eder, böylece paket içerisinde kullanılan tüm public sınıf metodlarına tek bir sınıf üzerinden erişim sağlanmış olur.

User servisi bir kez çağrıldığı zaman bu servis içerisinden ilgili kütüphane metotları çalıştırılabilir.

```php
$this->c->load('service/user');
$this->user->class->method();
```

Aşağıda verilen örnek prototipler size yetkilendirme sınıfı metodlarına <b>user</b> servisi üzerinden nasıl erişim sağlandığı hakkında bir fikir verebilir.

<b>UserLogin</b> için bir örnek

```php
$this->user->login->method();
```

<b>UserIdentity</b> için bir örnek

```php
$this->user->identity->method();
```

<b>UserActivity</b> için bir örnek

```php
$this->user->activity->method();
```


## Adaptörler

Yetkilendirme adaptörleri yetkilendirme servisinde esneklik için <b>Database</b> (RDBMS or NoSQL) veya <b>dosya tabanlı</b> gibi farklı türde kimlik doğrulama biçimleri olarak kullanılırlar.

Farklı adaptörlerin çok farklı seçenekler ve davranışları olması muhtemeldir , ama bazı temel şeyler kimlik doğrulama adaptörleri arasında ortaktır. Örneğin, kimlik doğrulama hizmeti sorgularını gerçekleştirmek ve dönen sonuçlar yetkilendirme adaptörleri için ortak kullanılır.

## Hazıfa Deposu ( Storage )

Hazıfa deposu yetkilendirme esnasında kullanıcı kimliğini ön belleğe alır ve tekrar tekrar oturum açıldığında database ile bağlantı kurmayarak uygulamanın performans kaybetmesini önler. Ayrıca yetkilendirme onayı açıksa onaylama işlemi için geçici bir kimlik oluşturulur ve bu kimliğe ait bilgiler yine hafıza deposu aracılığıyla önbellekte tutulur.

**Not:** O2 Yetkilendirme şu anda depolama için sadece <b>Redis</b> sürücüsünü desteklemektedir. Ubuntu altında redis kurulumu hakkında bilgi almak için <b>warmup</b> adı verilen dökümentasyon topluluğunun hazırladığı belgeden yararlanabilirsiniz. <a href="https://github.com/obullo/warmup/tree/master/Redis">Redis Installation</a>.

## Akış Şeması

Aşağıdaki akış şeması bir kullanıcının yetkilendirme aşamalarından nasıl geçtiği ve yetkilendirme servisinin nasıl çalıştığı hakkında size bir ön bilgi verecektir:

* [Şemayı görmek için buraya tıklayınız](/Authentication/Docs/images/flowchart.png?raw=true)

Şemada görüldüğü üzere <b>GenericUser</b> ve <b>AuthorizedUser</b> olarak iki farklı durumu olan bir kullanıcı sözkonusudur. GenericUser <b>yetkilendirilmemiş</b> AuhtorizedUser servis tarafından <b>yetkilendirilmiş</b> kullanıcıdır.

Akış şemasına göre GenericUser login butonuna bastığı anda ilk önce hafıza bloğuna bir sorgu yapılır ve daha önceden kullanıcının yetkilendirilmiş kalıcı kimliği olup olmadığında bakılır eğer hafıza bloğunda kalıcı yetkilendirme kaydı var ise kullanıcı kimliği buradan yok ise database adaptörüne sorgu yapılarak elde edilir.

Eğer kullanıcı kimliği database sorgusu yapılarak elde edilmişse elde edilen kimlik kartı performans için tekrar hafıza bloğuna yazılır.

Buradan sonraki işlemleri anlayabilmemiz için önce yetkilendirme onaylamasının ne olduğunu anlamamız gerekir.

<b>Yetkilendirme onaylaması</b>, kullanıcı başarılı olarak giriş yaptıktan sonra kullanıcı kimliğinin onay için bekletilmesi aşamasıdır. Onay özelliği açık ise kullanıcı kimliği hafıza bloğuna geçiçi olarak kaydedilir. Kullanıcın geçici kimliğini onaylaması sizin ona <b>email</b>, <b>sms</b> yada <b>mobil çağrı</b> gibi yöntemlerinden herhangi biriyle göndermiş olacağınız onay kodu ile gerçekleşir. Eğer kullanıcı 300 saniye içerisinde ( bu konfigürasyon dosyasından ayarlanabilir bir değişkendir ) kullanıcı kendisine gönderilen onay kodunu onaylayamaz ise geçiçi kimlik kendiliğinden yok olur.

Eğer kullanıcı onay işlemini başarılı bir şekilde gerçekleştirir ise <b>temporary</b> hafıza bloğuna kaydedilmiş geçici kimlik artık herhangi bir database sorgusu ve password hash işlemi olmadadan hafıza bloğuna <b>permanent</b> yani kalıcı olarak yazılır ve kullanıcı yetkilendirilmesi başarılı bir şekilde gerçekleşmiş olur.

Kullanıcın onaya düşmesi yani yetkilendirme onaylama varsayılan olarak kapalıdır. Aşağıdaki method login attemp fonksiyonun üzerinde kullanılırsa yetkilendirme onaylama özelliği açık hale gelecektir.

**Note:** Bu paket programlanırken geçici kimlik "__temporary" kalıcı kimlik ise "__permanent" simgesi ile ifade edilmiş aynı zamanda hafıza depolarında bu ifadeler kullanılmıştır.

#### Yetkilendirme onayının açılmasına bir örnek:

```php
$this->user->login->enableVerification();
```

Yetkilendirilme onayını aktif hale gelebilmesi için bu fonksiyonun oturum denemesi fonksiyondan önce kullanılması gerekmektedir. Bu fonksiyon kullanıldığında eğer oturum açma başarılı ise hafıza bloğunda geçici bir kimlik oluşturulur. Eğer sizin tarafınızdan yaratılıp gönderilecek olan onay kodunu kullanıcı onaylayamaz ise geçici kimlik 300 saniye içerisinde kendiliğinden yok olur. Fonksiyonun kullanılmadığı durumda ise tüm kullanıcılar sistemde kalıcı olarak oturum açmış olurlar.

Bu aşamadan sonra onaya düşen kullanıcı için bir onay kodu oluşturup bunu ona göndermeniz gerekmektedir. Onay kodu onaylanırsa bu onaydan sonra aşağıdaki method ile kullanıcıyı kalıcı olarak yetkilendirebilirsiniz.

#### Onaylanmış kimliğin kalıcı hale getirilmesine bir örnek:

```php
$this->user->login->authenticateVerifiedIdentity();
```

Yukarıdaki method geçici kimliği olan kullanıcıyı kalıcı kimlikli bir kullanıcı haline dönüştürür. Kalıcı kimliğine kavuşan kullanıcı artık sistemde yetkili konuma gelir.

Diğer bir durum yetkilendirme onayının kapalı olması yani varsayılan durumdur. Onaylamanın kapalı olması durumunda yani sisteminizde yetkilendirme öncesi onay gibi bir özellik kullanmıyor iseniz yukarıdaki onaylama metodlarının hiçbirini kullanmak zorunda kalmazsınız.

Akış şeması üzerinden gidersek yetkilendirme onayının kapalı olması durumunda varsayılan işlemler devam eder ve kullanıcı kalıcı (__permanent) olarak hafıza bloğuna yazılır. Kalıcılık kullanıcı kimliğinin önbelleklenmesi (cache) lenmesi demektir. Önbelleklenen kullanıcının kimliği tekrar oturum açıldığında database sorgusuna gidilmeden sağlanmış olur. Kalıcı önbelleklenme süresi konfigürasyon dosyasından ayarlanabilir bir değişkendir.


## Redis Deposu

Yetkilendirme sınıfı hafıza deposu için varsayılan olarak redis kullanır. Aşağıdaki resim kullanıcı kimliklerinin hafıza deposunda nasıl tutulduğunu göstermektedir.

![PhpRedisAdmin](/Authentication/Docs/images/redis.png?raw=true "PhpRedisAdmin")

Vardayılan hafıza sınıfı auth konfigürasyonundan değiştirilebilir.

```php
<?php

'cache' => array( 
        'key' => 'Auth',
        'storage' => '\Obullo\Authentication\Storage\Redis',
        'block' => array(

        )
    ),
```

Redis dışında bir çözüm kullanıyorsanız kendi hafıza depolama sınfınızı auth konfigürasyon dosyasından değiştererek kullanabilirsiniz.

### Yetkilendirme paketinin kullandığı rezerve edilmiş anahtarlar :

Yetkilendirme paketi kendi anahtarlarını oluştururup bunları hafıza deposunu kaydederken 2 adet underscore önekini kullanır. Yetkilendirme paketine ait olan bu anahtarlar yazma işlemlerinde çakışma olmaması için bu "__" önek kullanılarak ayırt edilir.

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

Yukarıda görüldüğü gibi çift underscore karakteri ile başlayan anaharlar paket tarafından kullanılan (rezerve) diğerleri ise size ait verilerin kaydedildiği anahtarlardır. Diğer bir anahtar <b>__activity</b> anahtarı ise yetkilendirilmiş anlık kullanıcı istatistikleri ile igili veriler için ayrılmış olan size ait bir anahtardır.

## Paket Konfigürasyonu

Yetkilendirme paketine ait konfigürasyon <kbd>app/config/auth.php</kbd> dosyasında tutulmaktadır. Bu konfigürasyona ait bölümlerin ne anlama geldiği aşağıda geniş bir çerçevede anlatılmıştır.

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
            <td>Yetkilendirme adaptörleri yetkilendirme servisinde esneklik için <b>Database</b> (RDBMS or NoSQL) veya <b>dosya-tabanlı</b> gibi farklı türde kimlik doğrulama biçimleri olarak kullanılırlar.</td>
        </tr>
        <tr>
            <td>cache[key]</td>
            <td>Bu değer auth paketinin kayıt olacağı anahtarın önekidir. Bu değeri her proje için farlı girmeniz projelerinizin karışmaması için tavsiye edilir. Bu değer "projectameAuth" ( örnek olarak frontendAuth, backendAuth ) olarak girilebilir.</td>
        </tr>
        <tr>
            <td>cache[storage]</td>
            <td>Hazıfa deposu yetkilendirme esnasında kullanıcı kimliğini ön belleğe alır ve tekrar tekrar oturum açıldığında database ile bağlantı kurmayarak uygulamanın performans kaybetmesini önler.Varsayılan depo Redis tir.</td>
        </tr>
        <tr>
            <td>cache[block][permanent][lifetime]</td>
            <td>Login denemesinden önce eğer yetkilendirme onayı devre dışı yada kullanıcı kalıcı olarak onaylandı ise kullanıcı kimliği verileri <b>permanent</b> hafıza bloğuna bloğuna kaydedilir. Kalıcı blokta ön belleğe alınan veriler varsayılan olarak <b>7200</b> saniye sonra yok olur.</td>
        </tr>
        <tr>
            <td>cache[block][temporary][lifetime]</td>
            <td>Login denemesinden önce eğer yetkilendirme onayı açık ise kullanıcı kimliği verileri <b>temporary</b> hafıza bloğuna kaydedilir. Geçici bloğa kaydedilmiş veriler <b>300</b> saniye sonrasında varsayılan olarak yok olur.Geçici blok yetkilendirme onaylandırma durumları için dizayn edilmiştir.
            </td>
        </tr>
        <tr>
            <td>security[cookie]</td>
            <td>Güvenlik çerezi ( Security Token ) varsayılan olarak kendisini her bir 1 dakika da bir yeniler oluşturulan damga kullanıcı tarayıcısına ve önbelleğe (storage) kaydedilir. Kullanıcı sistemi kullanırken sayfa yenilemelerinde ön bellekteki güvenlik damgası ( token ) kullanıcının tarayıcısına kaydedilen çerezin değeri ile eşleşmez ise kullanıcı sistemden dışarı atılır. Böylelikle session hijacking gibi güvenlik tehditlerinin önüne geçilmiş olunur. Yenileme zamanı auth konfigürasyon dosyasından ayarlanabilir bir değerdir. Eğer daha güçlü bir koruma istiyorsanız bu bu süreyi 30 saniyeye düşürebilirsiniz. Bu çereze ait <b>isValidToken()</b> adındaki kontrol fonksiyonu <b>Obullo/Authentication/User/UserIdentity</b> sınıfı içerisinde çalışır. Esneklik için bu fonksiyon Event kütüphanesi yardımı ile bir olay olarak ilan edilmiştir. Güvenlik çerezi değeri yanlış olması durumda event sınıfı <b>auth.invalidToken</b> olayı ilan edilir ve bu olayı <b>app/classes/Event/User</b> sınıfı içerisindeki <b>onInvalidToken()</b> metodu dinler.onInvalidToken() fonksiyonunun varsayılan işlevi süresi geçmiş yada hatalı olan güvenli çerezi ile karşılaşıldığında kullanıcıyı sistem dışına yönlendirmektir.Eğer bu davranışı özelleştirmek istiyorsanız uygulama altında <b>app/classes/Event/User</b> sınıfı içerisinde yeralan onInvalidToken() fonksiyonu içeriğini değiştirebilirsiniz.</td>
        </tr>
        <tr>
            <td>security[passwordNeedsRehash][cost]</td>
            <td>Bu değer crypt/password kütüphanesi tarafından şifre hash işlemi için kullanılır. Varsayılan değer 6 dır fakat maximum vermeniz gereken değer 8 ila 12 arasında olmalıdır aksi takdirde uygulamanız yetkilendirme aşamasında performans sorunları yaşayabilir. 8 veya 10 değerleri orta donanımlı bilgisayarlar için 12 ise güçlü donanımlı çekirdek sayısı fazla bilgisayarlar için uygun olabilir.</td>
        </tr>
        <tr>
            <td>login[rememberMe]</td>
            <td>Eğer kullanıcı beni hatırla özelliğini kullanarak giriş bilgilerini kalıcı olarak tarayıcısına kaydetmek istiyorsa  <b>__rm</b> isimli bir çerez ilk oturum açmadan sonra tarayıcısına kaydedilir. Bu çerezin sona erme süresi varsayılan olarak 6 aydır. Kullanıcı farklı zamanlarda uygulamanızı ziyaret ettiğinde eğer bu çerez ( remember token ) tarayıcısında kayıtlı ise <b>Authentication\Recaller->recallUser($token)</b> metodu çalışmaya başlar ve beni hatırla çerezi database de kayıtlı olan değer ile karşılaştırılır değerler birbiri ile aynı ise kullanıcı sisteme giriş yapmış olur. Güvenlik amacıyla her oturum açma (login) ve kapatma (logout) işlemlerinden sonra bu değer çereze ve veritabanına yeniden kaydedilir.</td>
        </tr>
        <tr>
            <td>login[session][regenerateSessionId]</td>
            <td>Session id nin önceden çalınabilme ihtimaline karşı uygulanan bir güvenlik yöntemlerinden bir tanesidir. Bu opsiyon aktif durumdaysa oturum açma işleminden önce session id yeniden yaratılır ve tarayıcıda kalan eski oturum id si artık işe yaramaz hale gelir.</td>
        </tr>
        <tr>
            <td>login[session][deleteOldSessionAfterRegenerate]</td>
            <td>Eğer bu opsiyon pasif (false) durumda ise oturum açma işleminden sonra yeniden yaratılan session id verileri içerisine kullanıcının oturum açmadan önceki session id verileri kopyalanır. Aksi durumda bu opsiyon açık (true) ise eski session id verileri oturum açılır açılmaz yok edilir.</td>
        </tr>
        <tr>
            <td>activity[uniqueSession]</td>
            <td>Tekil oturum opsiyonu aktif olduğunda aynı kimlik bilgileri ile yalnızca bir kullanıcı oturum açabilir. En son açılan oturum her zaman aktif kalırken eski oturumlar otomatik olarak silinir. Fakat bu fonksiyon <b>app/classes/Http/Filters</b> dizinindeki auth filtresi çalıştırıldığı zaman devreye girer. Filtreyi çalıştırmak için onu <b>route</b> yapısına tutturmanız gerekmektedir. Filtreler hakkında daha geniş bilgiye <b>router</b> paketi dökümentasyonunu inceleyerek ulaşabilirsiniz. Yetkilendirme filtresi içerisindeki <b>$this->user->activity->update();</b> metodu kullanıcının en son aktivite zamanı gibi verilerini günceller.UniqueSession özelliği yine bu metod içerisinden tetiklenmektedir ve daha fazla esneklik ve sürdürülebilirlik amacıyla bu metod event yönetimine bağlanmıştır, <b>app/classes/Event/User</b> sınıfı içerisindeki <b>onUniqueSession()</b> fonksiyonu içeriğini güncelleyerek tekil oturum işlevini kendi ihtiyaçlarınıza göre değiştirebilmeniz planlanmıştır.</td>
        </tr>
    </tbody>
</table>


### Bir Kalıcı Oturum Açma Denemesi

```php
<?php
$this->user->login->disableVerification();  // default disabled
$this->user->login->attempt(
    array(
        Auth\Constant::IDENTIFIER => $this->request->post('email'), 
        Auth\Constant::PASSWORD => $this->request->post('password')
    ),
    $this->request->post('rememberMe')
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
    $this->request->post('rememberMe')
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


### Yetkilendirilme Sabitleri ve Kimlik Sınıfları 

Uygulamanın esnek çalışması için kimlik classları ve sabit (constant) tanımlamaları <b>app/classes/Auth</b> klasörü altında gruplanmıştır. Bu klasör o2 auth paketi ile senkron çalışır ve aşağıdaki dizindedir.

```php
- app
    - classes
        - Authentication
            Identities
                - AuthorizedUser
                - GenericUser
        + Provider
```

<b>AuthorizedUser</b> yetkili kullanıcıların kimliklerine ait metodları, <b>GenericUser</b> sınıfı ise yetkisiz yani Guest diye tanımladığımız kullanıcıların kimliklerine ait metodları içerir. Bu sınıflar <b>get</b> metodu kullanıcı kimliklerinden <b>okuma</b>, <b>set</b> metodu ile de kimliklere <b>yazma</b> işlemlerini yürütülerer. Bu sınıflara metodlar ekleyerek ihtiyaçlarınıza göre düzenleme yapabilirsiniz fakat <b>Obullo\Authentication\Identities\IdentityInterface</b> sınıfı içerisindeki tanımlı metodlardan birini bu sınıflar içerisinden silmemeniz gerekir.

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
            <td>Yetkilendirme paketine ait öntanımlı sabitlerini (tablename, id, password gibi) içerir.</td>
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


### UserIdentity Sınıfı İşlevleri

------

Kullanıcı kimliği O2 paketi içerisindedir ve <b>app/Auth/Identities</b> içerisindeki AuthorizedUser sınıfına genişler. Bu sınıf aşağıdaki kimlik işlevlerini yönetir.

* Kimlikten veri okuma ve kimliğe veri kaydetme
* Kullanıcı kimliğinin olup olmadığı kontrolü
* Kullanıcı kimliğinin kalıcı olup olmadığı
* Kullanıcının kimliğinin oturumunu sonlandırma ( logout )
* Kullanıcı kimliğini tamamen yok etme ( destroy )
* Beni hatırla özelliği kullanılmışsa kullanıcı kimliğini çerezden kalıcı olarak silme forgetMe )


### UserActivity Sınıfı İşlevleri

------

Kullanıcı aktivite sınıfı yetkilendirilmiş kullancılara ait meta verilerini kaydeder. Son aktivite zamanı ve diğer eklemek istediğiniz harici anlık veriler bu sınıfı aracılığıyla activity key içerisinde tutulur.

#### Örnek bir aktivite verisi

```php
<?php
$this->user->activity->set('sid', $this->session->get('session_id'));
$this->user->activity->set('date', time());
$this->user->activity->update();

// __activity a:3:{s:3:"sid";s:26:"f0usdabogp203n5df4srf9qrg1";s:4:"date";i:1413539421;}
```

### Database Sorgularını Özelleştirmek için UserProvider Sınıfına Genişlemek

O2 yetkilendirme paketi kullanıcıya ait database fonksiyonlarını servis içerisinden <b>Obullo\Authentication\UserProvider</b> sınfından çağırmaktadır. Eğer mevcut database sorgularında değişlik yapmak istiyorsanız bu sınıfa genişlemek için önce auth konfigürasyon dosyasından provider anahtarını <b>Auth\UserProvider</b> olarak değiştirmeniz gerekmektedir.

Daha sonra <b>app/classes/Auth/Provider</b> klasörünü içerisine UserProvider.php dosyasını yaratarak aşağıdaki gibi UserProvider sınıfı içerisinden ObulloUserProvider sınıfına genişlemeniz gerekmektedir. Bunu yaparken UserProviderInterface içerisindeki yazım kurallarına bir göz atın.

Aşağıda O2 yetkilendirme paketi içerisindeki UserProviderInterface sınıfı görülüyor.

```php
<?php

interface UserProviderInterface
{
    public function __construct($c, $db);
    public function execQuery(GenericUser $user);
    public function execRecallerQuery($token);
    public function updateRememberToken($token, GenericUser $user);
}
```

Aşağıda sizin için bir provider örneği yaptık isterseniz bu örneği değiştererek ihtiyaçlarınıza göre kullanabilirsiniz. Bunun için <b>Obullo\Authentication\AbstractUserProvider</b> sınıfına bakın ve ezmek ( override ) istediğiniz method yada değişkenleri sınıfınız içerisine dail edin.


```php
<?php

namespace Auth\Provider;

use Obullo\Authentication\UserProviderInterface,
    Obullo\Authentication\UserProvider as ObulloUserProvider,
    Auth\Identities\GenericUser,
    Auth\Identities\AuthorizedUser,
    Auth\Constant;

Class UserProvider extends ObulloUserProvider implements UserProviderInterface
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

        $this->userSQL = 'SELECT * FROM %s WHERE BINARY %s = ?';      // Login attempt SQL
    }
    
    /**
     * Execute sql query
     *
     * @param object $user GenericUser object to get user's identifier
     * 
     * @return mixed boolean|array
     */
    public function execQuery(GenericUser $user)
    {
        // return parent::execQuery($user);
        
        $this->db->prepare($this->userSQL, array($this->tablename, Constant::IDENTIFIER));
        $this->db->bindValue(1, $user->getIdentifier(), PARAM_STR);
        $this->db->execute();

        return $this->db->rowArray();  // returns to false if fail
    }

}

// END UserProvider.php File
/* End of file UserProvider.php

/* Location: .app/classes/Auth/Provider/UserProvider.php */
```

### Olaylar ( Events )

Yetkilendirme paketine ait olaylar <b>app/classes/Event/User.php</b> sınıfı tarafından dinlenir. Bu sınıf içerisindeki en önemli olaylardan biri <b>onLoginAttempt()</b> olayıdır. Bu olay <b>Obullo/Authentication/User/UserIdentity</b> sınıfı içerisindeki loginAttempt metodu içerisinde <b>login.attempt</b> adı ile ilan edilmiştir. 

Aşağıdaki örnekte gösterilen <b>app/classes/Event/User.php</b> sınıfı onLoginAttempt() metodu <b>login.attempt</b> olayını dinleyerek oturum denemeleri anını ve bu andan sonra oluşan sonuçları kontrol edebilmenizi sağlar. 

Lütfen takip eden örneğe bir göz atın.

```php
<?php

namespace Event;

use Obullo\Authentication\AuthResult,
    Obullo\Authentication\User\UserIdentity;

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

Yukarıdaki örnekte <b>onLoginAttempt()</b> metodunu kullanarak oturum açma denemesinin başarılı olup olmaması durumuna göre oturum açma işlevine eklemeler yapabilir yetkilendirme sonuçlarınına göre uygulamanızın davranışlarını özelleştirebilirsiniz.


### Login Referansı

------

### $this->user->login->enableVerification();

Yetkilendirilme onayını aktif hale getirir.

### $this->user->login->disableVerification();

Yetkilendirilme onayını devre dışı bırakır.

### $this->user->login->attemp(array $credentials, $rememberMe = false);

Bu fonksiyon kullanıcı oturumunu açmayı dener ve AuthResult nesnesine döner.

### $this->user->login->authenticateVerifiedIdentity();

Kullanıcıyı kalıcı olarak yetkilendirir ve kalıcı kimliğe sahip olan kullanıcının geçici kimliğini önbellekten siler.

### $this->user->login->validate(array $credentials);

Yetkilendirme yapmadan kullanıcı Guest kimliği bilgilerine doğrulama işlemi yapar.Bilgiler doğruysa true değerine yanlış ise false değerine döner.

### $this->user->login->validateCredentials(AuthorizedUser $user, array $credentials);

AuthorizedUser kimliğine sahip kullanıcı bilgilerini dışarıdan gelen yeni bilgiler ile karşılaştırarak doğrulama yapar.

### $this->user->login->getAdapter();

Serviste kullanılan adaptör nesnesine geri döner.

### $this->user->login->getStorage();

Serviste kullanılan storage nesnesine geri döner.



### Identity Referansı

------

### $this->user->identity->check();

Kullanıcının yetkilendirilip yetkilendirilmediğini kontrol eder. Yetkili ise <b>true</b> değilse <b>false</b>değerine döner.

### $this->user->identity->guest();

Checks if the user is guest, if so, it returns to <b>true</b> otherwise <b>false</b>.

### $this->user->identity->exists();

Kimliğin önbellekte olup olmadığını kotrol eder. Varsa <b>true</b> yoksa <b>false</b>değerine döner.

### $this->user->identity->isVerified();

Onaya tabi olan yetkilendirmede başarılı oturum açma işleminden sonra kullanıcı onaylanıp onaylanmadığını gösterir. Kullanıcı onaylı ise <b>1</b> değerine değilse <b>0</b> değerine döner.

### $this->user->identity->isTemporary();

Onaya tabi olan yetkilendirmede kullanıcının kimliğinin geçici olup olmadığını gösterir. <b>1</b> yada </b>0</b> değerine döner.

### $this->user->identity->logout();

Oturumu kapatır ve __isAuthenticated anahtarı önbellekte <b>0</b> değeri ile güncellenir. Bu method önbellekteki kullanıcı kimliğini bütünü ile silmez sadece kullanıcıyı oturumu kappattı olarak kaydeder.

### $this->user->identity->destroy();

Önbellekteki kimliği bütünüyle yok eder.

### $this->user->identity->forgetMe();

Beni hatırla çerezinin bütünüyle tarayıcıdan siler.

### $this->user->identity->refreshRememberToken(GenericUser $genericUser);

Beni hatırla güvenlik çerezi (remember token) değerini yenileyerek database e kaydeder.


### Identity "Set" Metotları

------

### $this->user->identity->variable = 'value'

Kimlik dizisine yeni bir değer ekler.

### unset($this->user->identity->variable)

Kimlik dizisinde varolan değeri siler.

### $this->user->identity->setRoles(int|string|array $roles);

Eğer bir yetki sistemi kullanıyorsanız sisteme kayıtlı rolleri kimliğe bağlayabilirsiniz.

### $this->user->identity->setArray(array $attributes)

Tüm kullanıcı kimliği dizisinin üzerine girilen diziyi yazar.


### Identity "Get" Metotları

------

### $this->user->identity->getIdentifier();

Kullanıcın tekil tanımlayıcı sına geri döner. Tanımlayıcı genellikle kullanıcı adı yada id sidir.

### $this->user->identity->getPassword();

Kullanıcın hash edilmiş şifresine geri döner.

### $this->user->identity->getType();

Yetkilendirilmesi başarılı olmuş olan kullanıcının yetkilendirilme durumunu gösterir. Bu tipler : <b>UNVERIFIED, AUTHORIZED</b> dir.

### $this->user->identity->getRememberMe();

Eğer kullanıcı beni hatırla özelliğini kullanıyorsa <b>1</b> değerine aksi durumda <b>0</b> değerine döner.

### $this->user->identity->getTime();

Kimliğin ilk yaratılma zamanının verir. ( Php Unix microtime ).

### $this->user->identity->getArray()

Kullanıcının tüm kimlik değerlerine bir dizi içerisinde geri döner.

### $this->user->identity->getToken();

Güvenlik çerezinine geri döner.

### $this->user->identity->getRoles();

Kullanıcıya ait daha önceden kaydedilmiş rollere geri döner.


**Note:** Kendi metotlarınızı <kbd>app/classes/Auth/Identities/AuthorizedUser</kbd> sınıfı içerisine ekleyebilirsiniz.


### Activity Referansı

------

Activite verileri son aktivite zaman gibi anlık kullanıcı verilerini önbellekte tutubilmenizi sağlayan bir sınıftır.

### $this->user->activity->set($key, $val);

Aktivite dizininden bir değere geri döner. bir anahtar ve değerini ekler.

### $this->user->activity->get($key);

Aktivite dizininde anahtarla eşleşen değere geri döner.

### $this->user->activity->update();

Daha önce set metodu ile eklenen bütün verileri kaydeder. Bu metot en son çalıştırılmalıdır.

### $this->user->activity->remove();

Tüm aktivite verilerini önbellekten temizler.