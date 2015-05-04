
## Cache Sınıfı

------

Cache paketi çeşitli önbellekleme ( cache ) türleri için birleşik bir API sağlar. Cache paket konfigürasyonu ortam tabanlı konfigürasyon dosyası <kbd>app/config/$env/cache/</kbd>dosyasından yönetilir.

### Cache Sürcüleri

Bu sürüm için varolan cache sürücüleri aşağıdaki gibidir:

* Apc
* File
* Memcache
* Memcached
* Redis

Sürücü seçimi yapılırken küçük harfler kullanılmalıdır. Örnek : redis. Her bir önbellek türünün konfigürasyonuna <kbd>app/config/cache/$sürücü.php</kbd> adıyla ulaşılabilir.

### Servisi Yüklemek

Cache servisi aracılığı ile cache metotlarına aşğıdaki gibi erişilebilir.

```php
$this->c['cache']->metod();
```

### Servis Konfigürasyonu

Servisler uygulama içerisinde parametreleri değişmez olan ve tüm kütüphaneler tarafından ortak ( paylaşımlı ) kullanılan sınıflardır. Genellikle servisler kolay yönetilebilmek için bağımsız olan bir servis sağlayıcısına ihtiyaç duyarlar.

Cache paketini kullanabilmeniz için ilk önce servis ve servis sağlayıcısı ayarlarını kurmamız gerekir. Cache servisi uygulama içerisinde bazı yerlerde paylaşımlı olarak bazı yerlerde de parametre değişikliği gerektirdiği ( paylaşımsız yada bağımsız ) kullanıldığı için kimi zaman farklı ihtiyaçlara cevap veremez.

Bir örnek vermek gerekirse uygulamada servis olarak kurduğunuz cache kütüphanesi her zaman <b>serializer</b> parametresi ile kullanılmaya konfigüre edilmiştir ve değiştirilemez. Fakat bazı yerlerde <b>"none"</b> parametresini kullanmanız gerekir bu durumda servis sağlayıcı imdadımıza yetişir ve <b>"none"</b> parametresini kullanmanıza imkan sağlar. Böylece cache kütüphanesi yeni bir nesne oluşturarak servis sağlayıcısının diğer cache servisi ile karışmasını önler.

Bu nedenlerden ötürü cache kütüphanesi aşağıdaki gibi hem servis hem de servis sağlayıcı ( service provider ) olarak kullanılır.

### Servis Kurulumu

Servis kurulumu için tek yapmanız gereken kullanmak istediğiniz servis sağlayıcısının adını service provider get metodu içerisindeki driver anahtarı değerine sürücü adını girmek ve konfigürasyon dosyanızdaki bağlantı adını seçmektir aşağıdaki <b>default</b> bağlantısı seçilmiştir.

```php
namespace Service;

use Obullo\Container\Container;
use Obullo\Service\ServiceInterface;

class Cache implements ServiceInterface
{
    public function register(Container $c)
    {
        $c['cache'] = function () use ($c) {
            return $this->c['app']->provider('cache')->get(['driver' => 'redis', 'connection' => 'default']);
        };
    }
}

// END Cache class

/* End of file Cache.php */
/* Location: .classes/Service/Cache.php */
```

### Servis Sağlayıcısını Yüklemek

Cache kütüphanesi bağımsız olarak kullanılmak istendiği durumlarda servis sağlayıcısından direkt olarak çağrılabilir. Servis sağlayıcı yüklendiği zaman kütüphaneyi bir değişkene atayıp metotlara ulaşabilirsiniz.

```php
$this->cache = $this->c['app']->provider('cache')->get(['driver' => 'memcached', 'connection' => 'default'];
$this->cache->metod();
```

##### Servis Sağlayıcısı Bağlantıları

Servis sağlayıcısı <b>connection</b> anahtarındaki bağlantı değerini önceden <kbd>app/config/$env/cache</kbd> klasöründe tanımlı olan <b>$sürücü.php</b> dosyası connections dizisi içerisinden alır. Aşağıda memcached sürücüsü <b>default</b> bağlantısına ait bir örnek görülüyor.

```php

return array(

    'connections' => 
    [
        'default' => [
            'host' => '127.0.0.1',
            'port' => 11211,
            'weight' => 1,
            'options' => [

            ]
        ]
    ]
);

/* End of file memcached.php */
/* Location: .app/config/env/local/cache/memcached.php */
```

### HandlerInterface

Cache sürücüleri handler interface arayüzünü kullanırlar. Handler interface size cache servisinde hangi metotların ortak kullanıldığı gösterir ve eğer yeni bir sürücü yazacaksınız sizi bu metotları sınıfınıza dahil etmeye zorlar. Cache sürücüsü ortak metotları aşağıdaki gibidir.

```php
<?php

interface HandlerInterface
{
    public function connect();
    public function keyExists($key);
    public function set($key, $data = 60, $ttl = 60);
    public function get($key);
    public function replace($key, $data = 60, $ttl = 60);
    public function delete($key);
}
```

Şimdi bu metotları biraz tanıyalım.


### Ortak Metotlar Referansı

-------

##### $this->cache->set(mixed $key, $value, $ttl = 60);

Önbellek deposuna veri kaydeder. Birinci parametre anahtar, ikici parametre değer, üçüncü parametre ise anahtara ait verinin yok olma süresidir. Üçüncü parametrenin varsayılan değeri 60 saniyedir. Eğer üçüncü parametreyi "0" olarak girerseniz önbelleğe kaydetiğiniz anahtar siz silmedikçe silinmeyecektir. Yani kalıcı olacaktır.

##### $this->cache->get(string $key);

Önbellek deposundan veri okur.

##### $this->cache->set(array $key, $ttl = 60);

Eğer ilk parametreye bir dizi gönderirseniz ikinci parametreyi artık sona erme süresi olarak kullanabilirsiniz.

##### $this->cache->delete(string $key);

Anahtarı ve bu anahtara kaydedilen değeri bütünüyle siler.

##### $this->cache->replace(mixed $key, $value, $ttl = 60);

Varsayılan anahtara ait değeri yeni değer ile günceller.

##### $this->cache->keyExists(string $key);

Eğer girilen anahtar önbellekte mevcut ise <b>true</b> değerine aksi durumda <b>false</b> değerine döner.


## Memcached Sürücüsü

Ubuntu altında memcached kurulumu hakkında bilgi almak için <b>warmup</b> adı verilen dökümentasyon topluluğunun hazırladığı belgeden yararlanabilirsiniz. <a href="https://github.com/obullo/warmup/tree/master/Memcached" target="_blank">Memcached Kurulumu</a>. Aşağıda memcached için yapılandırılmış örnek konfigürasyon ayarlarını görüyorsunuz.

```php
return array(

    'connections' => 
    [
        'default' => [
            'host' => '127.0.0.1',
            'port' => 11211,
            'weight' => 1,
            'options' => [
                'persistent' => false,
                'pool' => 'connection_pool',   // http://php.net/manual/en/memcached.construct.php
                'timeout' => 30,               // seconds
                'attempt' => 100,
                'serializer' => 'php',    // php, json, igbinary
                'prefix' => null
            ]
        ]
    ],
);

/* End of file memcached.php */
/* Location: .app/config/env/local/cache/memcached.php */
```

#### Çoklu Sunucular ( Nodes )

Birden fazla memcached sunucunuz varsa konfigürasyon dosyasındaki diğer sunucu adreslerini aşağıdaki gibi nodes dizisi içerisine girmeniz gerekir.

```php
  'connections' => 
  [
      'default' => [ .. ],
      'nodes' => [
          [
              'host' => '10.0.0.168',
              'port' => 11211,
              'weight' => 1
          ],
          [
              'host' => '10.0.0.169',
              'port' => 11211,
              'weight' => 2
          ]

      ]
  ],
```

#### Servis Kurulumu

Eğer uygulama içerisinde cache servisinin memcached kullanmasını istiyorsanız <kbd>app/Classes/Service/Cache.php</kbd> dosyasındaki <b>driver</b> anahtarını <b>memcached</b> olarak değiştirin.

```php
$this->c['app']->provider('cache')->get(['driver' => 'memcached', 'connection' => 'default']);
```

### Memcached Metot Referansı

------

> Bu sınıf içerisinde tanımlı olmayan metotlar __call metodu ile php Memcached sınıfından çağrılırlar.

##### $this->cache->setSerializer($serializer = 'php');

Geçerli serializer tipini seçer. Serializer tipleri : <b>php</b>, <b>igbinary</b> ve <b>json</b> dır.

##### $this->cache->getSerializer();

Geçerli serializer tipine geri döner. Serializer tipleri : <b>php</b>, <b>igbinary</b> ve <b>json</b> dır.

##### $this->cache->setOption($option = 'OPT_SERIALIZER', $value = 'SERIALIZER_PHP');

Memcached için bir opsiyon tanımlar. Birer sabit olan opsiyonlar parametrelerden string olarak kabul edilir. Sabitler ( Constants ) hakkında daha detaylı bilgi için <a href="http://www.php.net/manual/en/memcached.constants.php">Memcahed Sabitleri</a> ne bir gözatın.


##### $this->cache->getOption($option = 'OPT_SERIALIZER');

Daha önceden set edilmiş opsiyonun değerine döner. Opsiyon sabitleri parametreden string olarak kabul edilir. Daha detaylı bilgi için <a href="http://www.php.net/manual/en/memcached.constants.php">Memcahed Sabitleri</a> ne bir gözatın.

 
##### $this->cache->get(string $key);

Anahtara atanmış değere geri döner. Anahtar mevcut değilse <b>false</b> değerine döner. Anahtar bir dizi de olabilir.

##### $this->cache->getAllKeys();

Kayıtlı tüm anahtarlara geri döner.

##### $this->cache->getAllData();

Kayıtı tüm verilere geri döner.

##### $this->cache->keyExists(string $key);

Girilen anahtar eğer mevcut ise <b>true</b> değilse <b>false</b> değerine döner.

##### $this->cache->set(mixed $key, mixed $data, int $ttl = 60);

Girilen anahtara veri kaydeder, son parametre sona erme süresine "0" girilirse veri siz silinceye kadar yok olmaz. Eğer ilk parametreye bir dizi gönderirseniz ikinci parametreyi artık sona erme süresi olarak kullanabilirsiniz.

##### $this->cache->delete(string $key);


## Redis Sürücüsü

-------

Ubuntu altında redis kurulumu hakkında bilgi almak için <b>warmup</b> adı verilen dökümentasyon topluluğunun hazırladığı belgeden yararlanabilirsiniz. <a href="https://github.com/obullo/warmup/tree/master/Redis" target="_blank">Redis Kurulumu</a>. Aşağıda redis için yapılandırılmış konfigürasyon ayarlarını görüyorsunuz.

Birden fazla redis bağlantısı yapmak istiyorsanız konfigürasyon dosyasına aşağıdaki gibi birden fazla dizi girmeniz gerekir.

Örnek

```php
'connections' => 
[
    'default' => [         // Default connection always use serializer none
        'host' => $c['env']['REDIS_HOST'],
        'port' => 6379,
        'options' => [
            'persistent' => false,
            'auth' => $c['env']['REDIS_AUTH'], // Connection password
            'timeout' => 30,
            'attempt' => 100,  // For persistent connections
            'serializer' => 'none',
            'database' => null,
            'prefix' => null,
        ]
    ],
    
    'second' => [         // Second connection always use a "serializer"
        'host' => $c['env']['REDIS_HOST'],
        'port' => 6379,
        'options' => [
            'persistent' => false,
            'auth' => $c['env']['REDIS_AUTH'],
            'timeout' => 30,
            'attempt' => 100,
            'serializer' => 'php',
            'database' => null,
            'prefix' => null,
        ]
    ],
],
```

#### Çoklu Sunucular ( Nodes )

Birden fazla redis sunucunuz varsa konfigürasyon dosyasındaki diğer sunucu adreslerini aşağıdaki gibi nodes dizisi içerisine girmeniz gerekir.

```php
  'connections' => 
  [
      'default' => [ .. ],
      'nodes' => [
          [
              'host' => '10.0.0.168',
              'port' => 11211,
              'weight' => 1
          ],
          [
              'host' => '10.0.0.169',
              'port' => 11211,
              'weight' => 2
          ]

      ]
  ],
```

Redis sürücüsü seçildiğinde bazı ek özellikler ve metotlar gelir. Aşağıda şu anki sürümde tanımlı olan metotlar basitçe anlatılmıştır.


### Redis Metot Referansı

-------

> Bu sınıf içerisinde tanımlı olmayan metotlar __call metodu ile php Redis sınıfından çağrılırlar.

##### $this->cache->auth(string $password)

Eğer yetkilendirme konfigürasyon dosyasından yapılmıyorsa bu fonksiyon ile manual olarak yetkilendirme yapabilirsiniz. Şifre plain-text biçiminde olmalıdır.

##### $this->cache->setSerializer(string $serializer);

Encode ve decode işlemleri için serileştirici türünü seçer.

* **none**     : Serileştirici kullanılmaz veriler raw biçiminde kaydedilir.
* **php**      : Php serialize() fonksiyonunu serileştiri olarak seçer.
* **json**     : Serileştiriciyi JSON encoder fonksiyonu olarak seçer.
* **igbinary** : Serileştiriciyi igbinary olarak seçer.


##### $this->cache->setOption($option = 'OPT_SERIALIZER', $value = 'SERIALIZER_NONE')

Redis için bir opsiyon tanımlar. Birer sabit olan opsiyonlar parametrelerden string olarak kabul edilir. Sabitler ( Constants ) hakkında daha detaylı bilgi için <a href="https://github.com/phpredis/phpredis#setoption">Redis setOption</a> metoduna bir gözatın.

##### $this->cache->getOption($option = 'OPT_SERIALIZER');

Redis e daha önceden set edilmiş opsiyonun değerine döner. Opsiyon sabitleri parametreden string olarak kabul edilir. Daha detaylı bilgi için <a href="https://github.com/phpredis/phpredis#getoption">Redis getOption</a> metoduna bir gözatın.

##### $this->cache->set(mixed $key, mixed $data, int optional $expiration)

Önbellek deposuna veri kaydeder. Kaydetme işlemlerinde <b>string</b> ve <b>array</b> türlerini kullanabilirsiniz Eğer ilk parametreye bir dizi gönderirseniz ikinci parametreyi artık sona erme süresi olarak kullanabilirsiniz.

>**Not:** Anahtar içerisinde ":" karakterini kullanırsanız anahtarlar gruplanarak kaydedilirler.

##### $this->cache->get($key)

Önbellek deposundan veri okur. Okuma işlemlerinde string ve array türlerini kullanabilirsiniz. Anahtar içerisinde ":" karakterini kullanarak gruplanmış verilere ulaşabilirsiniz.

```php
$this->cache->get('key');           // Çıktı value
$this->cache->get('example:key');   // Çıktı value
```

##### $this->cache->append(string $key, $value);

Varolan veri üzerine string biçiminde yeni değer ekler.

##### $this->cache->getSet(string $key, string $value);

Önbellek deposuna yeni veriyi kaydederken eski veriye geri dönerek eski veriyi elde etmenizi sağlar.

##### $this->cache->renameKey(string $key, string $newKey);

Mevcut bir anahtarı yeni bir anahtar ile değiştirme imkanı sağlar. Değiştirilmek istenen anahtar var ise işlem sonucu **true** yok ise **false** değerine dönecektir.

>**Not:** Yeni anahtar daha önce tanımlanmış ise yeni anahtar bir öncekinin üzerine yazılır.

##### $this->cache->getAllKeys();

Bütün anahtarları dizi olarak döndürür.

##### $this->cache->hSet(string $key, string $hashKey, mixed $value);

Belirtilen anahtarın alt anahtarına ( hashKey ) bir değer ekler.Metot eğer anahtara ait bir veri yoksa yani insert işleminde **true** değerine anahtara ait bir veri varsa yani replace işleminde **false** değerine döner.

```php
$this->cache->hSet('h', 'key1', 'merhaba'); // Sonuç true
$this->cache->hGet('h', 'key1'); // Sonuç "merhaba"

$this->cache->hSet('h', 'key1', 'php'); // Sonuç false döner ama değer güncellenir
$this->cache->hGet('h', 'key1');  // Sonuç "php"
```

##### $this->cache->hGet(string $key, string $hashKey);

Hash tablosundan bir değere ulaşmanızı sağlar. Saklanan değere erişmek için belirtilen anahtarı hash tablosunda veya diğer anahtarlar içinde arayacaktır. Bulunamaz ise sonuç **false** dönecektir. 

```php
$this->cache->hGet('h', 'key');   // key "h" tablosunda aranır
```

##### $this->cache->hGetAll();

Hash tablosundaki tüm değerleri bir dizi içerisinde verir.

```php
$this->cache->delete('h');
$this->cache->hSet('h', 'a', 'x');
$this->cache->hSet('h', 'b', 'y');

print_r($this->cache->hGetAll('h'));  // Çıktı array("x", "y");
```

##### $this->cache->hLen();

Hash tablosundaki değerlerin genişliğini rakam olarak döndürür.

```php
$this->cache->delete('h');
$this->cache->hSet('h', 'key1', 'php');
$this->cache->hSet('h', 'key2', 'obullo');
print_r($this->cache->hLen('h')); // sonuç 2
```

##### $this->cache->hDel();

Hash tablosundan bir değeri siler. Hash tablosu yada belirtilen anahtar yok ise sonuç **false** dönecektir.

```php
$this->cache->hDel('h', 'key');
```
##### $this->cache->hKeys();

Bir hash deki tüm anahtarları dizi olarak döndürür.

```php
$this->cache->delete('h');
$this->cache->hSet('h', 'a', 'x');
$this->cache->hSet('h', 'b', 'y');

print_r($this->cache->hKeys('h'));  // Çıktı  array("a", "b");
```

##### $this->cache->hVals();

Bir hash deki tüm değerleri dizi olarak döndürür.

```php
$this->cache->delete('h');
$this->cache->hSet('h', 'a', 'x');
$this->cache->hSet('h', 'b', 'y');

print_r($this->cache->hVals('h'));  // Çıktı array("x", "y");
```

##### $this->cache->hIncrBy();

Bir hash üyesinin değerini belirli bir miktarda artırır.

>**Not:** hIncrBy() metodunu kullanabilmek için serileştirme türü "none" olmalıdır.

```php
$this->cache->delete('h');
$this->cache->hIncrBy('h', 'x', 2);  // Sonuç:  2 / yeni değer: h[x] = 2
$this->cache->hIncrBy('h', 'x', 1);  // h[x] ← 2 + 1. sonuç 3
```
##### $this->cache->hIncrByFloat();

Bir hash üyesinin değerini float (ondalıklı) değer olarak artırmayı sağlar.

>**Not:** hIncrByFloat() metodunu kullanabilmek için serileştirme türü "none" olmalıdır.

```php
$this->cache->delete('h');
$this->cache->hIncrByFloat('h','x', 1.5);   // Sonuç 1.5: h[x] = 1.5 now
$this->cache->hIncrByFLoat('h', 'x', 1.5);  // Sonuç 3.0: h[x] = 3.0 now
$this->cache->hIncrByFloat('h', 'x', -3.0); // Sonuç 0.0: h[x] = 0.0 now
```
##### $this->cache->hMSet(string $key, array $members);

Tüm hash değerlerini doldurur. String olmayan değerleri string türüne çevirir, bunuda standart string e dökme işlemini kullanarak yapar. Değeri **null** olarak saklanmış veriyi boş string olarak saklar.

```php
$this->cache->delete('user:1');
$this->cache->hMset('user:1', array('ad' => 'Ali', 'maas' => 2000));
$this->cache->hIncrBy('user:1', 'maas', 100);  // Ali'nin maaşını 100 birim arttırdık.

```
##### $this->cache->hMGet(string $key, array $members);

Hash de özel tanımlanan alanların değerlerini dizi olarak getirir.

```php
$this->cache->delete('h');
$this->cache->hSet('h', 'field1', 'value1');
$this->cache->hSet('h', 'field2', 'value2');
$this->cache->hmGet('h', array('field1', 'field2')); 

// Sonuç: array('field1' => 'value1', 'field2' => 'value2')
```
##### $this->cache->getLastError()

En son meydana gelen hataya string biçiminde geri döner.

##### $this->cache->setTimeout(string $key, int $ttl)

Önceden set edilmiş bir anahtarın yok olma süresini değiştirir. Son parametre $ttl mili saniye formatında yazılmalıdır.

##### $this->cache->type(string $key)

Girilen anahtarın redis türünden biçimine döner bu biçimlerden bazıları şunlardır: <b>string, set, list, zset, hash</b>.

##### $this->cache->flushDB()

Geçerli veritabanından tüm anahtarları siler. Bu işlemin sonucu daima **true** döner.

##### $this->cache->append(string $key, string or array $data)

Daha önce değer atanmış bir anahtara yeni değer ekler. Yeni atanan değer önceki değer ile string biçiminde birleşir.

##### $this->cache->keyExists(string $key)

Bir anahtarın var olup olmadığını kontrol eder. Anahtar mevcut ise **true** değilse **false** değerinde döner.

##### $this->cache->getMultiple(array $key)

Tüm belirtilen anahtarların değerini dizi olarak döndürür. Bir yada daha fazla anahtar değeri bulunamaz ise bu anahtarların değeri **false** olarak dizide var olacaklardır.

```php
$this->cache->set('key1', 'value1');
$this->cache->set('key2', 'value2');
$this->cache->set('key3', 'value3');
$this->cache->getMultiple(array('key1', 'key2', 'key3')); 
```
##### $this->cache->sAdd(string $key, string or array $value);

Belirtilen değere bir değer ekler. Eğer değer zaten eklenmiş ise işlem sonucu **false** değerine döner.

```php
$this->cache->sAdd('key1', 'value1'); // 1, 'key1' => {'value1'}
$this->cache->sAdd('key1', array('value2', 'value3')); // 2, 'key1' => {'value1', 'value2', 'value3'}
$this->cache->sAdd('key1', 'value2'); // 0, 'key1' => {'value1', 'value2', 'value3'}
```

##### $this->cache->sort(string $key, array $sort)

Saklanan değerleri parametreler doğrultusunda sıralar.

Değerler:

```php
$this->cache->delete('test');
$this->cache->sAdd('test', 2);
$this->cache->sAdd('test', 1);
$this->cache->sAdd('test', 3);
```

Kullanımı:

```php
print_r($this->cache->sort('test')); // 1,2,3
print_r($this->cache->sort('test', array('sort' => 'desc')));  // 5,4,3,2,1
print_r($this->cache->sort('test', array('sort' => 'desc', 'store' => 'out'))); // (int)5
```
>**Not:** **sort** methodunun kullanılabilmesi için serileştirme türünün **"none"** olarak tanımlaması gerekmektedir.

##### $this->cache->sSize(string $key)

Belirtilen anahtara ait değerlerin toplamını döndürür.

```php
$this->cache->sAdd('key1' , 'test1');
$this->cache->sAdd('key1' , 'test2');
$this->cache->sAdd('key1' , 'test3'); // 'key1' => {'test1', 'test2', 'test3'}
```

```php
$this->cache->sSize('key1'); /* 3 */
$this->cache->sSize('keyX'); /* 0 */
```

##### $this->cache->sInter(array $key)

Belirtilen anahtarlara ait değerlerin bir birleriyle kesişenleri döndürür.

```php
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
print_r($this->cache->sInter('key1', 'key2', 'key3'));  // Çıktı array('val4', 'val3')
```

##### $this->cache->sGetMembers(string $key)

Belirtilen anahtarın değerini bir dizi olarak döndürür.

```php
$this->cache->delete('key');
$this->cache->sAdd('key', 'val1');
$this->cache->sAdd('key', 'val2');
$this->cache->sAdd('key', 'val1');
$this->cache->sAdd('key', 'val3');
```

```php
print_r($this->cache->sGetMembers('key'));  // Çıktı array('val3', 'val2', 'val1');
```

> **Not:** Bu dökümentasyonda tanımlı olmayan redis metotları __call metodu ile php Redis sınıfından çağrılırlar. Php Redis sınıfı hakkında daha detaylı dökümentasyona <a href="https://github.com/phpredis/phpredis" target="_blank">buradan</a> ulaşabilirsiniz.