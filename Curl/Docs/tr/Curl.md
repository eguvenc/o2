
## Curl Sınıfı

Daniel Stenberg tarafından yazılan libcurl kütüphanesi farklı türdeki sunuculara bağlanmaya ve farklı protokollerle iletişim kurmaya yarar. libcurl http, https, ftp, gopher, telnet, dict, file ve ldap protokollerini destekler. Ayrıca, HTTPS sertifikalarını, HTTP POST, HTTP PUT, FTP karşıya yüklemesini (PHP'nin ftp eklentisiyle de yapılabilmektedir), HTTP form karşıya yüklemesini, vekilleri, çerezleri ve kullanıcılı ve parolalı kimlik doğrulamasını da desteklemektedir.

Obullo Curl sınıfı basit curl işlevlerini yerine getirmek amacıyla yazılmıştır şu anki sürüm multiCurl ve upload özelliklerini desteklemez.

<ul>
    <a href="#running">Çalıştırma</a>
    <ul>
        <li><a href="#loading-class">Sınıfı Yüklemek</a></li>
        <li>
            <a href="#methods">Metotlar</a>
            <ul>
                <li><a href="#setUrl">$this->curl->setUrl()</a></li>
                <li><a href="#setHeader">$this->curl->setHeader()</a></li>
                <li><a href="#setCookie">$this->curl->setCookie()</a></li>
                <li><a href="#setCookieJar">$this->curl->setCookieJar()</a></li>
            </ul>
        </li>
    </ul>
</ul>

<a name="running"></a>

### Çalıştırma

<a name="loading-class"></a>

#### Sınıfı Yüklemek

```php
$this->c['cookie']->method();
```

#### Metotlar


<a name="setUrl"></a>

##### $this->curl->setUrl(string $url, $queryParameters = array())




<a name="method-reference"></a>

#### Fonksiyon Referansı

-------

##### $this->curl->setUrl(string $url, $queryParameters = array());

İstek yapılcak url değerini nesneye tanımlar. İkinci parametreden http sorgu parametreleri gönderilebilir.

##### $this->curl->setHeader(string|array $key, $val);

Curl tarayıcısı başlıklarını bir değişken içerisinde toplar curl isteği oluştuğunda bu başlıkları http_request değerine kaydeder.

##### $this->curl->setCookie(array $key, $val);

##### $this->curl->setCookieJar(string $cookieJar);

Bağlantı kapandığında tüm dahili çerezlerin kaydedileceği dosyanın ismi.

##### $this->curl->unsetHeader($key);

##### $this->curl->setOpt(CURLOPT_CONSTANT, $value);

##### $this->curl->setTimeout(int $timeout);

Set the maximum request time in seconds.

##### $this->curl->setUserAgent(string $agent);

##### $this->curl->setReferer(string $referer);

##### $this->curl->setPort(int $port);

##### $this->curl->setBasicAuthentication($username, $password);

##### $this->curl->setDigestAuthentication($username, $password);

##### $this->curl->verbose($on = true);

Ayrıntılı bilgi çıktılanması için true olmalıdır. Çıktıyı standart hataya veya CURLOPT_STDERR kullanarak belirtilen dosyaya yazar.

##### $this->curl->get($data = array());

##### $this->curl->post($data = array());

##### $this->curl->put($data = array());

##### $this->curl->delete($data = array());

##### $this->curl->patch($data = array());

##### $this->curl->options($data = array());

##### $this->curl->head($data = array());

##### $this->curl->custom(string $method, $data = array());

##### $this->curl->getError();

##### $this->curl->getBody();

##### $this->curl->getStatusCode();

##### $this->curl->getRequestHeaders();

##### $this->curl->getResponseHeaders();

##### $this->curl->jsonDecode($json, $assoc = false, $depth = 512, $options = 0)