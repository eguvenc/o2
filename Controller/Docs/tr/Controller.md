
## Kontrolör Sınıfı ( Controller )

Kontrolör sınıfı uygulamanın kalbidir ve uygulamaya gelen HTTP isteklerinin nasıl yürütüleceğini kontrol eder. Uygulama çalıştığı anda uygulama içerisinde kullanılan temel sınıflar ( config, uri, route, logger ) kontrolör sınıfı içerisine atanırlar.


### Kontrolör Nedir ?

------

Kontrolör dosyaları uygulamada http adres satırından çağrıldığı ismi ile bağlantılı olarak çözümlenebilen basit php sınıflarıdır. Kontrolör dosyaları uygulamada <kbd>.modules/modüladı/</kbd> klasörü altında tutulurlar. Uygulama içerisinde her bir kontrolör kendine ait isim alanı ( namespace ) ile belirtilmek zorundadır aksi takdirde çözümlenemezler.

Aşağıdaki adres satırı blog adlı modül altında bulunan start isimli kontrolör dosyasını çağırır:

```php
example.com/index.php/blog/start
```

Yukarıdaki örnekte uygulama modüller altında önce <kbd>blog</kbd> isimli klasörü bulmayı dener eğer böyle bir klasör varsa daha sonra <kbd>Start</kbd> isimli kontrolör dosyasını arar ve bulursa onu yükleyerek <kbd>index</kbd> metodunu çalıştırır. 


> **Not:** Metod ismi son segment olarak girilmediğinde varsayılan olarak index metodu çalışır.


### Sınıf Yükleyici

------

Obullo da bir kontrolör sınıfı <b>load</b> metodu içerebilir. Load metodu php __construct() metodu gibi çalışır. Load metodu mevcut ise içerisinde ilan edilen tüm container nesneleri controller içerisine kaydedilir.


```php
namespace Welcome;

class Welcome extends \Controller
{
    public function load()
    {
        $this->c['url'];
        $this->c['view'];
    }
}
```


> **Not:** Kontrolör sınıfları içerisinde <kbd>construct</kbd> yerine load yönteminin kullanılmasının birinci nedeni <kbd>parent::__construct()</kbd> yazımından bağımsız bir loader elde edebilmektir, ikinci nedeni ise uygulama içerisinde load için tanımlanmış http katmanının ( middleware ) kullanımını kolaylaştırmaktır.

### Bir Hello World Örneği

-------

Şimdi kontrolör sınıfını birazda iş başında görelim, aşağıdaki gibi <kbd>welcome</kbd> adında bir klasör yaratın.

```php
-  app
-  modules
    - welcome
       - view
           welcome.php
        Welcome.php
```

Metin editörünüzü kullanarak klasör içine yine <kbd>Welcome</kbd> adında bir kontrolör sınıfı oluşturun.

```php
namespace Welcome;

class Welcome extends \Controller
{
    public function load()
    {
        $this->c['url'];
        $this->c['view'];
    }

    public function index()
    {
        $this->view->load(
            'welcome',
            [
                'title' => 'Welcome to Obullo !',
            ]
        );
    }
}

/* End of file welcome.php */
/* Location: .modules/welcome/welcome.php */
```

Daha sonra adres satırına aşağıdaki gibi bir url yazıp çağırın.

```php
example.com/index.php/welcome
```

Sayfayı ziyaret ettiğinizde <kbd>welcome/welcome/index</kbd> metodu çalışmış olmalı.

Klasör ismi ve sınıf ismi <kbd>welcome/welcome</kbd> şeklinde aynı olduğunda route sınıfı adres çözümlemesi için sınıf ismine ihtiyaç duymaz. Eğer <kbd>welcome/hello</kbd> adında bir kontrolör sınıfımız olsaydı bu durumda adres satırını aşağıdaki gibi değiştirmemiz gerekirdi.

```php
example.com/index.php/welcome/hello/index
```

### Sınıf Yükleyicinin Kullanılmadığı Durumlar

Bazı sınıflar uygulamanın yüklenmesinin başında yani <b>load()</b> metodu içerisinde yüklenmek istenmeyebilir bu gibi durumlarda sınıflara array access <b>$this->c['class']</b> yöntemi ile konteyner içerisinden direkt erişilir.

Örneğin <b>view</b> sınıfına sadece <b>index()</b> metodu içerisinde ihtiyaç duysaydık <b>$this->view</b> yöntemini kullanmak yerine array access <b>$this->c['view']</b> yöntemini kullanarak ona aşağıdaki gibi erişmeliydik.


```php
namespace Welcome;

class Welcome extends \Controller
{
    public function index()
    {
        $this->c['view']->load(
            'welcome',
            [
                'title' => 'Welcome to Obullo !',
            ]
        );
    }
}

/* End of file welcome.php */
/* Location: .modules/welcome/welcome.php */
```

Bunun gibi bazı durumlarda uygulamanızda bir performans sorunu yaşamamanız için array access yöntemi ile kütüphaneleri sadece ihtiyacınız olduğu yerlerde yüklemeniz gerekebilir.

### Method Argümanları

------

Eğer adres satırında bir metot dan sonra gelen segmentler birden fazla ise bu segmentler metot argümanları olarak çözümlenir. Örneğin aşağıdaki gibi bir url adresimizin olduğunu varsayalım:

```php
example.com/index.php/products/computer/index/desktop/123
```

Products klasörü altına Computer.php adlı bir sınıf oluşturun.

```php
-  app
-  modules
    - products
        Computer.php
```

Yukarıdaki url adresi tarayıcıda çalıştırıldığında URI sınıfı tarafından <kbd>desktop</kbd> segmenti <b>3.</b> ve <kbd>123</kbd> segmenti ise <b>4.</b> segment olarak çözümlenir.

```php
namespace Products;

class Computer extends \Controller
{
    public function index($type, $id)
    {
        echo $type;           // Çıktı  desktop
        echo $id;             // Çıktı  123 
        echo $this->uri->segment(3);    // Çıktı  123 
    }
}

/* End of file computer.php */
/* Location: .modules/products/computer.php */
```

> **Not:** Eğer URI route özelliğini kullanıyorsanız fonksiyonunuza gelen segmentler route edilmiş segment değerleri olacaktır.


### Modüller

Modüller klasörleri kapsayan en dışdaki ana dizinlerdir ve alt dizinleri içerirler. Bir klasörü bir modül getirmek mümkündür, bunun için yapmanız gereken tek şey ana bir dizin açıp alt klasörlerinizi bu anadizin içerisine taşımak. 

Örneğin bir önceki örnekte kullandığımız <b>products</b> adlı dizini <b>shop</b> adında bir modül oluşturup bu modül içerisine taşıyalım.

```php
-  app
-  modules
    - shop
       - products
            Computer.php
```

> **Not:** Şu anki sürümde bir modül altında sadece bir alt klasör açılabilir.

Böyle bir değişiklikten sonra url adresini artık aşağıdaki gibi çağırmanız gerekir.

```php
example.com/index.php/shop/products/computer/index/desktop/123
```

### İlk Açılış Sayfası

------

Uygulamaya eğer domain adresinizden sonra herhangi bir kontrolör segmenti gönderilmezse ilk açılış sayfası için varsayılan bir kontrolör tanımlamasına ihtiyaç duyar. Varsayılan kontrolör <kbd>app/routes.php</kbd> dosyasında tanımlı olmadığında uygulama hata verecektir.

Bu nedenle route dosyanızı açıp varsayılan kontrolör sınıfınızı defaultPage() metodu ile aşağıdaki gibi belirlemeniz gerekir.

```php
$c['router']->domain($c['config']['url']['webhost']);
$c['router']->defaultPage('welcome/index');

/* End of file routes.php */
/* Location: .routes.php */
```

### Dipnotlar ( Annotations )

------

Bir dipnot aslında bir metadata yı (örneğin yorum,  açıklama, tanıtım biçimini) yazıya, resime veya diğer veri türlerine tutturmaktır. Dipnotlar genellikle orjinal bir verinin belirli bir bölümümü refere ederler.

> **Not:** Dipnotarı kullanmak herhangi bir kurulum yapmayı gerektirmez ve uygulamanıza performans açısından ek bir yük getirmez. Php ReflectionClass sınıfı ile okunan dipnotlar çekirdekte herhangi bir düzenli ifade işlemi kullanılmadan kolayca çözümlenir.

Şu anki sürümde biz dipnotları sadece <b>Http Katmanlarını</b> atamak ve <b>Event</b> sınıfına tayin edilen <b>Olayları Dinlemek</b> için kullanıyoruz.

### Mevcut olan dipnotlar

<table>
    <thead>
        <tr>
            <th>Dipnot</th>    
            <th>Açıklama</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td><b>@middleware->add();</b></td>
            <td>Bir middleware katmanını uygulamaya ekler. Virgül ile birden fazla katman ismi gönderebilirsiniz.</td>
        </tr>
        <tr>
            <td><b>@middleware->remove();</b></td>
            <td>Varolan bir middleware katmanını uygulamadan çıkarır. Virgül ile birden fazla katman ismi gönderebilirsiniz.</td>
        </tr>
        <tr>
            <td><b>@middleware->method();</b></td>
            <td>Http protokolü tarafından gönderilen istek metodu belirlenen metotlardan biri ile eşleşmez ise sayfaya erişime izin verilmez. Virgül ile birden fazla katman ismi gönderebilirsiniz.</td>
        </tr>
         <tr>
            <td><b>@middleware->when()->add()</b></td>
            <td>Katmanı koşullu olarak uygulamaya ekler. Eğer http protokolü tarafından gönderilen istek metodu when metodu içerisine yazılan metotlardan biri ile eşleşmez ise bu dipnotun kullanıldığı katman uygulumaya eklenmez.</td>
        </tr>
        <tr>
            <td><b>@event->subscribe();</b></td>
            <td>Event sınıfını çağırarak subscribe metodu ile varsayılan controller için bir dinleyici atamanızı sağlar.</td>
        </tr>
    </tbody>
</table>

### Controller için dipnotları aktif etmek

Config.php konfigürasyon dosyasını açın ve annotations enabled anahtarının değerini <b>true</b> olarak güncelleyin.

```php
'annotations' => array(
    'enabled' => true,
)
```

Artık controller sınıfı metotları üzerinde dipnotları aşağıdaki gibi kullanabilirsiniz.

```php
/**
 * Index
 *
 * @middleware->when("get", "post")->add("Example")
 * 
 * @return void
 */
public function index()
{
    // ..
}


/* End of file welcome.php */
/* Location: .modules/welcome/welcome.php */
```

> **Not:** Dipnotlar hakkında daha fazla bilgiye <b>Annotations</b> paketi dökümentasyonundan ulaşabilirsiniz.


### Rezerve Edilmiş Metotlar

Kontrolör sınıfı içerisine tanımlanmış yada tanımlanması olası bazı metotlar çekirdek kütüphaneler tarafından sık sık kullanılır. Bu metotlara uygulamanın dışından erişmeye çalıştığınızda 404 hataları ile karşılaşırsınız.

<table>
    <thead>
        <tr>
            <th>Metot</th>
            <th>Açıklama</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td><b>load()</b></td>
            <td>Konteyner değişkenlerini kontrolör sınıfı değişkenlerine enjekte etmek için kullanılır.</td>
        </tr>
        <tr>
            <td><b>extend()</b></td>
            <td>View servisi tarafından kontrolör sınıfı içerisinde bir şablona genişlemek için kullanılır. Detaylı bilgi için <b>View</b> paketi dökümentasyonunu inceleyiniz.</td>
        </tr>
    </tbody>
</table>

```php
http://example.com/welcome/load  // Çıktı 404 sayfa bulunamadı
```
