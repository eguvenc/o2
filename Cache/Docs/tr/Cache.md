
## Cache Sınıfı

------

Cache paketi çeşitli önbellekleme ( cache ) türleri için birleşik bir arayüz sağlar. Cache paket konfigürasyonu ortam tabanlı konfigürasyon dosyası <kbd>app/config/$env/cache/</kbd> dosyasından yönetilir.

<ul>
<li>
    <a href="#configuration">Konfigürasyon</a>
    <ul>
        <li><a href="#service-configuration">Servis Konfigürasyonu</a></li>
        <li><a href="#service-setup">Servis Kurulumu</a></li>
    </ul>
</li>

<li>
    <a href="#common-methods">Ortak Metotlar Referansı</a>
    <ul>
        <li><a href="#common-set">$this->cache->set()</a></li>
        <li><a href="#common-get">$this->cache->get()</a></li>
        <li><a href="#common-delete">$this->cache->delete()</a></li>
        <li><a href="#common-replace">$this->cache->replace()</a></li>
        <li><a href="#common-exists">$this->cache->exists()</a></li>
    </ul>
</li>
</ul>


#### Cache Sürcüleri

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

<a name="service-configuration"></a>

### Servis Konfigürasyonu

Servisler uygulama içerisinde parametreleri değişmez olan ve tüm kütüphaneler tarafından ortak ( paylaşımlı ) kullanılan sınıflardır. Genellikle servisler kolay yönetilebilmek için bağımsız olan bir servis sağlayıcısına ihtiyaç duyarlar.

Cache paketini kullanabilmeniz için ilk önce servis ve servis sağlayıcısı ayarlarını kurmamız gerekir. Cache servisi uygulama içerisinde bazı yerlerde paylaşımlı olarak bazı yerlerde de parametre değişikliği gerektirdiği ( paylaşımsız yada bağımsız ) kullanıldığı için kimi zaman farklı ihtiyaçlara cevap veremez.

Bir örnek vermek gerekirse uygulamada servis olarak kurduğunuz cache kütüphanesi her zaman <b>serializer</b> parametresi ile kullanılmaya konfigüre edilmiştir ve değiştirilemez. Fakat bazı yerlerde <b>"none"</b> parametresini kullanmanız gerekir bu durumda servis sağlayıcı imdadımıza yetişir ve <b>"none"</b> parametresini kullanmanıza imkan sağlar. Böylece cache kütüphanesi yeni bir nesne oluşturarak servis sağlayıcısının diğer cache servisi ile karışmasını önler.

Bu nedenlerden ötürü cache kütüphanesi aşağıdaki gibi hem servis hem de servis sağlayıcı ( service provider ) olarak kullanılır.

<a name="service-setup"></a>

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
            return $this->c['app']->provider('cache')->get(
                [
                  'driver' => 'redis',
                  'connection' => 'default'
                ]
            );
        };
    }
}

// END Cache class

/* End of file Cache.php */
/* Location: .classes/Service/Cache.php */
```

<a name="common-methods"></a>

### HandlerInterface

Cache sürücüleri handler interface arayüzünü kullanırlar. Handler interface size cache servisinde hangi metotların ortak kullanıldığı gösterir ve eğer yeni bir sürücü yazacaksınız sizi bu metotları sınıfınıza dahil etmeye zorlar. Cache sürücüsü ortak metotları aşağıdaki gibidir.

```php
interface CacheHandlerInterface
{
    public function connect();
    public function exists($key);
    public function set($key, $data = 60, $ttl = 60);
    public function get($key);
    public function replace($key, $data = 60, $ttl = 60);
    public function delete($key);
}
```

Şimdi bu metotları biraz tanıyalım.


<a name="common-set"></a>
<a name="common-get"></a>
<a name="common-delete"></a>
<a name="common-replace"></a>
<a name="common-exists"></a>

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

##### $this->cache->exists(string $key);

Eğer girilen anahtar önbellekte mevcut ise <b>true</b> değerine aksi durumda <b>false</b> değerine döner.

<a name="memcached-driver"></a>

## Memcached Sürücüsü

Memcached sürücüsü kurulum konfigürasyon ve sınıf referansı için [Memcached.md](Docs/tr/Memcached.md) dosyasını okuyunuz.

## Redis Sürücüsü

Redis sürücüsü kurulum konfigürasyon ve sınıf referansı için [Redis.md](Docs/tr/Redis.md) dosyasını okuyunuz.