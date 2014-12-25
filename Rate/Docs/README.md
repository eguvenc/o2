
## Rate Limiter Class

Rate limiter sınıfı gelen http isteklerini kullanıcı işlemlerine göre değerlendirip bir zaman damgası ile bu istekleri <b>saniyelik</b>, <b>saatlik</b> ve <b>günlük</b> olarak sınırlamamızı sağlar.

Rate limiter uygulama içerisinde çalışan yüksek güvenlik gerektiren bölümlerin kötü niyetli kullanıcıların çoklu istek yaparak servisi devre dışı bırakmaları veya servis için bir tehdit oluşturmalarını engellemek amacıyla geliştirilmiş bir sınıftır.

Bu sınıfın çalışma felsefesi şöyledir:  Dışarıdan gelen kullanıcılar başarılı işlem yaptıklarında kullanıcının kredisi yani istek yapma limiti arttırılır, tam tersine her başarısız işlemde ise bu limit azaltılarak kullanıcının istek yapma limiti işlemlere göre sınırlandırılır. İşlem yapma kredisi <b>0</b> olan kullanıcılar istek limitine takılarak belirli bir süre sistem tarafından engellenirler. Bu süreler ve istek limitleri <kbd>app/config/shared/rate.php</kbd> config dosyası ile belirlenmektedir.

------


### Cache Storage

Rate limiter class uses service cache object like a database. The following picture shown an example user request and configuration data stored in redis.

![PhpRedisAdmin](/Rate/Docs/images/redis.png?raw=true "PhpRedisAdmin")


### Initializing the Class

```php
<?php
$this->c->load('rate/limiter as limiter');
$this->limiter->load($identifier, $params = array());
$this->limiter->identifier->method();
```

**Note:** Eğer ikinci parametreden array formatında config verisi girilmez ise rate limiter sınıfı <kbd>app/config/shared/rate.php</kbd> dosyasından varsayılan konfigurasyonları yükler ve static veri olarak cache e yazar. <b>Rate_Limiter_Config</b> isimli key cache den silinmediği sürece artık varsayılan ayarlar cache üzerinden okunur.

### Identifiers

-----

Tamımlayıcılar ( Identifiers ) gelen http isteklerini ayrıştırıp kaydetmek için <b>anahtar</b> olarak kullanılırlar. Rate limiter ile gelen http isteiğini sınırlandırmadan önce mutlaka aşağıdaki 
tanımlayıcılardan birini seçmeniz gerekmektedir. Tanımlayıcı <b>load</b> komutu ile seçilir ve böylece ona ait konfigurasyon değerleri config dosyasından yüklenmiş olur. Her tanımlayıcı yeni bir rate limiter <b>nesnesi</b> ona air bir <b>konfigurasyon nesnesi</b> oluşturur.

<table>
    <thead>
        <tr>
            <th>Key</th>    
            <th>Description</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td><b>ip</b></td>
            <td>Kullanıcının ip adresini anahtar olarak kabul eden tanımlayıcıdır.</td>
        </tr>
        <tr>
            <td><b>username</b></td>
            <td>Kullanıcının id değerini ( email, username, account_id ) anahtar olarak kabul eden tanımlayıcıdır.</td>
        </tr>
        <tr>
            <td><b>mobile</b></td>
            <td>Kullanıcının gsm numarasını anahtar olarak kabul eden tanımlayıcıdır.</td>
        </tr>
    </tbody>
</table>

**Note:** Config dosyasına daha çok tanımlayıcı ekleyebilir ve çıkarabilirsiniz. Her tanımlayıcının config ayarları farklı olarak tanımlanmaktadır.


Aşağıda <b>ip</b> tanımlayıcısına ait örnek bir kullanım gösteriliyor.


```php
<?php
$this->c->load('rate/limiter as limiter');

$this->limiter->load('ip');                                     // load ip configuration
$this->limiter->ip->channel('login');                           // create a login channel
$this->limiter->ip->identifier($this->request->getIpAddress()); // set user ip

if ($this->limiter->ip->isAllowed()) {

    // ..
}
```


### Configuration

-----

Her bir tanımlayıcının ( Identifier ) ın kendine ait bir konfigurasyonu vardır, bu konfigurasyonlar aynı şekilde cache servisinde de kendi başlıkları altında ayrı olarak tutulurlar. Örneğin bir işlemde
<b>ip</b> ve </b>username</b> e göre ayrı olarak bir sınırlama getirildiyse bu tanımlayıcıların ( Identifiers ) cache tablosu üzerindeki verileri de ayrı olarak tutulurlar.

Böylece ip, username, mobile yada sizin belilediğiniz tanımlayıcılar birbirinden bağımsız çalışcakları için çalışma esnasında herhangi bir karışıklık olmayacaktır. Karışıklığın mümkün olduğunca olmaması için <b>$this->limiter->identifier->channel('name');</b>  metodu kullanarak rate limiter i bölümlere ayırabilirsiniz.

```php
<?php
/*
|--------------------------------------------------------------------------
| Rate Limiter
|--------------------------------------------------------------------------
| Configuration file
|
*/
return array(

    'ip' => array(

        'limit' => array(
            'interval' => array('amount' => 300, 'maxRequest' => 7),  // 300 seconds / 7 times
            'hourly' => array('amount' => 1, 'maxRequest' => 15),     // 1 hour / 15 times
            'daily' => array('amount' => 1, 'maxRequest' => 50),      // 1 day / 50 times
        ),
        'ban' => array(
            'status' => 1,          // If ban status disabled don't do ban
            'expiration' => 86400,  // If ban status enabled wait for this time
        )
    ),

    'username' => array(

        'limit' => array(
            'interval' => array('amount' => 300, 'maxRequest' => 7),
            'hourly' => array('amount' => 1, 'maxRequest' => 15),
            'daily' => array('amount' => 1, 'maxRequest' => 50),
        ),
        'ban' => array(
            'status' => 1,
            'expiration' => 86400,
        )
    ),

    // ..
);

/* End of file rate.php */
/* Location: .app/config/shared/rate.php */
```

### Basic Usage

-----

```php
<?php
$this->c->load('rate/limiter as limiter');

$this->limiter->load('username');        
$this->limiter->username->channel('login');
$this->limiter->username->identifier('user@example.com');

if ($this->limiter->username->isAllowed()) {
    
    echo 'Allowed User !';

    $fail = true; // If some operation failed ?

    if ($fail) {
        $this->limiter->username->reduce(); // Reduce user limits if operation fail ( e.g. login attempt )
    } else {
        $this->limiter->username->increase(); // Increase user limits if operation success.
    }
}

if ($this->limiter->username->isBanned()) {
    echo 'Maximum request limit reached user is banned !';
}
```

### Override to Settings

Bazen bası konfigurasyonları dinamik olarak değiştirmek isteyebiliriz. Aşağıdaki örnekte ip tanımlayıcısının ban ayarlarını dinamik olarak değiştiriyoruz.

```php
<?php
$this->limiter->username->config->setBanStatus(0);        // Disable ban
$this->limiter->username->config->setBanExpiration(100);  // Update ban expiration time
```

### Sending Dynamic Parameters

Sometimes you may want to save config variables to the database. Using second parameter dynamically you can override to default parameters.

```php
<?php
$this->limiter->load('username', $this->cache->get('RATE_CONFIG_LOGIN_USERNAME'));
```

### What does do the isAllowed() function ?

-----

Rate limiter içerisindeki çalışma sürecinin başlatılması bu fonksiyon sayesinde olur. <b>$this->limiter->identifier->isAllowed()</b> metodu çalıştırıldığında aşağıdaki işlemler sırasıya gerçekleşir.

* Fonksiyon çalıştığında seçilen kanala (channel) ve tanımlayıcıya (identifier) ait konfigurasyon dosyası ve kullanıcıya ait requestler cache servisi üzerinden çağrılır.
* Konfigürasyondaki <b>enabled</b> değeri kontrol edilierek rate limiter servisi aktifse normal işlemler devam eder değilse her durumda fonksiyon <b>true</b> değerine döner.
* Kulanıcı <b>ban</b> durumda ise fonksiyon false değerinde döner ve bu durum <b>log</b> lara kaydedilir.
* <b>Günlük</b> limit kontrolü yapılır
* <b>Saatlik</b> limit kontrolü yapılır
* <b>Saniyelik</b> limit kontrolü yapılır
* Yukarıdaki limit kontrollerinden kullanıcı geçemez ise sistem kullanıcıyı banlanmış olarak işaretler ve fonksiyon <b>false</b> değerine döner.
* Limiti dolan kullanıcı, tekrar istek yapabilmek için konfigurasyon dosyasındaki sürenin dolmasını beklemek zorundadır. Bu yüzden son istek zamanıyla tanımlanan süre karşılaştırılır.
* Kullanıcı yukarıdaki işlemlerin hiçbirine takılmaz ise fonksiyon <b>true</b> değerine döner ve kullanıcı limit sınırlamasınna takılmaz.

#### Increasing Limits for Successful Operations

Limite takılmayan ve işlemlerini sürekli başarılı gerçekleştiren kullanıcılar limitleri yükseltilerek ödüllendirilirler. Başarısız işlemlerin fazla olması durumunda limitler düşürülmelidir.

```php
<?php

if ($this->limiter->ip->isAllowed()) {
    
    $somethingWrong = false;
    if ($somethingWrong) {
	   $this->limiter->ip->reduce();
    } else {
        $this->limiter->ip->increase();
    }

} else {

    echo 'Maximum request limit reached !';
}
```

### Enable / Disable Ban Feature

-----

Eğer kullanıcı banlama özelliği kapatılmak isteniyorsa bu değer false olarak set edilmelidir. Bu özellik varsayılan olarak true (açık) konumdadır.

```php
<?php
$this->limiter->load('username');        
$this->limiter->username->channel('login');
$this->limiter->username->identifier('user@example.com');

$this->limiter->username->config->setBanStatus(false);
$this->limiter->username->config->setBanExpiration(300); // sec
$this->limiter->username->config->save();
```

#### Removing Ban

```php
<?php
$this->limiter->identifier->removeBan();
```

### Function Reference

-----


#### $this->limiter->identifier->channel(string $name);

Sets a channel name to the current request.

#### $this->limiter->identifier->identifier(string $id);

Sets identifier value to the current request.

#### $this->limiter->identifier->label(string $name);

Sets a name to the current requests.

#### $this->limiter->identifier->isAllowed();

Executes rate limiter for current request, returns to true if user allowed otherwise false.

#### $this->limiter->identifier->increase();

Increase user max request limit.

#### $this->limiter->identifier->reduce();

Reduce user max request limit.

#### $this->limiter->identifier->getLabel();

Returns to request label.

#### $this->limiter->identifier->isBanned();

Returns to true if user is banned otherwise false.

#### $this->limiter->identifier->removeBan();

Removes ban data from cache.

#### $this->limiter->identifier->addBan();

If you want to additionaly ban operation you can use it by manually.


### Configuration Reference

-----

#### $this->limiter->identifier->config->setBanStatus(true);

Enable / Disabled ban feature. Default is true.

#### $this->limiter->identifier->config->setBanExpiration();

Sets ban expiration time into cache.

#### $this->limiter->identifier->config->getBanStatus();

Returns to current ban status.

#### $this->limiter->identifier->config->getBanExpiration();

Returns to current ban expiration time.
