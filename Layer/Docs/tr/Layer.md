
## Katmanlar

Çok katmanlı programlama tekniği hiyerarşik kontrolör programlama kalıbından türetilmiş ( bknz. <a href="http://www.javaworld.com/article/2076128/design-patterns/hmvc--the-layered-pattern-for-developing-strong-client-tiers.html" target="_blank">Java Hmvc.</a> ) uygulamanızı ölçeklenebilir hale getirmek için kullanılan bir tasarım kalıbıdır. Çok katmanlı mimari MVC katmanlarını bir üst-alt hiyerarşisi içerisinde çözümler. Uygulama içerisinde tekrarlayan bu model yapılandırılmış bir client-tier mimarisi sağlar.

![Katmanlar](/Layer/Docs/images/layers.png?raw=true "Katmanlı Programlama")

Her bir katman basit kontrolör sınıflarıdır. Layer sınıfı tarafından tekralanabilir olarak çağrılabilen katmanlar uygulamayı parçalayarak farklı işlevsel özellikleri bileşen yada web servisleri haline getirir.

### Katmanlı Mimariyi Kullanmak

Katmanlı mimari sunum ( presentation ) katmanınının yazılım geliştirme sürecini etkileyici bir şekilde yönetir. Bu mimariyi kullanmanın faydalarını aşağıdaki gibi sıralayabiliriz.

* Arayüz Tutarlılığı: Katmanlı programlama görünen varlıkları ( views ) kesin parçalara ayırır ve her bölüm kendisinden sorumlu olduğu fonksiyonu çalıştırır ( view controller ) böylece her katman bir layout yada widget hissi verir.
* Bakımı Kolay Uygulamalar: Parçalara bölünen kullanıcı arayüzü bileşenleri MVC tasarım desenine bağlı kaldıkları için bakım kolaylığı sağlarlar.
* Mantıksal Uygulamalar: Katmanlar birbirleri ile etkişim içerisinde olabilecekleri gibi uygulama üzerinde hakimiyet ve önbelleklenebilme özellikleri ile genişleyebilir mantıksal uygulamalar yaratmayı sağlarlar. Bölümsel olarak birbirinden ayrılan katmanlar bir web servis gibi de çalışabilirler.

### Görünen Varlıkları ( views ) Katmanlar İle Oluşturmak

Aşağıdaki figürde görüldüğü gibi katmanlı mimaride görünen varlıklar parçalara ayrılarak bileşen haline getirilir. 

![Katmanlar](/Layer/Docs/images/ui-components.png?raw=true "HMVC")

Katmanlı mimaride oluşturulan bileşenler birbirlerinden bağımsız parçalardır ve birbirleri ile etkileşim içinde olabilirler. Her bir bileşen kendisi için tayin edilen bir kontrolör sınıfı tarafından yönetilir ve layer paketi aracılığı ile çağrılırlar. Ayrıca birbirinden ayrılıp bağımsız hale gelen bileşenlere dışarıda ajax yada http istekleri de gönderilebilir. Yani bir modül yada web servisi haline getirilen bu parçalara dışarıdan bir http isteği ( Curl vb. ) olmadan ulaşılabileceği gibi bir http yada ajax isteği gönderilerek de ulaşılabilir.

Örneğin bir yönetim paneline ait bir gezinme çubuğu (navigation bar) bir katman aracılığı ile yönetilebilir. Gezinme çubuğu bir katman kullanılarak oluşturulduğunda view yapısından bağımsız olarak kontrol edilebilir hale gelir ve oluşturduğunuz gezinme çubuğu eğer bir ajax isteği ile tazelenmek isteniyorsa bu ajax isteği için ikinci bir kontrolör yazma gereksinimi ortadan kaldırılır bunun yerine gezinme çubuğu katmanına bir ajax istek gönderilirek gezinme çubuğu yeniden yaratılır ve uygulamada genel mvc mantığı dışına çıkılmamış olur.

Burada üzerinde durulan önemli nokta katmanlı mimaride oluşturduğunuz katmanı aynı zaman da bir web servis gibi çalıştırabiliyor olmanızdır.

Bu özelliğin yanında katman içerisinde bir kez yapılması gereken veritabanı sorguları önbelleklenebilir. Eğer örneğimizdeki gezinme çubuğunu bir web servis gibi düşünürsek, bu web servise gönderilen istekler girilen parametrelere göre önbelleklenerek uygulama performansı arttrılabilir.

#### Sınıfı Yüklemek

```php
$this->c['layer']->method();
```
Konteyner nesnesi ile yüklenmesi gerekir. Layer sınıfı <kbd>app/components.php</kbd> dosyası içerisinde komponent olarak tanımlıdır.

> **Not:** Kontrolör sınıfı içerisinden bu sınıfa $this->layer yöntemi ile de ulaşılabilir.

#### Bir Katmanı Çağırmak

Katmanlar layer sınıfı üzerinden web servis metotlarına benzer şekilde çağırılırlar. Bir katman get yada post metodları yaratılabilir.

```php
echo $this->layer->get('controller/method/args', $data = array());
echo $this->layer->post('controller/method/args', $data = array());
```

Katman istekleri <kbd>module/controller/method/args</kbd> standart url çağırma yöntemi ile Obullo router sınıfı üzerinden oluşturulurlar.

#### Merhaba Katmanlar

Katmanları daha iyi anlamak için <kbd>modules/views</kbd> klasörü altında aşağıdaki gibi Header.php adında bir view kontrolör yaratın.

```php
namespace Views;

class Header extends \Controller
{
    public function index()
    {
        echo $this->view->get(
            'header',
            [
                'header' => '<pre>HELLO HEADER LAYER</pre>'
            ]
        );
    }
}


/* End of file header.php */
/* Location: .modules/views/header.php */
```

Daha sonra oluşturduğunuz header katmanı için <kbd>modules/views/view</kbd> klasörü altında aşağıdaki gibi bir view dosyası yaratın.

header.php

```php
<div><?php echo $header ?></div>
```

###### Dosya Görünümü

```php
- modules
      - welcome
          + view
            Welcome.php
      - views
          - view
              header.php
            Header.php
```

Görüldüğü gibi header katmanına ait bir view dosyası var ve bu view dosyasını yöneten bir kontrolör dosyası mevcut.Şimdi oluşturduğunuz katmanı welcome modülü welcome kontrolör dosyası içerisinde çalıştırın.

```php
namespace Welcome;

class Welcome extends \Controller
{
    public function index()
    {
        echo $this->layer->get('views/header');
    }
}

/* End of file welcome.php */
/* Location: .modules/welcome/welcome.php */
```

Son olarak <kbd>http://myproject/welcome</kbd> sayfasını ziyaret edin. Eğer yukarıdaki işlemleri doğru yaptı iseniz welcome sayfası içerisinde bir <kbd>HELLO HEADER LAYER</kbd> çıktısı almanız gerekir.

#### Bir Katmanı Önbelleklemek

Katman sınıfı get fonksiyonunu ikinci veya üçüncü parametresine bir tamsayı gönderilirse katman çıktısı gönderilen süre kadar cache sınıfı aracılığı ile önbellekte tutulur.

```php
$this->layer->get('views/header', $expiration = 7200);
```

##### Parametreler ile Önbellekleme

Katman sınıfı get fonksiyonunu ikinci parametresinden array türünde bir parametre gönderilirse gönderilen her farklı parametre serileştirilerek json raw formatına dönüştürülür ve elde edilen çıktıdan tekil bir katman kimliği ( ID ) üretilir. Eğer önbellekleme süresi üçüncü parametreye bir tamsayı olarak girilirse elde edilen katman kimliği ile ( ID ) her seferinde parametrelere duyarlı veriler önbelleğe kaydedilmiş olur.

```php
$this->layer->get('views/header', array('user_id' => 5), $expiration = 7200);
```
Yukarıdaki örnekte kullanıcı id değerinin sağlanması ile her bir kullanıcı için oluşturulmuş katman çıktısı verilen sürede önbelleğe kaydedilir.

##### Önbelleklenmiş Katmanı Silmek

Bir katmanı önbellekten temizlemek için katman yolu (url) ve varsa katman parametrelerini flush metoduna göndermek yeterlidir.

```php
$this->layer->flush('views/header', array('user_id' => 5));
```

### Katmanlarla Bir Gezinme Çubuğu Yaratalım






Aşağıdaki resimde görüldüğü üzere yarattığınız gezinme çubuğuna ait katmanı <kbd>http://yourproject/debugger</kbd> adresini ziyaret ederek katmanların bileşenler halinde nasıl çıktılandığını takip edebilirsiniz.


![Hata Ayıklama](/Layer/Docs/images/debugger.gif?raw=true "Hata Ayıklama")


#### Function Reference

------

##### $this->layer->post(string $uri, $data = array | int, expiration = 0);  

Creates $_POST request request to "public" folder.

##### $this->layer->get(string $uri, $data = array | int, expiration = 0);

Creates $_GET request to "public" folder.

##### $this->layer->id();

Returns to current layer Id using your json encoded hash of your uri and method parameters.


#### Layer/Flush Class Function Reference

------

##### $this->layer->flush->uri(string $uri, $data = array);

Deletes cache from memory using uri and parameters.

##### $this->layer->flush->id(integer $layerId);

Deletes cache from memory by layer id.
