
## Curl Sınıfı


Çerez, herhangi bir internet sitesi tarafından son kullanıcının bilgisayarına bırakılan bir tür tanımlama dosyasıdır. Çerez dosyalarında oturum bilgileri ve benzeri veriler saklanır. Çerez kullanan bir siteyi ziyaret ettiğinizde, bu site tarayıcınıza bir ya da birden fazla çerez bırakma konusunda talep gönderebilir.
> **Not:** Bir çereze kayıt edilebilecek maksimum veri 4KB tır.

<ul>
    <li><a href="#loading-class">Sınıfı Yüklemek</a></li>
    <li>
        <a href="#setcookie">Bir Çereze Veri Kaydetmek</a>
        <ul>
            <li><a href="#arrays">Array Yöntemi</a></li>
            <li><a href="#method-chaining">Zincirleme Method Yöntemi</a></li>
        </ul>
    </li>
    <li><a href="#parameters">Parametre Açıklamaları</a></li>
    <li><a href="#readcookie">Bir Çerez Verisini Okumak</a></li>
    <li><a href="#removecookie">Bir Çerezi Silmek</a></li>
    <li><a href="#queue">Çerezleri Kuyruğa Göndermek</a></li>
    <li><a href="#method-reference">Fonksiyon Referansı</a></li>
</ul>

<a name="loading-class"></a>

#### Sınıfı Yüklemek

```php
$this->c['cookie']->method();
```

<a name="method-reference"></a>

#### Fonksiyon Referansı

-------

##### $this->curl->