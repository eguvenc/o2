
## ReCAPTCHA Sınıfı

-------

ReCAPTCHA google şirketi tarafından geliştirilen popüler bir captcha servisidir. ReCaptcha servisini kurmak için önce <a href="https://www.google.com/recaptcha/intro/index.html" target="_blank">bu sayfayı</a> ziyaret ederek site key ve secret key bilgilerinizi almanız gerekir.

### Kurulum

```php
php task module add recaptcha
```

### Kaldırma

```php
php task module remove recaptcha
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
use Obullo\Service\ServiceInterface;
use Obullo\Captcha\Adapter\ReCaptcha as ReCaptchaClass;

class Recaptcha implements ServiceInterface
{
    public function register(Container $c)
    {
        $c['recaptcha'] = function () use ($c) {
            $captcha = new ReCaptchaClass($c);            
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