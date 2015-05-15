
## Veritabanı Sınıfı ( Database )

------

Veritabanı sınıfı veritabanı bağlantılarını sağlar ve temel veritabanı işlevlerini ( okuma, yazma, silme, kaydetme ) yürütür. Veritabanı operasyonları için <b><a href="http://php.net/manual/tr/book.pdo.php" target="_blank">PDO</a></b> ( Php Data Objects ) arayüzünü kullarak sadece ilişkili veritabanı türlerini ( <a href="http://tr.wikipedia.org/wiki/%C4%B0li%C5%9Fkisel_veri_taban%C4%B1_y%C3%B6netim_sistemi">RDBMS</a> ) türündeki veritabanlarını destekler.

<ul>
<li>
    <a href="#server-requirements">Sunucu Gereksinimleri</a>
    <ul>
        <li><a href="#unix-requirements">Unix Sunucularda Pdo Kurulumu</a></li>
        <li><a href="#windows-requirements">Windows Sunucularda Pdo Kurulumu</a></li>
        <li><a href="#supported-databases">Desteklenen Veritabanları</a></li>
    </ul>
</li>

<li>
    <a href="#database-connection">Veritabanı Bağlantısı</a>
    <ul>
        <li><a href="#standart-connection">Standart Bağlantı</a></li>
        <li><a href="#unix-connection">Unix Soket Bağlantısı</a></li>
        <li><a href="#config-configuration">Konfigürasyon</a></li>
        <li><a href="#connection-management">Bağlantı Yönetimi</a></li>
        <li><a href="#service-configuration">Servis Konfigürasyonu</a></li>
        <li><a href="#loading-class">Sınıfı Yüklemek</a></li>
    </ul>
</li>

<li>
    <a href="#service-provider">Servis Sağlayıcısı</a>
    <ul>
        <li><a href="#getting-existing-connection">Varolan Bağlantıyı Almak</a></li>
        <li><a href="#creating-new-connection">Yeni Bir Bağlantı Oluşturmak</a></li>
    </ul>
</li>

<li>
    <a href="#reading-database">Veritabanından Okumak</a>
    <ul>
        <li><a href="#query">$this->db->query()</a></li>
    </ul>
</li>

<li>
    <a href="#generating-results">Veritabanından Sonuçlar Getirmek</a>
    <ul>
        <li><a href="#count">$this->db->count()</a></li>
        <li><a href="#row">$this->db->row()</a></li>
        <li><a href="#rowArray">$this->db->rowArray()</a></li>
        <li><a href="#result">$this->db->result()</a></li>
        <li><a href="#resultArray">$this->db->resultArray()</a></li>
    </ul>
</li>
<li>
    <a href="#writing-database">Veritabanına Yazmak</a>
    <ul>
        <li><a href="#exec">$this->db->exec()</a></li>
    </ul>
</li>     
<li>
    <a href="#query-binding">Güvenli Sorgular Oluşturmak ( Query Binding )</a>
    <ul>
        <li><a href="#prepare">$this->db->prepare()</a></li>
    </ul>
</li>
<li>
    <a href="#escaping-sql-injections">Sql Enjeksiyonundan Kaçış</a>
    <ul>
        <li><a href="#escape">$this->db->escape()</a></li>
    </ul>
</li>  
<li>
    <a href="#transactions">Veri Kaybı Olmadan Veri Kaydetmek ( Transactions )</a>
    <ul>
        <li><a href="#native-transaction">Doğal Transaksiyon</a></li>
        <li><a href="#auto-transaction">Otomatik Transaksiyon</a></li>
    </ul>
</li>
<li>
    <a href="#helper-functions">Yardımcı Fonksiyonlar</a>
    <ul>
        <li><a href="#connection">$this->db->connection()</a></li>
        <li><a href="#reconnect">$this->db->reconnect()</a></li>
        <li><a href="#isConnected">$this->db->isConnected()</a></li>
        <li><a href="#stmt">$this->db->stmt()</a></li>
        <li><a href="#stmt">$this->db->inTransaction()</a></li>
        <li><a href="#queryId">$this->db->queryId()</a></li>
        <li><a href="#insertId">$this->db->insertId()</a></li>
        <li><a href="#lastQuery">$this->db->lastQuery()</a></li>
    </ul>
</li>
</ul>

<a name='server-requirements'></a>
<a name='unix-requirements'></a>

### Sunucu Gereksinimleri

------

#### Unix Sunucularda Pdo Kurulumu

1. PDO sürücüsü PHP 5.1.0'dan itibaren öntanımlı olarak etkindir.
2. PDO eklentisini bir paylaşımlı eklenti olarak kuruyorsanız, PHP çalıştığı zaman PDO eklentisinin özdevinimli olarak yüklenmesi için php.ini dosyasını buna göre düzenlemeniz gerekir. Ayrıca kullanacağınız veritabanına özgü sürücülerinde dosyada etkin kılınması gerekir. Bunu yaparken bunların pdo.so satırından sonra listelenmesine dikkat etmelisiniz. Çünkü, PDO eklentisinin veritabanlarına özgü eklentiler yüklenmeden önce ilklendirilmesi gerekir. PDO'yu ve veritabanlarına özgü eklentileri duruk olarak derliyorsanız php.ini adımını atlayabilirsiniz.

Paylaşımlı kurulumda php.ini dosyanızda pdo.so aşağıdaki gibi açık olmalı.

```php
extension=pdo.so
```

Daha fazla bilgi için bu sayfayı ziyaret edin. <a href="http://php.net/manual/tr/pdo.installation.php">http://php.net/manual/tr/pdo.installation.php</a>

<a name='windows-requirements'></a>

#### Windows Sunucularda Pdo Kurulumu

1. PDO ve belli başlı sürücülerin tamamı, birer paylaşımlı eklenti olarak PHP ile birlikte gelir ve php.ini dosyasında etkin kılınmaları gerekir:

```php
extension=php_pdo.dll
```

2. Bu satırın ardına veritabanlarına özgü eklentilerin DLL dosyalarını aşağıdaki gibi ekleyebilir veya dl() ile çalışma anında da yükleyebilirsiniz.


```php
extension=php_pdo_mysql.dll
```

> **Not:** Bu DLL'lerin hepsinin <a href="http://php.net/manual/tr/ini.core.php#ini.extension-dir" target="_blank">extension_dir</a> yönergesinde belirtilen dizinde bulunması gerektiğini unutmayın.

<a name='supported-databases'></a>

#### Desteklenen Veritabanları

<table class="span9">
<thead>
<tr>
<th>PDO Bağlantı Adı</th>
<th>Veritabanı Adı</th>
</tr>
</thead>
<tbody>
<tr>
<td>mysql</td>
<td>MySQL 3.x/4.x/5.x</td>
</tr>
<tr>
<td>pgsql</td>
<td>PostgreSQL</td>
</tr>
</tbody>
</table>

<a name='database-connection'></a>

### Veritabanı Bağlantısı

------

Veritabanı ile bağlantı kurulması veritabanı işlevleri ( query, execute, exec, transaction .. ) kullanıldığı zaman gerçekleşir. Bu metotların kullanılmadığı yerlerde bağlantı açık değildir ve bir kere açılan bir bağlantı varsa bu bağlantı tekrar açılmaz. ( Lazy Loading ). Veritabanı sınıfı <b>db</b> servis sınıfı tarafından yönetilir ve <b>db</b> servisi de bağlantı yönetimi için <b>database</b> servis sağlayıcısını kullanır. Veritabanı sınıfını kullanmaya başlayabilmeniz için lütfen konfigürasyon bölümüne de bir gözatın.

<a name='standart-connection'></a>

#### Standart Bağlantı

Veritabanına bağlantı konfigürasyonu yerel ortam için <kbd>app/config/env/local/database.php</kbd> dosyasından gerçekleştirilir. Aşağıdaki örnek bağlantı şeması <b>dsn</b> anahtarına girilir.

```php
mysql:host=localhost;port=;dbname=test;
```

<a name='unix-connection'></a>

#### Unix Socket Bağlantısı

Unix soket tipinde bağlantı isteniyorsa bağlantı şeması aşağıdaki gibi olmalıdır.

```php
mysql:unix_socket=/PATH/TO/SOCK_FILE;dbname=YOUR_DB_NAME;charset=utf8;
```
Örnek bir bağlantı

```php
mysql:unix_socket=/var/run/mysqld/mysqld.sock;dbname=test
```

<a name='config-configuration'></a>

#### Konfigürasyon

Veritabanına bağlantı konfigürasyonu yerel ortam için <kbd>app/config/env/local/database.php</kbd> dosyasından yapılır. Sürücü bağlantısı ve diğer bağlantı ayarları <b>connections</b> anahtarından okunur.

```php
return array(
    
    'connections' => 
    [
        'default' => [
            'dsn'      => 'mysql:host=localhost;port=;dbname=test',
            'username' => $c['env']['MYSQL_USERNAME.root'],
            'password' => $c['env']['MYSQL_PASSWORD.null'],
            'options'  => [
                PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES 'UTF8'",
                PDO::MYSQL_ATTR_USE_BUFFERED_QUERY => true
            ]
        ],
        'failed' => [ .. ],  // Diğer veritabanı bağlantı ayarları
    ]
);

/* End of file database.php */
/* Location: .app/config/env/local/database.php */
```

Konfigürasyon dosyasında varsayılan bağlantı ismi <b>default</b> anahtarıdır ve bağlantı adını değiştirmemeniz önerilir.

<a name='connection-management'></a>

#### Bağlantı Yönetimi

Her yeni açılacak bağlantı için <kbd>app/config/env/local/database.php</kbd> dosyasında <b>connections</b> anahtarı altında bir isim verilerek bu isme bağlı dizi içerisine konfigürasyon değerleri girilmelidir. 

Veritabanı servis sağlayıcısı <b>connections</b> anahtarı altına girilen konfigürasyonlar çağrıldığında çağırılan bağlantı eğer bağlantı havuzu içerisinde mevcut ise ( daha önceden bu bağlantı için bir açık bağlantı varsa ) bağlantı nesnesi tekrar yaratılmadan havuzdan alınır, eğer mevcut değilse konfigürasyon dosyanızda yarattığınız bağlantı ismi ile havuza bir bağlantı ekler.

Böylece <b>veritabanı</b> servis sağlayıcısı sayesinde uygulamada kullanılan çoklu veritabanları database.php konfigürasyon dosyasından takip edilerek her yazılımcının mevcut bir bağlantı varken yeni bir bağlantı açması önlenmiş olur.

> **Not:** Veritabanı bağlantısı teknik olarak <kbd>Obullo/Service/Providers/Database.php</kbd> servis sağlayıcısı üzerinden <kbd>Obullo/Database/Pdo/Handler/$sürücü.php</kbd> dosyasındaki createConnection() metodu aracılığı ile sağlanır.

<a name='service-configuration'></a>

#### Servis Konfigürasyonu

Uygulamada veritabanı nesnesi <kbd>app/classes/Service/Db.php</kbd> servis dosyası tarafından kontrol edilir. Db servis dosyası ise bağlantı kurabilmek için <b>database</b> servis sağlayıcısını kullanır. Servis konfigürasyonu için <kbd>app/classes/Service/Db.php</kbd> dosyasını açın ve varsayılan bağlantı konfigürasyonunuzu <b>get()</b> metodu içerisine girin.

```php
namespace Service;

use Obullo\Container\Container;
use Obullo\Service\ServiceInterface;

class Db implements ServiceInterface
{
    public function register(Container $c)
    {
        $c['db'] = function () use ($c) {
            return $c['app']->provider('database')->get(['connection' => 'default']);
        };
    }
}

// END Db service

/* End of file Db.php */
/* Location: .app/classes/Service/Db.php */
```
<a name='loading-class'></a>

#### Sınıfı Yüklemek

##### Controller sınıfı içerisinden yüklemek

Sınıfı kontrolör sınıfı içerisinden yüklemek için konteyner içerisinden <b>db</b> olarak çağırmanız gerekir.

```php
$this->c['db'];
```
Bir kez çağırıldıktan sonra nesne kontrolör sınıfına kaydedilir ve artık nesneye <kbd>$this->class->method()</kbd> yöntemiyle erişilebilir.

```php
$this->db->query("SELECT * FROM users LIMIT 10")->resultArray();
```

Bir örnek

```php
namespace Welcome;

class Welcome extends \Controller
{
    public function load()
    {
        $this->c['db'];
    }

    public function index()
    {
        $results = $this->db->prepare("SELECT * FROM users WHERE id = ?")
        ->bindValue(1, 1, \PDO::PARAM_INT)
        ->execute()->row();

        print_r($results);
    }
}

/* End of file welcome.php */
/* Location: .modules/welcome/welcome.php */
```

##### Diğer sınıflar içinden yüklemek

Herhangi bir sınıf içerisinde veritabanı nesnesini kullanıyor ve kontrolör sınıfına <kbd>$this->class->method()</kbd> olarak kaydedilmesini istemiyorsanız konteyner <b>get()</b> metodunu kullanabilirsiniz.

```php
$this->db = $this->c->get('db');
$this->db->query('...');
```

<a name='service-provider'></a>

### Servis Sağlayıcısı

------

Veritabanı servis sağlayıcısı <kbd>Obullo/Service/Providers/Database.php</kbd> dosyasıdır. Servis sağlayıcısı konfigürasyon dosyasını kullanarak bağlantıları yönetir eğer var olan bir veritabanı bağlantısı kullanmak yada yeni bir veritabanı bağlantısı açılmak isteniyorsa <b>database</b> servis sağlayıcısı kullanılır.

<a name='getting-existing-connection'></a>

#### Varolan Bağlantıyı Almak

Eğer bir yazılımcı paylaşımlı <b>db</b> servisinin kullandığı veritabanı nesnesi dışında <b>tanımlı</b> olan bir veritabanı bağlantısına ihtiyaç duyuyorsa bunun için servis sağlayıcısı <b>get</b> metodunu kullanır.

Servis sağlayıcıları uygulamanın her yerinde kullanılabilen işe yarar parçacıklardır. Veritabanı servis sağlayıcısı uygulamanın farklı bölümlerinde gereksiz yeni bağlantılar açmamak için yazılımcıdan gelen talebe göre konfigürasyon dosyasında varolan bir bağlantıyı alır yada konfigürasyon dosyasında olmayan yeni bir bağlanyı yaratır. Yaratılan bağlantılar bağlantı havuzunda toplanırlar ve tekrar aynı değerler ile istenen bir bağlantı olduğunda bu defa havuzdan getirilirler.

Aşağıdaki örnekte konfigürasyon dosyasında varolan <b>default</b> bağlantı nesnesi alınıyor.

```php
$this->db = $this->c['app']->provider('database')->get(['connection' => 'default']);
```

Eğer <b>second</b> isimli tanımlanmış farklı bir bağlantı olsaydı aşağıdaki gibi alınırdı.

```php
$this->db = $this->c['app']->provider('database')->get(['connection' => 'second']);
```

Veritabanı nesnesi alındıktan sonra artık veritabanı metotlarına erişilebilir.

```php
$this->db->query(" .. ");
```

<a name='creating-new-connection'></a>

#### Yeni Bir Bağlantı Oluşturmak

Eğer bir yazılımcı paylaşımlı <b>db</b> servisinin kullandığı veritabanı nesnesi dışında <b>tanımsız</b> olan yeni bir veritabanı bağlantısına ihtiyaç duyuyorsa bunun için servis sağlayıcısı <b>factory</b> metodunu kullanır.

Aşağıdaki örnekte konfigürasyon dosyasında varolmayan <b>yeni</b> bir bağlantı nesnesi oluşturuluyor.

```php
$this->db = $this->c['app']->provider('database')->factory(
    [
        'dsn'      => 'mysql:host=localhost;port=;dbname=test',
        'username' => 'root',
        'password' => '123456',
        'options' => [
            \PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES 'UTF8'",
            \PDO::MYSQL_ATTR_USE_BUFFERED_QUERY => true
        ]
    ]
);
```

<a name='reading-database'></a>
<a name='query'></a>

### Veritabanından Okumak

------

Veritabanından okuma işlemleri için query metodu kullanılır.

##### $this->db->query()

Bir sql sorgusunu çalıştırır ve veritabanı nesnesine geri döner.

```php
$users = $this->db->query("SELECT * FROM users")->resultArray();
```

<a name='generating-results'></a>
<a name='count'></a>
<a name='row'></a>
<a name='rowArray'></a>
<a name='result'></a>
<a name='resultArray'></a>

### Veritabanından Sonuçlar Getirmek

------

Veritabanına yapılan sorgudan sonra aşağıdaki metotlardan biri seçilerek sonuçlar elde edilir.

##### $this->db->count()

Son sql sorgusundan etkilenen satır sayısını döndürür.

```php
echo $this->db->query("SELECT * FROM users")->count();  // 5
```

##### $this->db->row($default = false)

Son sql sorgusundan dönen tekil sonucu <b>nesne</b> türünden verir.

```php
$row = $this->db->query("SELECT * FROM users WHERE id = 2")->row();
```

```php
var_dump($row);
```

```php
stdClass Object
(
    [id] => 2
    [username] => user@example.com
)
```

Eğer ilk parametre gönderilirse sonuçların başarısız olması durumunda fonksiyonun hangi türe döneceği belirlenir. Varsayılan <b>false</b> değeridir. Eğer başarısız işlemde sonucun <b>array()</b> değerine dönmesini isteseydik fonksiyonu aşağıdaki gibi kullanmalıydık.

```php
$row = $this->db->query("SELECT * FROM users WHERE id = 748")->row(array());
```

```php
var_dump($row);  // Çıktı array(0) { } 
```

##### $this->db->rowArray($default = false)

Son sql sorgusundan dönen tekil sonucu <b>dizi</b> türünden verir.

```php
$row = $this->db->query("SELECT * FROM users WHERE id = 2")->rowArray();
```

```php
var_dump($row);
```

```php
Array
(
    [id] => 2
    [username] => user@example.com
)
```

Eğer ilk parametre gönderilirse sonuçların başarısız olması durumunda fonksiyonun hangi türe döneceği belirlenir. Varsayılan <b>false</b> değeridir. Eğer başarısız işlemde sonucun <b>array()</b> değerine dönmesini isteseydik fonksiyonu aşağıdaki gibi kullanmalıydık.

```php
$row = $this->db->query("SELECT * FROM users WHERE id = 748")->rowArray(array());
```

```php
var_dump($row);  // Çıktı array(0) { } 
```

##### $this->db->result($default = false)

Son sql sorgusundan dönen tüm sonuçları bir dizi içinde <b>nesne</b> türünden verir.

```php
$results = $this->db->query("SELECT id, username FROM users")->result();
```

```php
var_dump($results);
```

```php
Array
(
    [0] => stdClass Object
        (
            [id] => 1
            [username] => user@example.com
        )

    [1] => stdClass Object
        (
            [id] => 2
            [username] => user2@example.com
        )

)
```

Eğer ilk parametre gönderilirse sonuçların başarısız olması durumunda fonksiyonun hangi türe döneceği belirlenir. Varsayılan <b>false</b> değeridir. Eğer başarısız işlemde sonucun <b>array()</b> değerine dönmesini isteseydik fonksiyonu aşağıdaki gibi kullanmalıydık.

```php
$row = $this->db->query("SELECT * FROM users WHERE id = 748")->result(array());
```

```php
var_dump($row);  // Çıktı array(0) { } 
```

##### $this->db->resultArray($default = false)

Son sql sorgusundan dönen tüm sonuçları <b>dizi</b> türünden verir.

```php
$results = $this->db->query("SELECT id, username FROM users")->resultArray();
```

```php
var_dump($results);
```

```php
Array
(
    [0] => Array
        (
            [id] => 1
            [username] => user@gmail.com
        )

    [1] => Array
        (
            [id] => 2
            [username] => user2@example.com
        )

)
```

Eğer ilk parametre gönderilirse sonuçların başarısız olması durumunda fonksiyonun hangi türe döneceği belirlenir. Varsayılan <b>false</b> değeridir. Eğer başarısız işlemde sonucun <b>array()</b> değerine dönmesini isteseydik fonksiyonu aşağıdaki gibi kullanmalıydık.

```php
$row = $this->db->query("SELECT * FROM users WHERE id = 748")->resultArray(array());
```

```php
var_dump($row);  // Çıktı array(0) { } 
```


<a name='writing-database'></a>
<a name='exec'></a>


### Veritabanına Yazmak

------

Veritabanına yazma işlemleri exec metodu ile yapılır. Exec metodu çalıştırıldıktan sonra veri işlemeyi gerçekleştirir ve etkilenen satırlara döner exec metodu kullanıldığında etkilenen satırları alabilmek ayrıca sütun sayma işlemi yapmaya gerek kalmaz.

##### $this->db->exec()

Bir sql sorgusunu çalıştırır ve etkilenen satır sayısına geri döner.

###### Insert Operasyonu

```php
$count = $this->db->exec("INSERT INTO users (username) VALUES ('user3@example.com')");
```

###### Update Operasyonu

```php
$count = $this->db->exec("UPDATE users SET username = 'user4@example.com' WHERE id = 2");
```
> **Not:** Update operasyonunda eğer veritabanındaki değer gönderilen değer ile <b>aynı</b> ise update işlemi yapılmaz ve etkilenen satır sayısı <b>0</b> olarak elde edilir.

###### Delete Operasyonu

```php
$count = $this->db->exec("DELETE FROM users WHERE id = 2");
```

###### Etkilenen Satır Sayısı

Yukarıdaki operasyonların herhangi birinde çıktı ekrana yazdırıldığında etkilenen satır sayısı elde edilir.

```php
var_dump($count);
```

```php
int(1)
```

<a name='query-binding'></a>
<a name='prepare'></a>

### Güvenli Sorgular Oluşturmak ( Query Binding )

------

Pdo nesnesi ile güvenli sorgular oluşturmak için <a href="http://php.net/manual/tr/pdo.prepare.php" target="_blank">prepare</a> ve <a href="http://php.net/manual/tr/pdostatement.execute.php" target="_blank">execute</a> metotlarını beraber kullanmak gerekir. Query binding yöntemi sql deyimi oluşturulurken sql enjeksiyon tehdidine karşı tehlikeli değerlere kaçış sembolü atar.

Ayrıca eğer uygulamanın bir bölümünde çok fazla aynı sql sorgusu kullanılıyorsa prepare yöntemi sql sorgularını önbelleğe alır ve birbirine eş değer çok fazla sorgu olması durumunda performans sağlar. 

> **Not:** Query binding yöntemini kullandığınızda sql enjeksiyon tehdidine karşı girilen değerlerden $this->db->escape() metodu ile kaçış yapmanıza gerek kalmaz.

##### Eğer uygulamanın bir bölümünde prepare metodu kullanıyorsanız aşağıdaki iki neden veya bu iki nedenden bir tanesine ihtiyaç duyuyor olmanız gerekir.

1. Uygulama için kritik bir sql sorgusu ve girilen değerlerde tür kontrolü gerekli ise.
2. Uygulamanın bir bölümünde aynı sql sorgusu farklı değerlerler ile çok kez tekrarlanıyorsa.

Bu iki koşuldan birinin oluşmadığı durumlarda query() metodunu kullanarak daha hızlı sql sorguları elde edebilirsiniz.


##### $this->db->prepare()

Çalıştırılmak üzere bir sql deyimi hazırlar ve bir deyim nesnesi olarak döndürür.

```php
$this->db->prepare("SELECT * FROM users")->execute()->resultArray();
```

##### $this->db->bindValue($num, $val, $type)

Bir değeri bir değiştirge ile ilişkilendirir.

```php
$result = $this->db->prepare("SELECT id, username FROM users WHERE id = ? AND active = ?")
->bindValue(1, 2, \PDO::PARAM_INT)
->bindValue(2, 'Active', \PDO::PARAM_STR)
->execute()
->resultArray();
```

Bind value işleminde parametre değerleri tür olarak belirlenir birinci parametre parametrenin numarası, ikinci parametre değeri ve üçüncü parametre ise türüdür. Bu tip sorgularda her değer için kendiliğinden escape yapılır bu nedenle <kbd>$this->db->escape()</kbd> metodunu kullanmaya gerek kalmaz.

##### $this->db->bindParam($num, $val, $type, $lenght)

Bir değiştirgeyi belirtilen değişkenle ilişkilendirir. Pdo bindValue() yönteminin tersine değişken gönderimli olarak ilişkilendirilir ve sadece execute() çağrısı sırasında değerlendirmeye alınır.

```php
$calories = 150;
$color = 'red';

$result = $this->db->prepare("SELECT name, colour, calories FROM fruit 
WHERE calories < ? AND color = ?")
->bindParam(1, $calories, \PDO::PARAM_INT)
->bindParam(2, $color, \PDO::PARAM_STR, 12)
->execute();
```

Değiştirgeler çoğunlukla girdi değiştirgesidir, yani değiştirgeler sadece sorguda salt okunur olarak ele alınır. Eğer değiştirge çıktı almak amacıyla kullanılacaksa son parametre veri türü uzunluğu mutlaka belirtilmelidir.

##### $this->db->execute(array $values)

Bir hazır deyimi girdiler ile çalıştırır.

```php
$result = $this->db->prepare('SELECT name, colour, calories FROM fruit 
WHERE calories < :calories AND colour = :colour')->execute(
    [':calories' => 150, ':colour' => 'red']
);
```

<a name='escaping-sql-injections'></a>
<a name='escape'></a>


### Sql Enjeksiyonundan Kaçış

------

Eğer <b>query binding</b> özelliğini kullanmıyorsanız sorgu değerlerini <a href="http://tr.wikipedia.org/wiki/SQL_Injection">sql enjeksiyon</a> güvenlik tehdidine karşı bir kaçış fonksiyonu kullanmanız gerekir. Escape fonksiyonu belirli karakterlerden kaçarak sql cümleciği değerlerini güvenli bir şekilde oluşturmanızı sağlar.


##### $this->db->escape()

Sql enjeksiyon tehditlerine karşı bağlantıdaki aktif karaktere türüne ( charset ) göre girilen karakterlerden kaçar.

```
$title = $this->db->escape("Welcome to John's Blog");   // Welcome to John\'s Blog
$post  = $this->db->escape("This is a dangerous content ' \ ");  // This is a dangerous content \' \\
```

```php
$this->db->exec("INSERT INTO blog (title, post) VALUES ($title, $post)");
```

<a name='transactions'></a>
<a name='native-transaction'></a>


### Veri Kaybı Olmadan Veri Kaydetmek ( Transactions )

------

Veritabanı katmanı güvenli transaksiyonu destekleyen tablo türleri ile veri kaybı olmadan veri kaydetmeyi sağlar. MySQL sürücüsü için transaksiyonların çalışabilmesi için MyISAM tablo türü yerine <b>InnoDB</b> veya <b>BDB</b> tablo türlerinin kullanılması gerekir. Diğer bilinen veritabanı türleri için transaksiyonlar kendiliğinden desteklenir.

Eğer transaksiyonlar konusuna aşina değilseniz veritabanınıza özgü konu hakkında internet üzerinde detaylı online bilgiler bulabilirsiniz. Burada bu konuya temel düzeyde değinilmiştir.


#### Doğal Transaksiyon

Uygulamada transaksiyonları doğal olarak <b>try ... catch</b> komutları ile çalıştırabilmek mümkündür. Bunun için try komutu içerisinde <kbd>$this->db->beginTransaction()</kbd> ile operasyonu başlatıp ve işlemlerin en sonunda başarılı işlemi gönderme anlamına gelen <kbd>$this->db->commit()</kbd> metodu ile işlemi bitirmeniz gerekir.

Eğer işlemde herhangi bir hata ile karşılaşılırsa <b>catch</b> komutu bloğunda <kbd>$this->db->rollBack()</kbd> komutu ile işlem kaydedilmeden bütün operasyonları geri alabilirsiniz. Geri dönen hata mesajı ise <kbd>$e->getMessage()</kbd> komutu ile elde edilebilir.


```php
try {

    $this->db->beginTransaction(); // Operasyonları başlat
    $this->db->exec("INSERT INTO persons (person_skill, person_name) VALUES ('javascript', 'john')");
    $this->db->commit();      // Operasyonu bitti olarak kaydet

    echo 'Veri başarı ile kaydedildi.';

} catch(Exception $e)
{    
    $this->db->rollBack();    // İşlem başarısız olursa kaydedilen tüm verileri geri al.
    echo $e->getMessage();    // Hata mesajını ekrana yazdır.
}
```

Transaction/Commit metotları arasında birden fazla sorgu çalıştırabilirsiniz ve işlem başarılı ise tüm operasyonlar sisteme <b>commit</b> edilir, başarısız ise <b>rollBack</b> komutu ile tüm işlemler başa döndürürülerek <b>$e</b> Exception nesnesi ile başarısız işlem metotlarına ulaşılır.

<a name='auto-transaction'></a>


#### Otomatik Transaksiyon

Otomatik transaksiyon bir closure fonksiyonu içerisine konulan veritabanı sorgu operasyonları için transaksiyonları başlatıp commit ve rollBack işlemlerini kendiliğinden yapar. 

```php
$result = $this->db->transactional(
    function () {
        return $this->db->exec("INSERT INTO persons (person_skill, person_name) VALUES ('php', 'Bob')");
    }
);

if ( ! $result) {          
    echo $e->getMessage();  // Hata mesajı
} else {
    echo 'Veri başarı ile kaydedildi. Etkilenen satır sayısı '.$result;
}
```

> **Not:** Eğer transactional() fonksiyonu içerisindeki fonksiyon sonucu <b>0</b> yada <b>false</b> ise sonuç her zaman <b>true</b> değerine dönecektir. Sadece gerçek bir istisnai hata olması durumunda sonuç <b>false</b> değerine döner. Eğer fonksiyon sonucu 0 dan büyük bir değere dönüyorsa o zaman sonucun kendisine dönülür.


<a name='helper-functions'></a>
<a name='connection'></a>
<a name='reconnect'></a>
<a name='isConnected'></a>
<a name='stmt'></a>
<a name='inTransaction'></a>
<a name='queryId'></a>
<a name='insertId'></a>
<a name='lastQuery'></a>

### Yardımcı Fonksiyonlar


##### $this->db->connection()

Varolan pdo bağlantı nesnesine geri döner.

##### $this->db->reconnect()

Eğer bazı durumlarda bağlantının varlığından emin olunamıyorsa reconnect ile yeniden bağlanma denemesi yapılarak bağlantı nın hep canlı kalması sağlanır.

##### $this->db->isConnected()

Bağlantının var olup olmadığını anlamaya yardımcı olur. Eğer aktif bir bağlantı varsa <b>true</b> değerine aksi durumda <b>false</b> değerine geri döner.

##### $this->db->stmt()

Varolan PDOStatement nesnesine geri döner. Veritabanı sınıfında olmayan bir PDOStatement metodu varsa <kbd>$this->db->query()->stmt()->method()</kbd> yöntemi ile doğal PDOStatement sınıfı metotlarına ulaşılır.

##### $this->db->inTransaction()

Eğer aktif bir transaksiyon işlemi varsa metot <b>true</b> değerine aksi durumda <b>false</b> değerine geri döner.

##### $this->db->queryId()

Uygulamada her sorguya bir identity değeri veririlir queryId metodu ise varolan sorgunun id değerine geri döner.

##### $this->db->insertId()

Veritabanına en son eklenen tablo id sinin değerine geri döner.

##### $this->db->lastQuery()

En son çalıştırılan sorgunun çıktısını elde etmeyi sağlar.
