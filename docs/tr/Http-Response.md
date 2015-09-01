
## Response Class

Http response sınıfının ana fonksiyonu finalize edilmiş web çıktısını tarayıcıya göndermektir. Tarayıcı başlıklarını, json yanıtı, http durum kodunu, 404 page not found yada özel bir hata mesajı göndermek response sınıfının diğer fonksiyonlarındandır. Ayrıca response sınıfı tarayıcıya gönderilen çıktıyı gzip yöntemi ile sıkıştırabilir fakat bu özellik opsiyoneldir ve bütün tarayıcılar desteklemeyebilir.

<ul>
    <li><a href="#loading-class">Sınıfı Yüklemek</a></li>
    <li>
        <a href="#output-control">Çıktı Kontrolü</a>
        <ul>
            <li><a href="#enableOutput">$this->response->enableOutput()</a></li>
            <li><a href="#disableOutput">$this->response->disableOutput()</a></li>
            <li><a href="#append">$this->response->append()</a></li>
            <li><a href="#setOutput">$this->response->setOutput()</a></li>
            <li><a href="#getOutput">$this->response->getOutput()</a></li>
        </ul>
    </li>

    <li>
        <a href="#callback-methods">Geri Çağırma Fonksiyonları</a>
        <ul>
            <li><a href="#callback">$this->response->callback()</a></li>
            <li><a href="#finalize">$this->response->finalize()</a></li>
            <li><a href="#sendHeaders">$this->response->sendHeaders()</a></li>
        </ul>
    </li>

    <li>
        <a href="#header-methods">Http Başlık Fonksiyonları</a>
        <ul>
            <li><a href="#status">$this->response->status()</a></li>
            <li><a href="#geStatus">$this->response->getStatus()</a></li>
            <li><a href="#headers-set">$this->response->headers->set()</a></li>
            <li><a href="#headers-get">$this->response->headers->get()</a></li>
            <li><a href="#headers-get">$this->response->headers->remove()</a></li>
            <li><a href="#headers-all">$this->response->headers->all()</a></li>
        </ul>
    </li>

    <li>
        <a href="#custom-methods">Özel Çıktı Metotları</a>
        <ul>
            <li><a href="#response-json">$this->response->json()</a></li>
            <li><a href="#response-show404">$this->response->show404()</a></li>
            <li><a href="#response-showError">$this->response->showError()</a></li>
        </ul>
    </li>

    <li>
        <a href="#compressing">Sıkıştırma</a>
        <ul>
            <li><a href="#compressing-test">Sıkıştırılmış Bir Sayfayı Test Etmek</a></li>
        </ul>
    </li>

</ul>

<a name="loading-class"></a>

### Sınıfı Yüklemek

```php
$this->c['response']->method();
```
Konteyner nesnesi ile yüklenmesi gerekir. Response sınıfı <kbd>app/components.php</kbd> dosyası içerisinde komponent olarak tanımlıdır.

> **Not:** Kontrolör sınıfı içerisinden bu sınıfa $this->response yöntemi ile de ulaşılabilir.

<a name="output-control"></a>

### Çıktı Kontrolü

Çıktıyı kontrol etmenizi sağlayan fonksiyonlardır.

<a name="enableOutput"></a>

##### $this->response->enableOutput();

Çıktılamayı aktif hale getirir bu opsiyon açık olduğunda tarayıcıya çıktı gönderilir.

<a name="disableOutput"></a>

##### $this->response->disableOutput();

Çıktılamayı pasif hale getirir bu opsiyon açık olduğunda tarayıcıya çıktı gönderilmez.

<a name="append"></a>

##### $this->response->append(string $output);

Çıktı gövdesine oluşturduğunuz çıktıları ekler.

```php
$this->response->append('<p>example append data</p>');
$this->response->append('<p>example append data</p>');
```
> **Not:** View paketi çıktıları oluştururken append fonksiyonunu kullanır. 

<a name="setOutput"></a>

##### $this->response->setOutput(string $data);

En son oluşan çıktıyı belirlemenizi sağlar. Bir örnek:

```php
$this->response->setOutput($data);
```
> **Not:** Eğer bu fonksiyonu kullanırsanız tüm çıktı girilen veri ile değiştirilir bu yüzden fonksiyon içerisinde en son çağrılan fonksiyon olmalıdır.

<a name="getOutput"></a>

##### $this->response->getOutput();

En son oluşturulmuş çıktı verisini almanızı sağlar. Bir örnek:

```php
$string = $this->response->getOutput();
```

<a name="callback-methods"></a>

### Geri Çağırma Fonksiyonları

Eğer çıktı aşamaları kontrol edilmek isteniyorsa callback metodu içerisinden bir isimsiz fonksiyon yardımı ile çıktı oluşturma aşamaları kontrol edilebilir.

<a name="callback"></a>

##### $this->response->callback();

```php
$this->response->callback(
    function ($response) {

        list($status, $headers, $options, $output) = $response->finalize();
        $response->sendHeaders($status, $headers, $options);

        echo $output;
    }
);
```

Aşağıdaki metotlar yalnızca response callback metodu içerisinde çıktıyı kontrol etmek için kullanılırlar.

<a name="finalize"></a>

##### $this->response->finalize();

Yalnızca response callback metodu içerisinde çıktıyı oluşturduktan sonra sırasıyla http durum kodu, http başlıkları ve opsiyonlarını ve çıktının kendisini bir dizi içerisinde verir.

<a name="sendHeaders"></a>

##### $this->response->sendHeaders();

Yalnızca response callback metodu içerisinde kullanılır ve tarayıcıya http başlıklarını göndermeyi sağlar.


<a name="header-methods"></a>

#### Http Başlık Fonksiyonları

Tarayıcı başlıklarını kontrol eden fonksiyonları içerir.

<a name="status"></a>

##### $this->response->status($code = 401, 'text');

Tarayıcı gönderilen durum kodunu belirler.

```php
$this->reponse->status('401');  // Http başlığını "Unauthorized" olarak ayarlar.
```
Http durum kodu listesi için [Buraya tıklayın](http://www.w3.org/Protocols/rfc2616/rfc2616-sec10.html)

<a name="getStatus"></a>

##### $this->response->getStatus();

Mevcut http durum kodu değerini verir.

```php
echo $this->reponse->getStatus();   // 401
```

<a name="headers-set"></a>

##### $this->response->headers->set(string $header, string $value = null, $replace = true);

Çıktı tarayıcıya gönderilmeden önce http başlıkları eklemenizi sağlar. Takip eden örnekte bir çıktının içerik türü belirleniyor.

```php
$this->response->headers->set("content-type", "application/json");
```

Ya da aşağıdaki gibi birden fazla başlık eklenebilir.

```php
$this->reponse->headers->set("HTTP/1.0 200 OK");
$this->reponse->headers->set("HTTP/1.1 200 OK");
$this->reponse->headers->set("last-modified", gmdate('D, d M Y H:i:s', time()).' GMT');
$this->reponse->headers->set("cache-control", "no-store, no-cache, must-revalidate");
$this->reponse->headers->set("cache-control", "post-check=0, pre-check=0");
$this->reponse->headers->set("pragma", "no-cache");
```

Http başlıkları tam listesi için [Buraya tıklayın](https://en.wikipedia.org/wiki/List_of_HTTP_header_fields)

<a name="headers-get"></a>

##### $this->response->headers->get();

Http başlığına eklenmiş bir başlığın değerine döner.

```php
echo $this->response->headers->get('pragma');  // no-cache
```

<a name="headers-remove"></a>

##### $this->response->headers->remove();

Http başlığından bir değeri siler.

```php
echo $this->response->headers->remove('pragma');
```

<a name="headers-all"></a>

##### $this->response->headers->all();

Http başlığında ekleniş tüm başlıklara döner.

```php
Array
(
    [content-type] => plain-text
    [pragma] => no-cache
)
```

<a name="custom-methods"></a>

#### Özel Çıktı Metotları

Json formatında yada 404 sayfa bulunamadı gibi özel başlıkları içeren metotlar aşağıda sıralanmıştır.

<a name="response-json"></a>

##### $this->response->json(array $data, mixed $header = 'default');

Json formatında kodlanmış bit metni http json başlığı ile birlikte tarayıcıya gönderir.

```php
echo $this->response->json(['test']);  // Çıktı [ "test" ]
```

İkinci parametre <kbd>config/response.php</kbd> konfigürasyon dosyasında tanımlı olan http başlıklığını kullanır varsayılan değer <kbd>default</kbd> değeridir.

```php
echo $this->response->json(['test'], 'second');
```

Yukarıda örnek tanımlı ise <kbd>second</kbd> adlı konfigürasyona ait http başlıklarını ekler.

<a name="response-show404"></a>

##### $this->response->show404();

<kbd>app/templates/errors/404.php</kbd> html şablon dosyasını kullanarak <kbd>404 Page Not Found</kbd> hatası oluşturur.

```php
$this->response->show404();
```

<a name="response-showError"></a>

##### $this->response->showError(string $message, $status_code = 500, $heading = 'An Error Was Encountered');

<kbd>app/templates/errors/general.php</kbd> html şablon dosyasını kullanarak uygulamaya özel hatalar oluşturur.

```php
$this->response->showError('Custom error message');
```

> **Not:** Hata mesajları girdilerine güvenlik amacıyla response sınıfı içerisinde özel karakter filtrelmesi yapılır.

<a name="compressing"></a>

### Sıkıştırma

Response sınıfı php <b>ob_gz_handler</b> fonksiyonunu kullanarak tarayıcıya gönderilen çıktıyı gzip formatında sıkıştırabilir. Sıkıştırma özelliğinin çalışabilmesi için bu opsiyonunun <kbd>config/response.php</kbd> konfigürasyon dosyasından açık olması gerekir.

```php
'compress' => [
    'enabled' => true,
]                       
```

<a name="compressing-test"></a>

#### Sıkıştırılmış Bir Sayfayı Test Etmek

Sıkıştırılmış bir web sayfasını test etmek için aşağıdaki 3 yöntemden birini kullanabilirsiniz.

1. Sayfa Başlıkları Görüntülemek: Firefox <a href="https://addons.mozilla.org/en-US/firefox/addon/live-http-headers/" target="_blank">Live HTTP Headers</a> eklentisi çıktı başlıklarını görüntüleyin. "Content-encoding: gzip" satırını kontrol edin.

2. Belge Boyutunu İncelemek: Firefox Web Developer eklentisi kullanılarak  <kbd>Toolbar > Information > View Document Size</kbd> sekmesi sayfanın sıkıştırılp sıkıştırılmadığı bilgisini verir.

3. Bir Web Sitesi İle: <a href="https://www.google.com.tr/search?q=gzip+test+tool" target="_blank">Online Gzip test araçları</a>. 

> **Not:** Yukarıdaki başlıklar online test için hazırlanmıştır yerel testlerde farklı yöntemler kullanmalısınız.

Sıkıştırma açıkken eğer beyaz bir boş sayfa hatası alıyorsanız bu tarayıcıya yanlış bir çıktı gönderdiğiniz anlamına gelir. Php dosyalarınızın herhangi birininin sonunda fazladan bir satır boşluk karakteri kalmış olabilir. Sıkıştırmanın çalışabilmesi için çıktılama tamponundan önce response sınıfının tarayıcıya hiçbirşey göndermemesi gerekir.