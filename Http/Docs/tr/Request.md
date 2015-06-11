
## Request Class

Http request sınıfı gelen istek türü, bağlantının güvenli olup olmadığı, ip adresi, ajax istekleri ve buna benzer sunucuda dinamik oluşan bilgilere ulaşmanızı sağlar. Bunun yanında süper küresel değişkenlerden elde edilen girdileri [filters](/Filters/Docs/tr/Filters.md) paketi yardımı ile opsiyonel doğrulama ve filtreleme işlevlerinden geçirerek güvenilir girdiler elde etmenizi yardımcı olur.

<ul>
    <li><a href="#loading-class">Sınıfı Yüklemek</a></li>
    <li>
    	<a href="#super-globals">Girdi Değerlerine Erişmek</a>
    	<ul>
    		<li><a href="#sget">$this->request->get()</a></li>
    		<li><a href="#spost">$this->request->post()</a></li>
    		<li><a href="#sall">$this->request->all()</a></li>
    		<li><a href="#sserver">$this->request->server()</a></li>
    		<li><a href="#smethod">$this->request->method()</a></li>
    		<li><a href="#sip">$this->request->getIpAddress()</a></li>
    		<li><a href="#sheaders-get">$this->request->headers->get()</a></li>
    		<li><a href="#sheaders-all">$this->request->headers->all()</a></li>
    	</ul>
    </li>

    <li>
    	<a href="#input-filters">Girdi Doğrulama / Filtreleme</a>
    	<ul>
    		<li><a href="#re-get">$this->request->get()</a></li>
    		<li><a href="#re-post">$this->request->post()</a></li>
			<li><a href="#isValidIp">$this->request->isValidIp()</a></li>
    	</ul>
    </li>

    <li>
    	<a href="#filtering-http-requests">Http İsteklerini Filtrelemek</a>
    	<ul>
    		<li><a href="#isCLi">$this->request->isCli()</a></li>
    		<li><a href="#isAjax">$this->request->isAjax()</a></li>
    		<li><a href="#isSecure">$this->request->isSecure()</a></li>
    		<li><a href="#isLayer">$this->request->isLayer()</a></li>
    	</ul>
    </li>

    <li>
    	<a href="#filtering-http-methods">Http Metodunu Filtrelemek</a>
    	<ul>
    		<li><a href="#isPost">$this->request->isPost()</a></li>
    		<li><a href="#isGet">$this->request->isGet()</a></li>
    		<li><a href="#isPut">$this->request->isPut()</a></li>
    		<li><a href="#isDelete">$this->request->isDelete()</a></li>
    		<li><a href="#isHead">$this->request->isHead()</a></li>
    		<li><a href="#isOption">$this->request->isOption()</a></li>
    		<li><a href="#isPatch">$this->request->isPatch()</a></li>
    		<li><a href="#isMethod">$this->request->isMethod()</a></li>
    	</ul>
    </li>
</ul>

<a name="loading-class"></a>

### Sınıfı Yüklemek

```php
$this->c['request']->method();
```

Request sınıfı <kbd>app/components.php</kbd> dosyasında tanımlı olduğundan çağrıldığında conteiner sınıfı içerisinden yüklenir.

<a name="super-globals"></a>

### Girdi Değerlerine Erişmek

<a name="sget"></a>

##### $this->request->get(mixed $key, $filter = '')

<kbd>$_GET</kbd> süper küresel değişkeninden değerler almanıza yardımcı olur. Değişkenlere direkt olarak $_GET['variable'] şeklinde ulaşmak yerine get fonksiyonu kullanmanın ana avantajı değişkenin varlığını kontrol etme durumunda isset() fonksiyonu kullanımı ortadan kaldırmasıdır. Eğer girdi $_GET küresel değişkeni içerisinde yoksa <kbd>false (boolean)</kbd> değerine var ise kendi değerine geri döner. 

Normal şartlarda isset($_GET['variable']) bloğunu her seferinde yazmamak için aşağıdaki gibi request sınıfı get metodu kullanmanız tavsiye edilir.

```php
if ($variable = $this->request->get('variable')) {
	echo $variable;
}
```

Eğer tüm get değerlerine ulaşmak isteseydik ilk parametreye true değerini göndermeliydik.

```php
$GET = $this->request->get(true);
print_r($GET);
```

<a name="spost"></a>

##### $this->request->post(mixed $key, $filter = '')

<kbd>$_POST</kbd> süper küresel değişkeninden değerler almanıza yardımcı olur.

```php
if ($variable = $this->request->post('variable')) {
	echo $variable;
}
```

<a name="sall"></a>

##### $this->request->all(mixed $key, $filter = '')

<kbd>$_REQUEST</kbd> süper küresel değişkeninden değerler almanıza yardımcı olur.

```php
if ($variable = $this->request->all('variable')) {
	echo $variable;
}
```

<a name="sserver"></a>

##### $this->request->server(mixed $key)

<kbd>$_SERVER</kbd> süper küresel değişkeninden değerler almanıza yardımcı olur. Eğer girilen anahtar $_SERVER küresel değişkeni içerisinde mevcut değilse fonksiyon <b>null</b> değerine döner.

```php
if ($variable = $this->request->server('variable')) {
	echo $variable;
}
```

<a name="smethod"></a>

##### $this->request->method()

Php <kbd>$_SERVER['REQUEST_METHOD']</kbd> değerine ger döner.

```php
$this->request->method();  // GET
```

<a name="sip"></a>

##### $this->request->getIpAddress()

```php
echo $this->request->getIpAddress(); // 88.54.844.15
```

<a name="sheaders-get"></a>

##### $this->request->headers->get($key)

Seçilen http sunucu başlığına geri döner.

```php
echo $this->request->headers->get('host'); // demo_blog
echo $this->request->headers->get('content-type'); // gzip, deflate
```

<a name="sheaders-all"></a>

##### $this->request->headers->all()

Tüm http sunucu başlıklarına geri döner.

```php
$headers = $this->request->headers->all();
print_r($headers);
```

```php
Array
(
    [Host] => framework
    [User-Agent] => Mozilla/5.0 (X11; Ubuntu; Linux x86_64; rv:38.0) Gecko/20100101 Firefox/38.0
    [Accept] => text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8
    [Accept-Language] => en-US,en;q=0.5
    [Accept-Encoding] => gzip, deflate
    ...
)
```

<a name="input-filters"></a>

### Girdi Doğrulama / Filtreleme

Request sınıfındaki get, post ve all metotlarına ikinci parametre olarak filtre ismi girilerek [Filters](/Filters/Docs/tr/Filters.md) paketine ait filtreler çalıştırılabilir. 

<a name="re-get"></a>

##### $this->request->get($key, $filter = null);

Doğrulama filtresi

```php
if ($variable = $this->request->get('variable', 'is')->int()) {
	echo 'variable is integer';
}
```
Temizleme filtresi

```php
$cleanVariable = $this->request->get('variable', 'clean')->int();

echo $cleanVariable;
```

> **Not:** Daha fazla filtreleme örnekleri için filters paketine ait [Filters.md](/Filters/Docs/tr/Filters.md) sayfasını ziyaret edin.

<a name="re-post"></a>

##### $this->request->post($key, $filter = null);

Doğrulama filtresi

```php
if ($variable = $this->request->get('variable', 'is')->str()) {
	echo 'variable is string';
}
```
Temizleme filtresi

```php
$cleanVariable = $this->request->get('variable', 'clean')->str('strip_high');
echo $cleanVariable;
```

<a name="isValidIp"></a>

##### $this->request->isValidIp($ip);

Girilen ip adresi doğru ise true değerine aksi durumda false değerine geri döner.

> **Not:** $this->request->getIpAddress() fonksiyonu ip doğrulama işlevini kendi içerisinde zaten yapar.

```php
if ( ! $this->request->isValidIp($ip)) {
	echo 'Not Valid';
} else {
	echo 'Valid';
}
```

<a name="filtering-http-requests"></a>

### Http İsteklerini Filtrelemek

Http isteklerini metotlar yardımı ile filtelenebilir.

<a name="isCli"></a>

##### $this->request->isCli();

Uygulama eğer bir konsol arayüzünden çalışıyorsa true değerine aksi durumda false değerine geri döner.

<a name="isLayer"></a>

##### $this->request->isLayer();

Uygulama gelen istek eğer katman ( Layer diğer bir adıyla Hmvc ) isteği ise true değerine aksi durumda false değerine geri döner.

<a name="isAjax"></a>

##### $this->request->isAjax();

Uygulama gelen istek eğer xmlHttpRequest ( Ajax ) isteği ise true değerine aksi durumda false değerine geri döner.

<a name="isSecure"></a>

##### $this->request->isSecure();

Uygulamaya gelen istek eğer https protokülünden geliyorsa true aksi durumda false değerine geri döner.


<a name="filtering-http-methods"></a>

#### Http Metodunu Filtrelemek

Uygulamanıza gelen http istekleri metot türüne göre filtrelenebilir.

<a name="isPost"></a>

##### $this->request->isPost();

Eğer http metodu <kbd>POST</kbd> değerinde geliyorsa true değerine aksi durumda false değerine döner.

<a name="isGet"></a>

##### $this->request->isGet();

Eğer http metodu <kbd>GET</kbd> değerinde geliyorsa true değerine aksi durumda false değerine döner.

<a name="isPut"></a>

##### $this->request->isPut();

Eğer http metodu <kbd>PUT</kbd> değerinde geliyorsa true değerine aksi durumda false değerine döner.

<a name="isDelete"></a>

##### $this->request->isDelete();

Eğer http metodu <kbd>DELETE</kbd> değerinde geliyorsa true değerine aksi durumda false değerine döner.

<a name="isOptions"></a>

##### $this->request->isOptions();

Eğer http metodu <kbd>OPTIONS</kbd> değerinde geliyorsa true değerine aksi durumda false değerine döner.

<a name="isPatch"></a>

##### $this->request->isPatch();

Eğer http metodu <kbd>PATCH</kbd> değerinde geliyorsa true değerine aksi durumda false değerine döner.

<a name="isHead"></a>

##### $this->request->isHead();

Eğer http metodu <kbd>HEAD</kbd> değerinde geliyorsa true değerine aksi durumda false değerine döner.

<a name="isMethod"></a>

##### $this->request->isMethod($method);

Eğer http metodu sizin belirlediğiniz <kbd>ÖZEL</kbd> metot türüne eşitse true değerine aksi durumda false değerine döner.

```php
if ($this->request->isMethod('COPY')) {

}
```