
## Curl Sınıfı

Daniel Stenberg tarafından yazılan libcurl kütüphanesi farklı türdeki sunuculara bağlanmaya ve farklı protokollerle iletişim kurmaya yarar. libcurl http, https, ftp, gopher, telnet, dict, file ve ldap protokollerini destekler. Ayrıca, HTTPS sertifikalarını, HTTP isteklerini, HTTP form karşıya yüklemesini, vekilleri, çerezleri, kullanıcı ve parolalı kimlik doğrulamasını desteklemektedir.

Obullo Curl sınıfı basit curl işlevlerini yerine getirmek amacıyla yazılmıştır multiCurl ve upload özelliklerini desteklemez. Daha kapsamlı bir paket için composer ile <a href="http://guzzle3.readthedocs.org/" target="_blank">Guzzle</a> paketini indirip <kbd>app/classes/Service</kbd> klasörü altında <b>servis</b> olarak konfigüre edebilirsiniz.

<ul>
    <a href="#running">Çalıştırma</a>
    <ul>
        <li><a href="#loading-class">Sınıfı Yüklemek</a></li>
        <li>
            <a href="#http-requests">Http Metotları ( GET, POST, DELETE, HEAD, OPTIONS, PUT, PATCH )</a>
        </li>

        <li>
            <a href="#set-methods">Set Metotları</a>
            <ul>
                <li><a href="#set-methods">$client->setHeader()</a></li>
                <li><a href="#set-methods">$client->unsetHeader()</a></li>
                <li><a href="#set-methods">$client->setCookie()</a></li>
                <li><a href="#set-methods">$client->setCookieJar()</a></li>
                <li><a href="#set-methods">$client->setBody()</a></li>
                <li><a href="#set-methods">$client->setUserAgent()</a></li>
                <li><a href="#set-methods">$client->setReferer()</a></li>
                <li><a href="#set-methods">$client->setPort()</a></li>
                <li><a href="#set-methods">$client->setTimeout()</a></li>
                <li><a href="#set-methods">$client->setVerbose()</a></li>
                <li><a href="#set-methods">$client->setOpt()</a></li>
                <li><a href="#set-methods">$client->setAuth()</a></li>
            </ul>
        </li>

        <li>
            <a href="#get-methods">Get Metotları</a>
            <ul>
                <li><a href="#get-methods">$client->getBody()</a></li>
            </ul>
        </li>

        <li>
            <a href="#response-methods">Response Metotları</a>
            <ul>
                <li><a href="#response-methods">$client->response->getError()</a></li>
                <li><a href="#response-methods">$client->response->getBody()</a></li>
                <li><a href="#response-methods">$client->response->getStatusCode()</a></li>
                <li><a href="#response-methods">$client->response->getInfo()</a></li>
                <li><a href="#response-methods">$client->response->getHeaders()</a></li>
            </ul>
        </li>

        <li>
            <a href="#request-methods">Request Metotları</a>
            <ul>
                <li><a href="#request-methods">$client->request->getMethod()</a></li>
                <li><a href="#request-methods">$client->request->getBody()</a></li>
                <li><a href="#request-methods">$client->request->getHeaders()</a></li>
            </ul>
        </li>

        <li>
            <a href="#additional-info">Yardımcı Bilgiler</a>
            <ul>
                <li><a href="#helper-methods">$client->jsonDecode()</a></li>
                <li><a href="#guzzle">Guzzle Servis Konfigürasyonu</a></li>
            </ul>
        </li>

    </ul>
</ul>

<a name="running"></a>

### Çalıştırma

<a name="loading-class"></a>

#### Sınıfı Yüklemek

```php
use Obullo\Utils\Curl\Client;
```

Curl kütüphanesi Utils içerisinden çağırılan yardımcı bir sınıftır bu nedenle <b>use</b> komutu ile çağırıldıktan sonra ilan Client sınıfı ile edilir.

```php
$client = new Client;
echo $client->get('http://httpbin.org?a=1&b=2', ['var', 'test'])->getBody();
```

<a name="http-requests"></a>

#### Http İstekleri

##### $client->get(string $url, $queryParams = [])

Girilen sunucuya bir Http <kbd>GET</kbd> isteği gönderir. Varsa sorgu parametreleri ikinci parametreden array olarak da gönderilebilir.

```php
echo $client->get('http://httpbin.org?a=1&b=2', ['var', 'test'])->getBody();
```

```html
{
  "args": {
    "foo": "bar"
  }, 
  "data": "", 
  "files": {}, 
  "form": {
    "var": "test"
  }, 
  "headers": {
    "Accept": "*/*", 
    "Cache-Control": "max-age=259200", 
    "Content-Length": "8", 
    "Content-Type": "application/x-www-form-urlencoded", 
    "Host": "httpbin.org", 
    "Via": "1.1 localhost:3128 (squid/2.7.STABLE9)"
  }, 
  "json": null, 
  "url": "http://httpbin.org/post?foo=bar"
}

```

##### $client->post(string $url, $postFields = [])

Girilen sunucuya bir Http <kbd>POST</kbd> isteği gönderir. Varsa post verisi ikinci parametreden array olarak gönderilebilir.

##### Bir form verisi göndermek

```php
echo $client->post('http://httpbin.org/post?foo=bar', ['var' => 'test'])->getBody();
```

```html
{
  "args": {
    "foo": "bar"
  }, 
  "data": "", 
  "files": {}, 
  "form": {
    "var": "test"
  }, 
  "headers": {
    "Accept": "*/*", 
    "Cache-Control": "max-age=259200", 
    "Content-Length": "8", 
    "Content-Type": "application/x-www-form-urlencoded", 
    "Host": "httpbin.org", 
    "Via": "1.1 localhost:3128 (squid/2.7.STABLE9)"
  }, 
  "json": null, 
  "origin": "10.0.0.122, 78.189.19.92", 
  "url": "http://httpbin.org/post?foo=bar"
}
```

##### Bütün veri göndermek

```php
echo $client->setBody("test body content")->post('http://httpbin.org/post?foo=bar')->getBody();
```

```html
{
  "args": {
    "foo": "bar"
  }, 
  "data": "test body content", 
  "files": {}, 
  "form": {}, 
  "headers": {
    "Accept": "*/*", 
    "Cache-Control": "max-age=259200", 
    "Content-Length": "17", 
    "Content-Type": "text/plain", 
    "Host": "httpbin.org", 
    "Via": "1.1 localhost:3128 (squid/2.7.STABLE9)"
  }, 
  "json": null, 
  "url": "http://httpbin.org/post?foo=bar"
}
```

##### Örnek Bir Xml Verisi

```php
echo $client
    ->setUserAgent('test')
    ->setBody("<root><test>example</test></root>")
    ->post('http://httpbin.org/post?foo=bar')->getBody();
```

```php
{
  "args": {
    "foo": "bar"
  }, 
  "data": "example", 
  "files": {}, 
  "form": {}, 
  "headers": {
    "Accept": "*/*", 
    "Cache-Control": "max-age=259200", 
    "Content-Length": "33", 
    "Content-Type": "application/xml; charset=utf-8", 
    "Host": "httpbin.org",
    "User-Agent": "test", 
    "Via": "1.1 localhost:3128 (squid/2.7.STABLE9)"
  }, 
  "json": null, 
  "origin": "*", 
  "url": "http://httpbin.org/post?foo=bar"
}
```

##### $client->put(string $url, ['body' => ''])

Put istekleri varolan bir http kaynağını bütünüyle değiştirmek için kullanılır. Girilen sunucuya bir Http <kbd>PUT</kbd> isteği gönderir. Varsa put verisi ikinci parametreden array olarak gönderilebilir.

```php
echo $client->put('http://httpbin.org/put', ['body' => 'test'])->getBody();
```

```php
{
  "args": {}, 
  "data": "test", 
  "files": {}, 
  "form": {}, 
  "headers": {
    "Accept": "*/*", 
    "Cache-Control": "max-age=259200", 
    "Content-Length": "4", 
    "Content-Type": "text/plain", 
    "Host": "httpbin.org", 
    "Via": "1.1 localhost:3128 (squid/2.7.STABLE9)"
  }, 
  "json": null,  
  "url": "http://httpbin.org/put"
}
```

```php
echo $client->setBody('{"test": "1"}')->put('http://httpbin.org/put')->getBody();
```

```php
{
  "args": {}, 
  "data": "{\"test\": \"1\"}", 
  "files": {}, 
  "form": {}, 
  "headers": {
    "Accept": "*/*", 
    "Cache-Control": "max-age=259200", 
    "Content-Length": "13", 
    "Content-Type": "application/json; charset=utf-8", 
    "Host": "httpbin.org", 
    "Via": "1.1 localhost:3128 (squid/2.7.STABLE9)"
  }, 
  "json": {
    "test": "1"
  }, 
  "origin": "*", 
  "url": "http://httpbin.org/put"
}
```     


##### $client->patch(string $url, ['body' => ''])

Patch istekleri varolan bir http kaynağını modifiye etmek veya değiştirmek için kullanılır. Girilen sunucuya bir Http <kbd>PATCH</kbd> isteği gönderir. Varsa patch verisi ikinci parametreden array olarak gönderilebilir.

```php
echo $client->setBody('{"test": "1"}')->patch('http://httpbin.org/patch')->getBody();
```

Daha çok patch örneği için bu adrese <a href="https://www.mnot.net/blog/2012/09/05/patch">https://www.mnot.net/blog/2012/09/05/patch</a> gözatabilirsiniz.


<a name="ref-http-requests"></a>

#### Http Metotları

-------

##### $client->get($url, $queryParams = array());

Girilen sunucuya bir Http <kbd>GET</kbd>  isteği gönderir. Varsa sorgu parametreleri ikinci parametreden array olarak da gönderilebilir.

##### $client->post($url, $postFields = array());

Girilen sunucuya bir Http POST isteği gönderir. Varsa sorgu parametreleri ikinci parametreden array olarak da gönderilebilir.

##### $client->put($url, $data = null);

Girilen sunucuya bir Http PUT isteği gönderir. Varsa sorgu parametreleri ikinci parametreden array olarak da gönderilebilir.

##### $client->delete($url, $postFields = array());

Girilen sunucuya bir Http <kbd>DELETE</kbd> isteği gönderir. Varsa delete verisi ikinci parametreden array olarak gönderilebilir.

##### $client->patch($url, $data = null);

Girilen sunucuya bir Http <kbd>PATCH</kbd> isteği gönderir. Varsa patch verisi ikinci parametreden array olarak gönderilebilir.

##### $client->options($url, $queryParams= array());

Girilen sunucuya bir Http <kbd>OPTIONS</kbd> isteği gönderir. Varsa sorgu parametreleri ikinci parametreden array olarak gönderilebilir.

##### $client->head($url, $queryParams = array());

Girilen sunucuya bir Http <kbd>HEAD</kbd>  isteği gönderir. Varsa sorgu parametreleri ikinci parametreden array olarak da gönderilebilir.

##### $client->createRequest(string $method, string $url, mixed $data = null);

Girilen sunucuya bir belileyeceğiniz bir <kbd>ÖZEL</kbd> method isteği gönderir. Varsa sorgu parametreleri yada veri ikinci parametreden gönderilebilir.

<a name="set-methods"></a>

#### Set Metotları

-------

##### $client->setHeader(string|array $key, $val);

Curl tarayıcısı başlıklarını bir değişken içerisinde toplar curl isteği oluştuğunda bu başlıkları CURLOPT_HTTPHEADER opsiyonuna atar.

##### $client->setCookie(array $key, $val);

Curl tarayıcısı ile birlikte gönderimek üzerre http isteği başlığına çerezler ekler.

##### $client->setCookieJar(string $cookieJar);

Bağlantı kapandığında tüm dahili çerezlerin kaydedileceği dosyanın ismini belirler.

##### $client->unsetHeader($key);

Http başlıklığına daha önce atanmış çerez başlığını siler.

##### $client->setOpt(CURLOPT_CONSTANT, $value);

Curl tarayıcısı için bir CURLOPT_* seçeneklerini atar. Tanımlı sabitlere <a href="http://php.net/manual/tr/function.curl-setopt.php" target="_blank">php dökümentasyonu</a> curl sayfasından ulaşabilirsiniz.

##### $client->setTimeout(int $timeout);

<kbd>CURLOPT_CONNECTTIMEOUT</kbd> ve <kbd>CURLOPT_TIMEOUT</kbd> opsiyonlarını birlikte girilen değere eşitler. Bu değerler ayrı ayrı kullanılmak isteniyorsa <kbd>$this->setOpt(CURLOPT_TIMEOUT, $timeout);</kbd> fonksiyonu kullanılarak yeni değerler ile ayrı ayrı atanabilirler.

##### $client->setUserAgent(string $agent);

Curl tarayıcısına ait tarayıcı adı ve versiyonunu belirler.

##### $client->setReferer(string $referer);

Curl tarayıcısı http başlığına referans bilgisini yani "Referer": "x" değerini atar.

##### $client->setBody(string $text);

Http isteği ile birlikte raw formatında bütün bir veri gönderebilmek için kullanılır örnek xml yada json verileri.

##### $client->setPort(int $port);

CURLOPT_PORT değerine girilen port değerini atar.

##### $client->setVerbose($on = true);

Ayrıntılı bilgi çıktılanması için true olmalıdır. Çıktıyı standart hataya veya CURLOPT_STDERR kullanarak belirtilen dosyaya yazar.

##### $client->setAuth($username, $password, 'basic/digest');

Http basic yada http digest türündeki yetki doğrulama işlevlerini yerine getirir. İkinci parametre yetki doğrulama türünü belirler ( digest veya basic olmalıdır ).

<a name="get-methods"></a>

#### Get Metotları

-------

##### $client->getBody();

Response nesnesini oluşturmadan raw türünde doğal istek çıktısına döner.


<a name="response-methods"></a>

#### Response Metotları

-------

##### $client->response->getError();

İşlem sonucu eğer bir hata varsa hata değerine aksi durumda null değerine döner.

##### $client->response->getHeaders();

İşlem sonucu karşı sunucuda oluşan http başlıklarına bir dizi içerisinde geri döner.

##### $client->response->getBody();

Response nesnesini oluşturup raw türünde doğal istek çıktısına döner.

##### $client->response->getStatusCode();

İşlem sonucu dönen http durum kodunu verir.

##### $client->response->getInfo();

curl_getinfo(); fonksiyonunu çalıştırır fonksiyon, işlem sonucuna ait ayrıntılı bilgilere bir dizi içerisinde geri döner.

<a name="request-methods"></a>

#### Request Metotları

-------

##### $client->request->getHeaders();

Karşı sunucuya gönderilmek için oluşturulmuş http başlıklarına bir dizi içerisinde geri döner.

##### $client->request->getMethod();

Karşı sunucuya gönderilen metot türünü verir. ( POST, GET, PUT, DELETE .. )

##### $client->request->getBody();

Request nesnesini oluşturup daha önce setBody() ile oluşturulmuş gövdeye geri döner.

<a name="additional-info"></a>

### Yardımcı Bilgiler

-------

#### $client->jsonDecode($json, $assoc = false, $depth = 512, $options = 0)

<kbd>json_decode()</kbd> fonksiyonu için kurtarıcı metotdur bir http isteğinden dönen yanıt json formatında ise çözümlemek için bu fonksiyonun ilk parametresine gönderilmelidir.

<a name="guzzle"></a>

#### Guzzle Servis Kurulumu

Obullo Curl sınıfı basit curl işlevlerini yerine getirmek amacıyla yazılmıştır multiCurl ve upload özelliklerini desteklemez. Daha kapsamlı bir paket için composer ile <a href="http://guzzle3.readthedocs.org/" target="_blank">Guzzle</a> paketini indirip <kbd>app/classes/Service</kbd> klasörü altında <b>servis</b> olarak konfigüre edebilirsiniz.

Composer kurulumu için Obullo composer kurulum dökümentasyonu [Composer.md](/Application/Docs/tr/Composer.md) dosyasına bir gözatın.

Kurulumu gerçekleştirdikten sonra <kbd>app/classes/Service/</kbd> klasörü altına Guzzle.php adı ile bir dosya oluşturup aşağıdaki gibi servisi konfigure edin.

```php
namespace Service;

use GuzzleHttp\Client;
use Obullo\Service\ServiceInterface;
use Obullo\Container\ContainerInterface;

class Guzzle implements ServiceInterface
{
    public function register(ContainerInterface $c)
    {
        $c['guzzle'] = function () {
            return new Client;
        };
    }
}
```

Artık client metotlarını guzzle sınıfı üzerinden aşağıdaki gibi çağırabilirsiniz.

```php
$request = $this->c['guzzle']->get('https://google.com');
$response = $request->send();

echo $response->getBody();
```

Güncel sürüme ait guzzle dökümentasyonu için bu linkten <a href="https://media.readthedocs.org/pdf/guzzle/latest/guzzle.pdf" target="_blank">https://media.readthedocs.org/pdf/guzzle/latest/guzzle.pdf</a> faydalanabilirsiniz.