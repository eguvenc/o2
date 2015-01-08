
## Cache Class

------

Cache paketi çeşitli önbellekleme ( cache ) türleri için birleşik bir API sağlar. Cache paket konfigürasyonu ortam tabanlı konfigürasyon dosyası app/config/$env/cache.php dosyasından yönetilir.

Desteklenen önbellek sürücüleri  <b>Apc, File, Memcache ve Redis</b> dir. Her bir önbellek türünün konfigürasyonu tek bir dosyada fakat diğer türlerden ayrı olarak konfigüre edilir. Eğer büyük yada orta çaplı bir uygulama geliştiriyorsanız öncelikli olarak O2 içerisinde diğer bazı kütüphanelerde de sık kullanılan <b>Redis</b> sürücüsünü alternatif olarak <b>Memcached</b> sürücüsünü kullanmanızı tavsiye ediyoruz.

### Servis Konfigürasyonu

Cache paketini kullanabilmeniz için ilk önce servis ve servis sağlayıcısı ayarlarını kurmamız gerekir. Cache servisi nesnesi uygulama içerisinde bazı yerlerde paylaşımlı olarak bazı yerlerde de konfigürasyon değişikliği yapılarak ( paylaşımsız ) kullanıldığı için sık sık kullanılır fakat kimi zaman farklı ihtiyaçlara cevap veremez.

Bir örnek vermek gerekirse uygulamada servis olarak kurduğunuz cache kütüphanesi her zaman <b>PHP_SERIALIZER</b> parametresi ile kullanılmaya konfigure edilmiştir ve değiştirilemez. Fakat bazı yerlerde <b>SERIALIZER_NONE</b> parametresini kullanmanız gerekir bu durumda servis sağlayıcı imdadımıza yetişir ve <b>SERIALIZER_NONE</b> parametresini kullanmanıza imkan sağlar. Böylece cache kütüphanesi yeni bir nesne oluşturarak servis sağlayıcısının diğer cache servisi ile karışmasını önler.

Hem bu nedenle hem de uygulama içerisinde kolaylık ve esneklik gibi avantajlardan yararlanmak için cache kütüphanesini aşağıdaki gibi hem servis hem de servis sağlayıcı ( service provider ) olarak kurmamız gerekir.

### Servis Kurulumu

Servis kurulumu çok kolaydır tek yapmanız gereken kullanmak istediğiniz sürücüyü <b>use</b> komutu ile namespace ten önce çağırmak ve <b>closure</b> fonksiyonu içerisine <b>return new Sürücü($c);</b> şeklinde sınıfınızı ilan etmektir.

```php
<?php

namespace Service;

use Obullo\Cache\Handler\Redis;

Class Cache implements ServiceInterface
{
    public function register($c)
    {
        $c['cache'] = function () use ($c) {
            return new Redis($c);
        };
    }
}

// END Cache class

/* End of file Cache.php */
/* Location: .classes/Service/Cache.php */
```

### Servisi Yüklemek

Servis bir kez load komutu ile yüklendiği zaman artık kütüphane metotlarına kendi adıyla ulaşabilirsiniz.

```php
<?php
$this->c->load('service/cache');
$this->cache->metod();
```

Durum controller içerisinde böyle iken size ait herhangi bir sınıf içerisinden servisi yüklemek aşağıdaki gibidir.

```php
<?php
$this->cache = $this->c->load('service/cache');
$this->cache->method();
```

### Servis Sağlayıcısı Kurulumu

Servis sağlayıcısı kurulumunun servisler den tek farkı göndermek isteğiniz ekstra paremetreleri burada konfigüre edebilme imkanınız olmasıdır. Aşağıda görüldüğü servis sağlayıcısında bir bağlantı menejeri ile sürücülere bağlanılır ve hangi sürücüye bağlanmanız gerektiği dışarıdan bir parametre gönderilerek belirlenir. O2 kütüphaneleri de dahil olmak üzere uygulamada bir çok yer servis sağlayıcıları kullandığı için çok gerek olmadığı sürece servis sağlayıcılar üzerinde değişklilik yapmamanız önerilir. Fakat ekstra parametre göndermek için $params değişkenine parametre ekleyebilirsiniz.

```php
<?php

namespace Service\Provider;

use Obullo\Cache\Connection;

Class Cache implements ProviderInterface
{
    public function register($c)
    {
        $c['provider:cache'] = function ($params = array('serializer' => 'SERIALIZER_NONE', 'provider' => 'redis')) use ($c) {
            $connection = new Connection($c, $params);
            return $connection->connect();
        };
    }
}

// END Cache class

/* End of file Cache.php */
/* Location: .classes/Service/Provider/Cache.php */
```

### Servisi Sağlayıcısını Yüklemek

Bir kez servis sağlayıcı load komutu ile yüklendiği zaman artık kütüphane metotlarına providerCache adıyla ulaşabilirsiniz.

```php
<?php
$this->c->load('service/provider/cache', array('serializer' => 'SERIZALIZER_NONE'));
$this->providerCache->metod();
```

Fakat daha kısa bir yazım şekli istiyorsanız ve görünürde daha önceden yüklenen bir başka cache servisi de yoksa <b>as</b> komutu kullanabilirsiniz.

```php
<?php
$this->c->load('service/provider/cache as cache', array('serializer' => 'SERIZALIZER_NONE'));
$this->cache->metod();
```

Durum controller içerisinde böyle iken size ait herhangi bir sınıf içerisinden servis sağlaycısını yüklemek aşağıdaki gibidir.

```php
<?php
$this->cache = $this->c->load('service/provider/cache', array('serializer' => 'SERIZALIZER_NONE'));
$this->cache->method();
```

### New Komutunu Kullanmak

Servis sağlayıcılara bir kez parametre gönderildiği zaman sonraki çağrımlarında hep aynı instance a göre dönerler yani otomatik olarak <b>singleton</b> yapmaya başlarlar. Eğer her defasında yeni bir nesne yaratmak istiyorsanız servis sağlayıcısı yüklemesinden önce <b>new</b> komutunu kullanmanız gerekir. Bu komut servisler için de aynı davranışı gösterir.

```php
<?php
$this->c->load('service/provider/cache', array('serializer' => 'SERIALIZER_PHP'));
$this->c->load('service/provider/cache');  // eski instance
$this->c->load('new service/provider/cache', array('serializer' => 'SERIALIZER_IGBINARY')); // yeni instance
```

### HandlerInterface

Cache sürücüler handler interface arayüzünü kullanırlar. Handler interface size cache servisinde hangi metotların ortak kullanıldığı gösterir ve eğer yeni bir sürücü yazacaksınız sizi bu metotları sınıfınıza dahil etmeye zorlar. Cache sürücüsü ortak metotları aşağıdaki gibidir.

```php
<?php

interface HandlerInterface
{
    public function __construct($c, $params = array());
    public function setOption($params);
    public function getSerializer();
    public function connect();
    public function set($key = '', $data = 60, $ttl = 60);
    public function get($key);
    public function replace($key = '', $data = 60, $ttl = 60);
    public function delete($key);
    public function keyExists($key);
}
```
Şimdi bu metotları biraz tanıyalım.


## Ortak Metotlar

-------

### $this->cache->set(mixed $key, $value, $ttl = 60);

```php
<?php
$this->cache->set('test', 'hello world', $ttl = 60);
$this->cache->set('test', 'hello world', 0);  // No expire
```
Önbellek deposuna veri kaydeder. Birinci parametre anahtar, ikici parametre değer, üçüncü parametre ise anahtara ait verinin yok olma süresidir. Üçüncü parametrenin varsayılan değeri 60 saniyedir. Eğer üçüncü parametre "0" olarak girerseniz önbelleğe kaydettiniz anahtar siz silmedikçe silinmeyecektir. Yani kalıcı olacaktır.

### $this->cache->get(string $key);

Önbellek deposundan veri okur.

```php
<?php
echo $this->cache->get('test');  // gives hello world
```

### $this->cache->set(array $key, $ttl = 60);

Eğer ilk parametreye bir dizi gönderirseniz ikinci parametreyi artık sona erme süresi olarak kullanabilirsiniz.

```php
<?php
$this->cache->set(array('test' => 'cache test', 'test 1' => 'cache test 1'), $ttl = 20);
```

### $this->cache->delete(string $key);

Anahtarı ve bu anahtara kaydedilen değeri bütünüyle siler.

```php
<?php
$this->cache->delete($key);
```

### $this->cache->setOption(array('serializer' => 'SERIALIZER_PHP'));

Encode ve decode işlemlerini için serileştirici türünü tekrar seçer, varsayılan opsiyon <b>SERIALIZER_PHP</b> dir.

* **SERIALIZER_NONE**     : Serileştirici kullanılmaz veriler raw biçiminde kaydedilir.
* **SERIALIZER_PHP**      : Varsayılan serileştirici php serializer fonksiyonudur.
* **SERIALIZER_JSON**     : Serileştiriciyi JSON encoder fonksiyonu olarak seçer.
* **SERIALIZER_IGBINARY** : Serileştiriciyi igbinary olarak seçer.


### $this->cache->replace(mixed $key, $value, $ttl = 60);

Varsayılan anahtara ait değeri yeni değer ile günceller.

### $this->cache->keyExists(string $key);

Eğer girilen anahtar önbellekte mevcut ise <b>true</b> değerine aksi durumda <b>false</b> değerine döner.

### $this->cache->getMetaData(string $key);

Girilen anahtar ile ilgili meta data verilerine döner.

### $this->cache->flushAll();

Önbelleği bütünüyle temizler.


## Redis Sürücüsünü Yapılandırma

-------

Ubuntu altında redis kurulumu hakkında bilgi almak için <b>warmup</b> adı verilen dökümentasyon topluluğunun hazırladığı belgeden yararlanabilirsiniz. <a href="https://github.com/obullo/warmup/tree/master/Redis">Redis Installation</a>. Aşağıda redis için yapılandırılmış konfigürasyon ayarlarını görüyorsunuz.

```php
<?php

'redis' => array(

   'servers' => array(
                    array(
                      'hostname' => env('REDIS_HOST'),
                      'port'     => '6379',
                       // 'timeout'  => '2.5',  // 2.5 sec timeout, just for redis cache
                      'weight'   => '1'         // The weight parameter effects the consistent hashing 
                                                // used to determine which server to read/write keys from.
                    ),
    ),
    'auth' =>  env('REDIS_AUTH'),       // connection password
    'serializer' =>  'SERIALIZER_PHP',  // SERIALIZER_NONE, SERIALIZER_PHP, SERIALIZER_IGBINARY
    'persistentConnect' => 0,           // Enable / Disable persistent connection, "1" on "0" off.
    'reconnectionAttemps' => 100,
),
```

Birden fazla bağlantı yapmak istiyorsanız ( multi-connection ) konfigürasyon dosyasında server dizisi içine aşağıdaki gibi birden fazla dizi girmeniz gerekir.

Örnek

```php
<?php

 'redis' => array(
       'servers' => array(
                        array(
                          'hostname' => env('REDIS_HOST'),
                          'port'     => '6379',
                           // 'timeout'  => '2.5',  // 2.5 sec timeout, just for redis cache
                          'weight'   => '1'         // The weight parameter effects the consistent hashing 
                                                    // used to determine which server to read/write keys from.
                        ),
                        array(
                          'hostname' => env('REDIS_HOST'),
                          'port'     => '6379',
                           // 'timeout'  => '2.5',  // 2.5 sec timeout, just for redis cache
                          'weight'   => '1'         // The weight parameter effects the consistent hashing 
                                                    // used to determine which server to read/write keys from.
                        )
        ),
    ),
```

## Redis Metotları

-------

Redis sürücüsü seçildiğinde bazı ek özellikler ve metotlar gelir bu özellikleri kullanabilmeniz için bunları biliyor olmanız gereklidir. Aşağıda şu anki sürümde tanımlı olan metotlar basitçe anlatılmıştır.

#### $this->cache->auth(string $password)

Eğer yetkilendirme konfigürasyon dosyasından yapılmıyorsa bu fonksiyon ile manual olarak yetkilendirme yapabilirsiniz. Şifre plain-text biçiminde olmalıdır.


#### $this->cache->setOption(string $option)

Varsayılan serileştiriciyi değiştirir.

```php
<?php
$this->cache->setOption('SERIALIZER_NONE');
```

#### $this->cache->set(mixed $key, mixed $data, int optional $expiration)

Önbellek deposuna veri kaydeder. Kaydetme işlemlerinde string ve array türlerini kullanabilirsiniz.

```php
<?php
$this->cache->set('key', 'value');    // Basit key -> string değer
$this->cache->set('key','value', 10); // 10 saniye yok olma süresi
$this->cache->set('key', array('testKey' => 'test value', 'testKey2' => 'test value 2'));  // Array tipi set
```

Array türü aşağıdaki iki yöntemden biriyle olabilir.

```php
<?php
$this->cache->set('example:key', 'value');
```
veya

```php
<?php
$this->cache->set(array('example' => 'value'));
```

#### $this->cache->get($key)

Önbellek deposundan veri okur. Okuma işlemlerinde string ve array türlerini kullanabilirsiniz.

```php
<?php
$this->cache->get('key');           // Gives value
$this->cache->get('example:key');   // Gives value
$this->cache->get(array('example'));  // Gives value
```

#### $this->cache->append(string $key, $value);

Varolan veri üzerine string biçiminde yeni değer ekler.

```php
<?php
$this->cache->set('key', 'value1');
$this->cache->append('key', 'value2'); /* 12 */
$this->cache->get('key'); /* 'value1value2' */
```

#### $this->cache->getSet(string $key, );

Önbellek deposuna yeni veriyi kaydederken eski veriye geri dönerek eski veriyi elde etmenizi sağlar.

```php
<?php
$this->cache->set('x', '42');
$exValue = $this->cache->getSet('x', 'lol');  // return '42', replaces x by 'lol'
$newValue = $this->cache->get('x')'           // return 'lol'
```

#### $this->cache->renameKey(string $key, string $newKey);
Mevcut bir anahtarı yeni bir anahtar ile değiştirme imkanı sağlar.
Değiştirilmek istenen anahtar var ise işlem sonucu **true** yok ise **false** dönecektir.

>**Önemli:** Yeni anahtar daha önce tanımlanmış ise yeni anahtar bir öncekinin üzerine yazacaktır.

#### $this->cache->getAllKeys();

Bütün anahtarları dizi olarak döndürür.

```php
<?php
print_r($this->cache->getAllKeys());
```
Çıktı:

```php
<?php
// array ( "key1", "key2", "key3")
```
#### $this->cache->hSet();

Belirtilen anahtara string değer ekler ve anahtarı hash olarak tutar. Daha önce hash`lenmiş bir anahtar ile değer eklenmek istendiğinde yeni değeri ekler fakat işlem sonucu **false** döner.


```php
<?php
$this->cache->delete('h')
$this->cache->hSet('h', 'key1', 'hello'); /* 1, 'key1' => 'hello' in the hash at "h" */
$this->cache->hGet('h', 'key1'); /* returns "hello" */

$this->cache->hSet('h', 'key1', 'plop'); /* 0, value was replaced. */
$this->cache->hGet('h', 'key1'); /* returns "plop" */
```

#### $this->cache->hGet();

Anahtarı hash`lenmiş değer tablosundan bir değere ulaşmanızı sağlar.
Saklanan değere erişmek için belirtilen anahtarı hash tablosunda veya diğer anahtarlar içinde arayacaktır. Bulunamaz ise sonuç **false** dönecektir. 

```php
<?php
$this->cache->hGet('h', 'key');
```
#### $this->cache->hGetAll();

Tüm değerleri string dizisi olarak döndürür.
```php
<?php
$this->cache->delete('h');
$this->cache->hSet('h', 'a', 'x');
$this->cache->hSet('h', 'b', 'y');
$this->cache->hSet('h', 'c', 'z');
$this->cache->hSet('h', 'd', 't');
var_dump($this->cache->hGetAll('h'));
```

Çıktı:
```php
<?php
array(4) {
  ["a"]=>
  string(1) "x"
  ["b"]=>
  string(1) "y"
  ["c"]=>
  string(1) "z"
  ["d"]=>
  string(1) "t"
}
```
#### $this->cache->hLen();

Hash tablosundaki değerlerin toplamını rakam olarak döndürür.

```php
<?php
$this->cache->delete('h')
$this->cache->hSet('h', 'key1', 'hello');
$this->cache->hSet('h', 'key2', 'plop');
$this->cache->hLen('h'); /* returns 2 */
```

#### $this->cache->hDel();

Hash tablosundan bir değeri siler. Hash tablosu yada belirtilen anahtar yok ise sonuç **false** dönecektir.

```php
<?php
$this->cache->hDel('h', 'key');
```
#### $this->cache->hKeys();
Bir hash`teki tüm anahtarları dizi olarak döndürür.

```php
<?php
$this->cache->delete('h');
$this->cache->hSet('h', 'a', 'x');
$this->cache->hSet('h', 'b', 'y');
$this->cache->hSet('h', 'c', 'z');
$this->cache->hSet('h', 'd', 't');
var_dump($this->cache->hKeys('h'));
```

Çıktı:
```php
<?php
array(4) {
  [0]=>
  string(1) "a"
  [1]=>
  string(1) "b"
  [2]=>
  string(1) "c"
  [3]=>
  string(1) "d"
}
```
#### $this->cache->hVals();
Bir hash`teki tüm değerleri dizi olarak döndürür.

```php
<?php
$this->cache->delete('h');
$this->cache->hSet('h', 'a', 'x');
$this->cache->hSet('h', 'b', 'y');
$this->cache->hSet('h', 'c', 'z');
$this->cache->hSet('h', 'd', 't');
var_dump($this->cache->hVals('h'));
```

Çıktı:
```php
<?php
array(4) {
  [0]=>
  string(1) "x"
  [1]=>
  string(1) "y"
  [2]=>
  string(1) "z"
  [3]=>
  string(1) "t"
}
```

#### $this->cache->hIncrBy();
Bir hash üyesinin değerini belirli bir miktarda artırır.

```php
<?php
$this->cache->delete('h');
$this->cache->hIncrBy('h', 'x', 2); /* returns 2: h[x] = 2 now. */
$this->cache->hIncrBy('h', 'x', 1); /* h[x] ← 2 + 1. Returns 3 */
```
#### $this->cache->hIncrByFloat();

Bir hash üyesinin değerini float (ondalıklı) değer olarak artırmayı sağlar.

```php
<?php
$this->cache->delete('h');
$this->cache->hIncrByFloat('h','x', 1.5); /* returns 1.5: h[x] = 1.5 now */
$this->cache->hIncrByFLoat('h', 'x', 1.5); /* returns 3.0: h[x] = 3.0 now */
$this->cache->hIncrByFloat('h', 'x', -3.0); /* returns 0.0: h[x] = 0.0 now */
```
#### $this->cache->hMSet();

Tüm hash'leri doldurur. String olmayan değerleri string türüne çevirir, bunuda standart string`e dökme işlemini kullanarak yapar. Değeri **NULL** olarak saklanmış veriyi boş string olarak saklar.

```php
<?php
$this->cache->delete('user:1');
$this->cache->hMset('user:1', array('name' => 'Joe', 'salary' => 2000));
$this->cache->hIncrBy('user:1', 'salary', 100); // Joe earns 100 more now.

```
#### $this->cache->hMGet();

Hash'te özel tanımlanan alanların değerlerini dizi olarak getirir.

```php
<?php
$this->cache->delete('h');
$this->cache->hSet('h', 'field1', 'value1');
$this->cache->hSet('h', 'field2', 'value2');
$this->cache->hmGet('h', array('field1', 'field2')); /* returns array('field1' => 'value1', 'field2' => 'value2') */
```
#### $this->cache->getLastError()

En son meydana gelen hataya string biçiminde geri döner.

```php
<?php
$this->cache->getLastError();  // "ERR Error compiling script"
```

#### $this->cache->setTimeout(string $key, int $ttl)

Önceden set edilmiş bir anahtarın yok olma süresini değiştirir. TTL parametresi mili saniye formatında yazılmalıdır.

```php
<?php
$this->cache->setTimeout('key','60'); // 60 saniye sonra key silinecektir
```

#### $this->cache->type(string $key)

Girilen anahtarın redis türünden biçimine döner bu biçimler şunlardır: string, set, list, zset, hash, other

```php
<?php
$this->cache->type('key');
```

#### $this->cache->flushDB()

Geçerli veritabanından tüm anahtarları siler. Bu işlemin sonucu daima **true** döner.
```php
<?php

$this->cache->flushDB();
```

#### $this->cache->append(string $key, string or array $data)

Daha önce değer atanmış bir anahtara yeni değer ekler. Önceki değer ile birleşir.
```php
<?php

$this->cache->set('key', 'value1');
$this->cache->append('key', 'value2'); /* 12 */
$this->cache->get('key'); /* 'value1value2' */
```

##### $this->cache->keyExists(string $key)

Bir anahtarın var olup olmadığını kontrol eder.

```php
<?php

$this->cache->set('key', 'value');
$this->cache->keyExists('key'); /*  true */
$this->cache->keyExists('NonExistingKey'); /* false */
```

#### $this->cache->getMultiple(array $key)

Tüm belirtilen anahtarların değerini dizi olarak döndürür. Bir yada daha fazla anahtar değeri bulunamaz ise bu anahtarların değeri **false** olarak dizide var olacaklardır.

```php
<?php

$this->cache->set('key1', 'value1');
$this->cache->set('key2', 'value2');
$this->cache->set('key3', 'value3');
$this->cache->getMultiple(array('key1', 'key2', 'key3')); 
```
#### $this->cache->sAdd(string $key, string or array $value);

```php
<?php

$this->cache->sAdd('key1', 'value1'); /* 1, 'key1' => {'value1'} */
$this->cache->sAdd('key1', array('value2', 'value3')); /* 2, 'key1' => {'value1', 'value2', 'value3'}*/
$this->cache->sAdd('key1', 'value2'); /* 0, 'key1' => {'value1', 'value2', 'value3'}*/
```

#### $this->cache->sort(string $key, array $sort)

Saklanan değerleri parametreler doğrultusunda sıralar.

Değerler:
```php
<?php

$this->cache->delete('test');
$this->cache->sAdd('test', 2);
$this->cache->sAdd('test', 1);
$this->cache->sAdd('test', 3);
```
Kullanımı:
```php
<?php
var_dump($this->cache->sort('test')); // 1,2,3
var_dump($this->cache->sort('test', array('sort' => 'desc'))); // 5,4,3,2,1
var_dump($this->cache->sort('test', array('sort' => 'desc', 'store' => 'out'))); // (int)5
```
>** Bilgi:** **sort** methodunun kullanılabilmesi için serileştirme tipi **SERIALIZER_NONE** olarak tanımlı olması gerekmektedir.

#### $this->cache->sSize(string $key)

Belirtilen anahtara ait değerlerin toplamını döndürür.

```php
<?php

$this->cache->sAdd('key1' , 'test1');
$this->cache->sAdd('key1' , 'test2');
$this->cache->sAdd('key1' , 'test3'); /* 'key1' => {'test1', 'test2', 'test3'}*/
```
```php
<?php
$this->cache->sSize('key1'); /* 3 */
$this->cache->sSize('keyX'); /* 0 */
```

#### $this->cache->sInter(array $key)

Belirtilen anahtarlara ait değerleri bir birleriyle kesişenleri döndürür.

```php
<?php

$this->cache->sAdd('key1', 'val1');
$this->cache->sAdd('key1', 'val2');
$this->cache->sAdd('key1', 'val3');
$this->cache->sAdd('key1', 'val4');

$this->cache->sAdd('key2', 'val3');
$this->cache->sAdd('key2', 'val4');

$this->cache->sAdd('key3', 'val3');
$this->cache->sAdd('key3', 'val4');
```
```php
<?php
var_dump($this->cache->sInter('key1', 'key2', 'key3'));
```

Çıktı:
```php
<?php
array (size=2)
    0 => string 'val4' (length=4)
    1 => string 'val3' (length=4)
```
#### $this->cache->sGetMembers(string $key)

Belirtilen anahtarın değerini bir dizi olarak döndürür.

```php
<?php

$this->cache->delete('key');
$this->cache->sAdd('key', 'val1');
$this->cache->sAdd('key', 'val2');
$this->cache->sAdd('key', 'val1');
$this->cache->sAdd('key', 'val3');
```

```php
<?php
var_dump($this->cache->sGetMembers('key'));
```
Çıktı:
```php
<?php
array (size=3)
    0 => string 'val3' (length=4)
    1 => string 'val2' (length=4)
    2 => string 'val1' (length=4)
```

### Metod Referansları

-----

#### $this->cache->keyExists($key);

Belirtilen anahtarın var olup olmadığını kontrol eder.

#### $this->cache->get($key);

Belirtilen anahtarın değerini döndürür.

#### $this->cache->set($key, $data, $expiration_time);

İstenilen değeri belirtilen anahtar ile kayıt eder.

#### $this->cache->getAllKeys();

Tanımlı olan tüm anahtarları döndürür, fakat sadece file, memcached ve redis ile uyumlu çalışır.

#### $this->cache->getAllData();

Tanımlı olan tüm değerleri döndürür, fakat sadece file, memcached ve redis ile uyumlu çalışır.

#### $this->cache->delete($key);

Belirtilen anahtara ait değeri siler.

#### $this->cache->info();

Sunucuda kurulu cache sürücüsü hakkında bilgileri döndürür.

#### $this->cache->getMetaData($key);

Belirtilen anahtara ait değer hakkındaki meta bilgileri döndürür. *(Bu özelliği redis sürücüsü desteklememektedir.)*

#### $this->cache->flushAll($key);

Remove all keys from all databases.

Tanımlanmış bütün anahtarları tüm veritabanından siler.

#### $this->cache->isConnected()

Bağlantı aktif ise **true** değerine aksi durumda **false** değerine döner.
