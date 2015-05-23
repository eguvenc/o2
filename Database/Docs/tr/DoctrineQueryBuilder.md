

## Doctrine DBAL Sorgu Oluşturucu ( Query Builder )

------

Doctrine 2.1 sürüm ile gelen query builder sınıfı eklentisi SQL dili için sorgu oluşturmanızı kolaylaştırır. Sorgu oluşturucu bir SQL ifadesine sql bölümleri ekleyen metotlardan ibarettir. Sorgu oluşturucu ile oluşturulan bir SQL ifadesi bir çıktı olarak alınabilir yada <b>execute</b> metodu ile varolan bağlantı içerisinden sorgu olarak çalıştırılabilir.

<ul>

<li>
    <a href="#configuration">Konfigürasyon</a>
    <ul>
        <li><a href="#dependencies">Bağımlılıklar</a></li>
        <li><a href="#service-provider">Servis Sağlayıcısı</a></li>
        <li><a href="#loading-class">Sınıfı Yüklemek</a></li>
    </ul>
</li>

<li>
    <a href="#security">Güvenlik</a>
    <ul>
        <li><a href="#sql-injection">SQL Enjeksiyonunu Önlemek</a></li>
    </ul>
</li>

<li>
    <a href="#build-queries">Sorgular Oluşturmak</a>
    <ul>
        <li>
            <a href="#select">SELECT</a>
            <ul>
                <li><a href="#from">$this->db->from()</a></li>
                <li><a href="#get">$this->db->get()</a></li>
                <li><a href="#getSQL">$this->db->getSQL()</a></li>
            </ul>
        </li>
        <li>
            <a href="#where">WHERE</a>
            <ul>
                <li><a href="#andWhere">$this->db->andWhere()</a></li>
                <li><a href="#orWhere">$this->db->orWhere()</a></li>
                <li><a href="#where-expr">$this->db->expr()</a></li>
            </ul>
        </li>
        <li><a href="#group-by">GROUP BY & HAVING</a></li>
        <li><a href="#join">JOIN</a></li>
        <li><a href="#order-by">ORDER BY</a></li>
        <li><a href="#limit">LIMIT</a></li>
        <li><a href="#value">VALUES</a></li>
        <li><a href="#set">SET</a></li>
        <li><a href="#exressions">Sql İfadeleri Yaratmak</a></li>
        <li><a href="#query-binding">Sorgulara Parametre Yerleştirme ( Query Binding )</a></li>
    </ul>
</li>

</ul>

<a name="configuration"></a>
<a name="dependencies"></a>

### Konfigürasyon

Konfigürasyon için <b>DoctrineDBALServiceProvider</b> ve <b>DoctrineQueryBuilderServiceProvider</b> adlı servis sağlayıcıların yapılandırılması gerekir.

#### Bağımlılıklar

Sorgu oluşturucu <b>DoctrineDBALServiceProvider</b> servis sağlayıcısı olmadan çalışamaz. Eğer servis sağlayıcısı tanımlı değilse <kbd>app/providers.php</kbd> dosyasındaki database anahtarına aşağıdaki gibi tanımlamanız gerekir.

```php
$c['app']->register(
    [
        'logger' => 'Obullo\Service\Providers\LoggerServiceProvider',
        // 'database' => 'Obullo\Service\Providers\DatabaseServiceProvider',
       	'database' => 'Obullo\Service\Providers\DoctrineDBALServiceProvider',
    ]
);

/* End of file providers.php */
/* Location: .app/providers.php */
```

Böylelikle mevcut database servis sağlayıcısını doctrine dbal servis sağlayıcısı ile değiştirmiş olduk. Bu değişiklikten sonra varolan veritabanı fonksiyonlarınızda herhangi bir değişikliğe gitmenize gerek kalmaz.

<a name="service-provider"></a>

#### Servis Sağlayıcısı

Sorgu oluşturucu <b>DoctrineQueryBuilderServiceProvider</b> isimli servis sağlayıcısı üzerinden çalışır. Servis sağlayıcı database servis sağlayıcısına bağlanarak önceden tanımlı olan bağlantı adına ilişkin veriler ile sorgu oluşturucuya ait veritabanı bağlantısını kurar. 

Eğer servis sağlayıcısı tanımlı değilse <kbd>app/providers.php</kbd> dosyasına aşağıdaki gibi <b>qb</b> anahtarına tanımlamanız önerilir.

```php
$c['app']->register(
    [
        'logger' => 'Obullo\Service\Providers\LoggerServiceProvider',
        // 'database' => 'Obullo\Service\Providers\DatabaseServiceProvider',
       	'database' => 'Obullo\Service\Providers\DoctrineDBALServiceProvider',
        'qb' => 'Obullo\Service\Providers\DoctrineQueryBuilderServiceProvider',
    ]
);

/* End of file providers.php */
/* Location: .app/providers.php */
```

<a name="loading-class"></a>

#### Sınıfı Yüklemek

Servis sağlayıcısı yapılandırmasından sonra sorgu oluşturucuyu servis sağlayıcısı üzerinden bağlantı parametereleri göndererek oluşturabilirsiniz. Gönderilen bağlantı parametreleri <b>qb</b> servis sağlayıcısı üzerinden <b>database</b> servis sağlayıcısına gönderilirler.

Sınıfı servis sağlayıcısı ile bir kez oluşturduktan sonra istediğiniz değişkene atayabilirsiniz.

```php
$this->db = $this->c['app']->provider('qb')->get(['connection' => 'default']);
```

Eğer parametre gönderilmezse database servis sağlayıcısı varsayılan olarak default bağlantısına bağlanacaktır.

```php
$this->db = $this->c['app']->provider('qb')->get();

$row = $this->db
    ->select('id', 'name')
    ->get('users')
    ->row();
```

<a name="security"></a>
<a name="sql-injection"></a>

### Güvenlik

Veri tabanı operasyonlarında güvenli sorgular oluşturmak herhangi bir saldırı riskini önler. Veritabanı operasyonlarında bilinen en tehlikeli saldırı yöntemi <a href="http://tr.wikipedia.org/wiki/SQL_Injection" target="_blank">SQL Enjeksiyonu</a> dur.

#### SQL Enjeksiyonunu Önlemek

Sorgu oluşturucunun SQL ataklarını nasıl ve hangi şartlara göre önlediğini anlamak önemlidir. Son kullanıcıdan gelen tüm girdilerin SQL enjeksiyon riski vardır. Sorgu oluşturucu ile güvenli çalışmak için <b>ASLA</b> <kbd>$this->db->setParameter()</kbd> metodu dışındaki metotlara kullanıcı girdilerini göndermeyin. Ve <kbd>$this->db->setParameter($placeholder, $value)</kbd> metodunu kullandığınızda placeholder <b>?</b> veya <b>:name</b> söz dizimlerinden birini metod ile birlikte kullanın.

Aşağıdaki örnekte placeholder <b>?</b> parametre yerleştirme yöntemi ile güvenli bir sorgu oluşturuluyor.

```php
$email = $this->c['request']->get('email', 'clean')->email();

$row = $this->db
    ->select('id', 'name')
    ->from('users')
    ->where('email = ?')
    ->setParameter(0, $email)
    ->execute()
    ->row();
```

> **Not:** API tasarımındaki sayısal değerlere ilişkin olarak QueryBuilder uygulama arayüzü PDO arayüzünden farklı olarak <b>1</b> yerine <b>0</b> değeri ile başlar.

<a name="build-queries"></a>
<a name="select"></a>
<a name="from"></a>

### Sorgular Oluşturmak

Sorgu oluşturucu SELECT, INSERT, UPDATE ve DELETE sorgularını destekler. Select sorguları <kbd>select()</kbd> metodu ile INSERT, UPDATE ve DELETE sorguları ise tablo ismi girilerek <kbd>insert($table)</kbd>, <kbd>update($table)</kbd> ve <kbd>delete($table)</kbd> metotları ile oluşturulur.

#### SELECT

Select metodu kullanılırken tablo seçmek için from metodu kullanılır sorgu execute metodu ile çalıştırılır. Sorgu sonuçları için <kbd>Obullo\Database\Doctrine\DBAL\Result</kbd> sınıfı içerisindeki count(), row(), rowArray(), result() ve resultArray() metotları kullanılır.

##### $this->db->from()

```php
$row = $this->db
    ->select('id', 'name')
    ->from('users')
    ->execute()
    ->row();
```

Opsiyonel olarak <kbd>from()</kbd> metodu ikinci parametresine bir tablo takma adı gönderilebilir.

```php
$this->db
    ->select('u.id', 'u.name')
    ->from('users', 'u')
    ->where('u.email = ?');
```

<a name="get"></a>

##### $this->db->get()

Opsiyonel olarak bazı durumlara <kbd>from()</kbd> ve <kbd>execute()</kbd>metotları yerine Obullo adaptörü içerisinde bulunan <kbd>get()</kbd> kısayolunu da kullabilirsiniz.

```php
$row = $this->db
    ->select('id', 'name')
    ->get('users')
    ->row();
```
<a name="getSQL"></a>

##### $this->db->getSQL()

Eğer sorgu çalıştırılmak istenmiyorsa sorgu çıktısı <kbd>getSQL()</kbd> metodu ile alınabilir.

```php
echo $this->db
    ->select('id', 'name')
    ->from('users')
    ->getSQL();

// SELECT id, name FROM users
```

<a name="where"></a>

#### WHERE

SELECT, UPDATE ve DELETE türündeki sorgularda aşağıdaki gibi <kbd>where()</kbd> ifadesi kullanabilirsiniz.

```php
$this->db
    ->select('id', 'name')
    ->from('users')
    ->where('email = ?');
```

Her bir <kbd>where()</kbd> metodunu çağırıldığında bir önceki çağırılan ifade ile birleşir ve ifadeleri aşağıdaki diğer where metotları ile kombine edebilirsiniz.

<a name="andWhere"></a>

##### $this->db->andWhere()

Birbiri ardına kullanıldığında where ifadesinden sonra AND ifadelerini oluşturur.

```php
$this->db
    ->select('id', 'name')
    ->from('users')
    ->andWhere('email = ?');
    ->andWhere('username = ?');
```

<a name="orWhere"></a>

##### $this->db->orWhere()

Birbiri ardına kullanıldığında where ifadesinden sonra OR ifadelerini oluşturur.

```php
$this->db
    ->select('id', 'name')
    ->from('users')
    ->orWhere('email = ?');
    ->orWhere('username = ?');
```

<a name="where-expr"></a>

##### $this->db->expr()

Alternatif olarak where ifadeleri içerisinde ifadeler yaratmak için <kbd>$this->db->expr()</kbd> metodunu kullabilirsiniz.

```php
$or = $this->db->expr()->orx();
$or->add($this->db->expr()->eq('u.id', 1));
$or->add($this->db->expr()->eq('u.id', 2));

echo $this->db->update('users', 'u')
    ->set('u.password', md5('password'))
    ->where($or);

// UPDATE users u SET u.password = 5f4dcc3b5aa765d61d8327deb882cf99 WHERE (u.id = 1) OR (u.id = 2)
```

### Sql İfadeleri Yaratmak


##### $this->db->expr()

##### $this->db->andx()

##### $this->db->orx()

##### $this->db->orx()