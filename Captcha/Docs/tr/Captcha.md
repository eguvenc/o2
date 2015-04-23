
## Captcha Sınıfı

------

CAPTCHA Carnegie Mellon School of Computer Science tarafından geliştirilen bir projedir. Projenin amacı bilgisayar ile insanların davranışlarının ayırt edilmesidir ve daha çok bu ayrımı yapmanın en zor olduğu web ortamında kullanılmaktadır.

CAPTCHA projesinin bazı uygulamalarına çoğu web sayfalarında rastlamak mümkündür. Üyelik formlarında rastgele resim gösterilerek formu dolduran kişiden bu resmin üzerinde yazan sözcüğü girmesi istenir. Buradaki basit mantık o resimde insan tarafından okunabilecek ancak bilgisayar programları tarafından okunması zor olan bir sözcük oluşturmaktır. Eğer forma girilen sözcük resimdeki ile aynı değilse ya formu dolduran kişi yanlış yapmıştır ya da formu dolduran bir programdır denebilir.

O2 captcha sınıfı captcha resimlerini oluşturmanıza yardımcı olan bir kütüphanedir. 

### Servisi Yüklemek

------

Captcha servisi aracılığı ile captcha metotlarına aşağıdaki gibi erişilebilir.

```php
$this->c['captcha']->method();
```

## Image Sınıfı

-------

Uygulamalarınıza özgü captcha hazırlayabilmeniz için captcha kodlarını belirlenen bileşenler ile ( yazı fontu, renkler, arka plan ) tarayıcı önbelleğinde geçici resim olarak oluşturmanızı sağlayan captcha türüdür.

### Kurulum

```php
php task module add --name=captcha
```

### Kaldırma

```php
php task module remove --name=captcha
```

### Konfigürasyon

Modül yüklendiğinde konfigürasyon dosyaları da <kbd>app/config/captcha</kbd> klasörü altına kopyalanmış olur. Konfigürasyon ayarlarını ihtiyaçlarınızı göre aşağıdaki dosyadan ayarlamanız gerekir.

```php
- app
- config
	- captcha
		image.php
```

### Örnek Dosyalar

Modül yaratıldığına örnek captcha oluşturma dosyaları <kbd>.modules/recaptcha/examples</kbd> dizini altına kopyalanır. Bu kapsamlı örnekleri incelemek için tarayıcınızdan aşğıdaki adresleri ziyaret edin.

```html
http://myproject/captcha/examples/form
http://myproject/captcha/examples/ajax
```

#### Servis Konfigürasyonu

Servis dosyası modül eklendiğinde otomatik olarak <kbd>app/classes/Service</kbd> klasörü altına kopyalanır. Servis dosyasındaki captcha özelliklerini ihtiyaçlarınıza göre konfigüre etmeniz gerekebilir.

```php
namespace Service;

use Obullo\Container\Container;
use Obullo\Captcha\Adapter\Image;
use Obullo\ServiceProviders\ServiceInterface;

class Captcha implements ServiceInterface
{
    public function register(Container $c)
    {
        $c['captcha'] = function () use ($c) {

            $captcha = new Image($c);            
            $captcha->setMod('secure');  // set to "cool" for no background
            $captcha->setPool('alpha');     // "random", "numbers"
            $captcha->setChar(5);
            $captcha->setFont(array('NightSkK','Almontew', 'Fordd'));
            $captcha->setFontSize(20);
            $captcha->excludeFont(['Fordd']);  // remove font
            $captcha->setHeight(36);
            $captcha->setWave(false);
            $captcha->setColor(['red', 'black']);
            $captcha->setTrueColor(false);
            $captcha->setNoiseColor(['red']);
            return $captcha;
        };
    }
}

// END Captcha service

/* End of file Captcha.php */
/* Location: .app/classes/Service/Captcha.php */
```

#### Mod Seçimi

Image sürücüsü iki tür moda sahiptir: <kbd>secure</kbd> ve <kbd>cool</kbd>. Konfigürasyonda tanımlı varsayılan mod <b>cool</b> modudur. Güvenli mod <b>secure</b> modu imajları kompleks bir arkaplan seçerek oluşturur. Cool modunda ise captcha arkaplan kullanılmadan oluşturulur.

```php
$this->captcha->setMod('secure');
```

#### Font Seçimi

```php
$this->captcha->setFont(['Arial', 'Tahoma', 'Verdana']);
```

#### Font Dizini

Fontlarınız <kbd>.assets/fonts</kbd> dizininden yüklenirler. Bu dizin konfigürasyon dosyasından değiştirilebilir.

```php
- assets
	- fonts
		My_font1.ttf
		My_font2.ttf
		My_font3.ttf
```

#### Özel Fontlar Eklemek

```php
$this->captcha->setFont([
                  'AlphaSmoke',         // Default captcha font
                  'Almontew',        
				  'My_Font1.ttf',		// Your custom fonts with extension (.ttf etc ..)
				  'My_Font2.ttf',
				  'My_Font3.ttf'
				 ]);
```

#### Gereksiz Fontları Çıkarmak

```php
$this->captcha->excludeFont(['AlphaSmoke','Anglican'});
```

#### Renk Seçimi

Varsayılan renkleri <b>app/config/captcha.php</b> dosyasından ayarlayabilirsiniz.

Mevcut renkler aşağıdaki gibidir.

<kbd>red</kbd> - <kbd>blue</kbd> - <kbd>green</kbd> - <kbd>black</kbd> - <kbd>yellow</kbd> 

```php
$this->captcha->setColor(['red','black']);
```

#### Arkaplan Desen Renkleri

Varsayılan renkleri <b>app/config/captcha.php</b> dosyasından ayarlayabilirsiniz. Birden fazla renk seçildiğinde captcha rastgele bir renk seçilerek yaratılır.

Mevcut renkler aşağıdaki gibidir.

<kbd>red</kbd> - <kbd>blue</kbd> - <kbd>green</kbd> - <kbd>black</kbd> - <kbd>yellow</kbd> 

```php
$this->captcha->setNoiseColor(['black','cyan']);
```

#### Imaj Yüksekliği

Eğer imaj <b>yüksekliği</b> bir kez ayarlanır ise imaj genişliği, karakter ve font genişliği değerleri otomatik olarak hesaplanır. Varsayılan değer <kbd>40</kbd> px dir.

```php
$this->captcha->setHeight(40);
```

#### Font Genişliği

Font size değerini atar, varsayılan değer <kbd>20</kbd> px dir.

```php
$this->captcha->setFontSize(20);
```

#### Font Eğimi

Font eğimi özelliği etkin kılar.

```php
$this->captcha->setWave(false);
```

#### Karakter Havuzu

Karakter havuzu captcha imajında kullanılacak karakterleri belirler, aşağıdaki listedeki değerler örnek olarak verilmiştir. Değerler konfigürasyon dosyanızdan değiştirilebilir.

```php
$this->captcha->setPool('numbers');
```

<table>
<thead>
<tr>
<th>Type</th>
<th>Description</th>
</tr>
</thead>
<tbody>
<tr>
<td>numbers</td>
<td>23456789</td>
</tr>
<tr>
<td>alpha</td>
<td>ABCDEFGHJKLMNPRSTUVWXYZ</td>
</tr>
<tr>
<tr>
<td>random</td>
<td>23456789ABCDEFGHJKLMNPRSTUVWXYZ</td>
</tr>
</tbody>
</table>

Daha fazla okunabilirlik için <kbd>"1 I 0 O"</kbd> karakterlerini kullanmamanız tavsiye edilir.

Varsayılan değer <kbd>random</kbd> değeridir.


#### Karakter Genişliği

```php
$this->captcha->setChar(10);
```

#### Image Controller Örneği

Captcha modülü eklendiğinde captcha modülü altında <kbd>/modules/captcha/Create.php</kbd> adında aşağıdaki gibi bir imaj controller yaratılır.

```php
namespace Captcha;

class Create extends \Controller
{
    public function load()
    {
        $this->c['captcha'];
    }

    public function index()
    {
        header('Cache-Control: no-cache, must-revalidate');
        header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');

        $this->captcha->create();
    }
}
```

#### $this->captcha->printJs();

Sayfaya captcha eklemek için aşağıdaki gibi <b>head</b> tagları arasına javascript çıktısını ekrana dökmeniz gerekir.

```php
<!DOCTYPE html>
<html>
<head>
    <title></title>
    <?php echo $this->captcha->printJs() ?>
</head>
<body>

</body>
</html>
```

#### $this->captcha->printHtml();

Formlarınıza captcha eklemek için aşağıdaki gibi captcha çıktısını ekrana dökmeniz gerekir.

```php
<form method="POST" action="/captcha/examples/form">
	<?php echo $this->captcha->printHtml() ?>
    <input type="submit" value="Send" name="sendForm">
</form>
```

#### $this->captcha->printRefreshButton();

Eğer refresh button özelliğinin etkin olmasını istiyorsanız. Form taglarınız içierisinde bu fonksiyonu kullanın.

```php
<form method="POST" action="/captcha/examples/form">
    <?php echo $this->captcha->printHtml() ?>
    <?php echo $this->captcha->printRefreshButton() ?>
    <input type="submit" value="Send" name="sendForm">
</form>
```

#### Doğrulama 

Captcha doğrulama için bütün sürücüler için ortak olarak kullanılan CaptchaResult sınıfı kullanılır. Bir captcha kodunun doğru olup olmadığı aşağıdaki gibi isValid() komutu ile anlaşılır.

```php
if ($this->c['captcha']->result()->isValid()) {

	// Doğrulama başarılı
}
```

Bir doğrulamadan dönen mesajlar aşağıdaki gibi alınır.

```php
print_r($this->c['captcha']->result()->getMessages());
```

Bir doğrulamaya ait hata kodu alma örneği


```php
echo $this->c['captcha']->result()->getCode();  // -2  ( Invalid Code )
```

#### Validator Sınıfı İle Doğrulama 

Eğer varolan formunuz içerisinde bir captcha doğrulaması yapıyorsanız ve konfigürasyon dosyasından <kbd>validation</kbd> ve <kbd>callback</kbd> anahtarları aktif ise doğrulama için aşağıdaki kodlar haricinde herhangi bir kod yazmanıza gerek kalmaz.

```php
namespace Captcha\Examples;

class Form extends \Controller
{
    public function load()
    {
        $this->c['url'];
        $this->c['form'];
        $this->c['view'];
        $this->c['captcha'];
    }

    public function index()
    {
        if ($this->request->isPost()) {

            if ($this->c['validator']->isValid()) {
                $this->form->success('Form Validation Success.');
            }
        }
        $this->view->load(
            'form',
            [
                'title' => 'Hello Captcha !'
            ]
        );
    }
}
```

#### Hata ve Sonuç Kodları Tablosu

<table>
    <thead>
        <tr>
            <th>Kod</th>    
            <th>Sabit</th>    
            <th>Açıklama</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td>0</td>
            <td>CaptchaResult::FAILURE</td>
            <td>Genel başarısız doğrulama.</td>
        </tr>
        <tr>
            <td>1</td>
            <td>CaptchaResult::SUCCESS</td>
            <td>Doğrulama başarılıdır.</td>
        </tr>
        <tr>
            <td>-1</td>
            <td>CaptchaResult::FAILURE_EXPIRED</td>
            <td>Girilen captcha kodunun zaman aşımına uğradığını gösterir.</td>
        </tr>
        <tr>
            <td>-2</td>
            <td>CaptchaResult::FAILURE_INVALID_CODE</td>
            <td>Girilen captcha kodunun yanlış olduğunu gösterir.</td>
        </tr>
        <tr>
            <td>-3</td>
            <td>CaptchaResult::FAILURE_CAPTCHA_NOT_FOUND</td>
            <td>Girilen captcha kodunun veriler içerisinde hiç bulunamadığını gösterir.</td>
        </tr>
    </tbody>
</table>


#### Image Sınıfı Referansı

------

##### $this->captcha->setMod(string $mod = 'secure');

Captcha modunu ayarlar <kbd>secure</kbd> veya <kbd>cool</kbd> seçilebilir. Cool seçildiğinde arkaplan boşaltılır.

##### $this->captcha->setNoiseColor(mixed $color = ['red']);

Arkaplan desen renklerini belirler.

##### $this->captcha->setColor(mixed $color = ['black']);

Imaj yazı rengini belirler.

##### $this->captcha->setTrueColor(boolean $bool = false);

Image true color seçeneğini etkin kılar. Mevcut renklerin bir siyah versiyonunu yaratır. Bknz. Php <a href="http://php.net/manual/en/function.imagecreatetruecolor.php" target="_blank">imagecreatetruecolor</a>

##### $this->captcha->setFontSize(integer $size);

Font genişliği belirler.

##### $this->captcha->setHeight(integer $height);

Eğer imaj <b>yüksekliği</b> bir kez ayarlanır ise imaj genişliği, karakter ve font genişliği değerleri otomatik olarak hesaplanır.

##### $this->captcha->setPool(string $pool);

Karakter havuzunu belirler. Değerler: numbers, random ve alpha dır.

##### $this->captcha->setChar(integer $char);

Imaj üzerindeki karakterlerin maximum sayısını belirler.

##### $this->captcha->setWave(true or false);

Yazı eğimi özelliğini açar veya kapatır.

##### $this->captcha->setFont(mixed ['FontName', ..]);

Mevcut fontlardan font yada fontlar seçmenize olanak tanır.

##### $this->captcha->excludeFont(mixed ['FontName', ..]);

Mevcut fontlardan font yada fontlar çıkarmanızı sağlar.

##### $this->captcha->getInputName();

Captcha input alanı adını verir.

##### $this->captcha->getImageUrl();

Captcha http image adresini verir.

##### $this->captcha->getImageId();

Rastgele üretilen captcha imajı id sini verir.

##### $this->captcha->getCode();

Geçerli captcha koduna geri döner.

##### $this->captcha->create();

Captcha kodunu yaratır. Http başlıkları ile birlikte kullanılması tavsiye edilir.

##### $this->captcha->result(string $code = null);

Parametreden gönderilen captcha kodunu doğrulama işlemini başlatarak CaptchaResult nesnesine döner. Eğer bir parametre girilmezse otomatik olarak $this->c['request']->post('capthca_input_ismi'); değeri alınır.

##### $this->captcha->printJs();

Captcha refresh javascript fonksiyonunu sayfaya yazdırır. Html head tagları arasında kullanılması önerilir.

##### $this->captcha->printHtml();

Captcha html <b>img</b> tagını yaratır dönen sonuç "echo" ile ekrana yazdırılmalıdır.

##### $this->captcha->printRefreshButton();

Captcha html refresh button tagını yaratır.

## ReCAPTCHA Sınıfı

-------

ReCAPTCHA google şirketi tarafından geliştirilen popüler bir captcha servisidir. ReCaptcha servisini kurmak için önce <a href="https://www.google.com/recaptcha/intro/index.html" target="_blank">bu sayfayı</a> ziyaret ederek site key ve secret key bilgilerinizi almanız gerekir.

### Kurulum

```php
php task module add --name=recaptcha
```

### Kaldırma

```php
php task module remove --name=recaptcha
```

### Örnek Dosyalar

Modül yaratıldığına örnek recaptcha oluşturma dosyaları <kbd>.modules/recaptcha/examples</kbd> dizini altına kopyalanır. Bu kapsamlı örnekleri incelemek için tarayıcınızdan aşğıdaki adresleri ziyaret edin.

```html
http://myproject/recaptcha/examples/form
http://myproject/recaptcha/examples/ajax
```

#### Konfigürasyon

Modül yüklendiğinde konfigürasyon dosyaları da <kbd>app/config/recaptcha</kbd> klasörü altına kopyalanmış olur. Bu dosyadan <b>api.key.site</b> ve <b>api.key.secret</b> anahtarlarını reCaptcha api servisinden almış olduğunuz bilgiler ile doldurmanız  gerekir.

```php
return array(
    
    'locale' => [
        'lang' => 'en'                                             // Captcha language
    ],
    'api' => [
        'key' => [
            'site' => '6LcWtwUTAAAAACzJjC2NVhHipNPzCtjKa5tiE6tM',  // Api public site key
            'secret' => '6LcWtwUTAAAAAEwwpWdoBMT7dJcAPlborJ-QyW6C',// Api secret key
        ]
    ],
    'user' => [                                                    // Optional
        'autoSendIp' => false                                      // The end user's ip address.
    ],
    'form' => [                                                    // Captcha input configuration.
        'input' => [
            'attributes' => [
                'name' => 'recaptcha-validation',                  // Hidden input for validator
                'id' => 'recaptcha-validation',
                'type' => 'text',
                'value' => 1,
                'style' => 'display:none;',
            ]
        ],
        'validation' => [
            'enabled' => true,      // Whether to use validator package
            'callback' => true,     // Whether to build validator callback_captcha function
        ]
    ]
);

/* End of file recaptcha.php */
/* Location: .app/config/captcha/recaptcha.php */
```


#### Servis Konfigürasyonu

Servis dosyası modül eklendiğinde otomatik olarak <kbd>app/classes/Service</kbd> klasörü altına kopyalanır. Servis dosyasındaki reCaptcha özelliklerini ihtiyaçlarınıza göre konfigüre etmeniz gerekebilir.

```php
namespace Service;

use Obullo\Container\Container;
use Obullo\Captcha\Adapter\ReCaptcha;
use Obullo\ServiceProviders\ServiceInterface;

class Recaptcha implements ServiceInterface
{
    public function register(Container $c)
    {
        $c['recaptcha'] = function () use ($c) {

            $captcha = new ReCaptcha($c);            
            $captcha->setLang('en');
            return $captcha;
        };
    }
}

// END Recaptcha service

/* End of file Recaptcha.php */
/* Location: .app/classes/Service/Recaptcha.php */
```

#### $this->recaptcha->printJs();

Formlarınıza ReCAPTCHA eklemek için aşağıdaki gibi <b>head</b> tagları arasına javascript çıktısını ekrana dökmeniz gerekir.

```php
<!DOCTYPE html>
<html>
<head>
	<title></title>
	<?php echo $this->recaptcha->printJs() ?>
</head>
<body>

</body>
</html>
```

#### $this->recaptcha->printHtml();

ReCAPTCHA nın görüntülenmesi için aşağıdaki gibi captcha çıktıyı ekrana dökmeniz gerekir.

```php
<form method="POST" action="/captcha/examples/form">
	<?php echo $this->recaptcha->printHtml() ?>
    <input type="submit" value="Send" name="sendForm">
</form>
```



#### Doğrulama 

ReCaptcha doğrulama için bütün sürücüler için ortak olarak kullanılan CaptchaResult sınıfı kullanılır. Bir captcha kodunun doğru olup olmadığı aşağıdaki gibi isValid() komutu ile anlaşılır.

Bir doğrulamadan dönen mesajlar aşağıdaki gibi alınır.

```php
print_r($this->c['recaptcha']->result()->getMessages());
```

Bir doğrulamaya ait hata kodu alma örneği


```php
echo $this->c['recaptcha']->result()->getCode();  // -2  ( Invalid Code )
```

#### Validator Sınıfı İle Doğrulama 

Eğer varolan formunuz içerisinde bir captcha doğrulaması yapıyorsanız ve konfigürasyon dosyasından <kbd>validation</kbd> ve <kbd>callback</kbd> anahtarları aktif ise doğrulama için aşağıdaki kodlar haricinde herhangi bir kod yazmanıza gerek kalmaz.

```php
namespace ReCaptcha\Examples;

class Form extends \Controller
{
    public function load()
    {
        $this->c['url'];
        $this->c['form'];
        $this->c['view'];
        $this->c['recaptcha'];
    }

    public function index()
    {
        if ($this->request->isPost()) {

            if ($this->c['validator']->isValid()) {
                $this->form->success('Form Validation Success.');
            }
        }
        $this->view->load(
            'form',
            [
                'title' => 'Hello Captcha !'
            ]
        );
    }
}
```
#### Recaptcha Sınıfı Referansı

------

>**Not:** ReCaptcha hakkında ayrıntılı resmi dökümentasyona bu linkten <a href="https://developers.google.com/recaptcha/docs/display" target="_blank">https://developers.google.com/recaptcha/docs/display</a> ulaşabilirsiniz. 

##### $this->recaptcha->setLang(string $lang);

Servisin hangi dili desteklemesi gerektiğini belirler.

##### $this->recaptcha->setUserIp(string $ip);

Servise son kullanıcının ip adresini gönderilmesini sağlar.

##### $this->recaptcha->printJs();

Servise ait javascript tagını ekrana yazdırır. Html head tagları arasında kullanılması önerilir.

##### $this->recaptcha->printHtml();

Servise ait captcha elementinin html taglarını ekrana yazdırır. Html body tagları arasında kullanılması önerilir.

##### $this->recaptcha->setSiteKey(string $lang);

Varolan site key konfigurasyonunu dinamik olarak değiştirebilmenizi sağlar.

##### $this->recaptcha->setSecretKey(string $lang);

Varolan secret key konfigurasyonunu dinamik olarak değiştirebilmenizi sağlar.

##### $this->recaptcha->getUserIp();

Tanımlanan user ip adresini verir.

##### $this->recaptcha->getSiteKey();

Tanımlanmış site key konfigürasyonunu verir.

##### $this->recaptcha->getSecretKey();

Tanımlanmış secret key konfigürasyonunu verir.

##### $this->recaptcha->getLang();

Servise tanımlı dili verir.

##### $this->recaptcha->getInputName();

Validator sınıfın çalışabilmesi framework tarafından oluşturulan recaptcha elemetinin ismini verir.