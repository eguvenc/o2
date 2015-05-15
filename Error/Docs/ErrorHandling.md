
## Hata Yönetimi ( Error Handling )

------

Hata kontrolü ( hata raporlama ) uygulama ile tümleşik gelir ve <kbd>app/config/env/$env/config.php</kbd> dosyasından kontrol edilir.  Local çevre ortamında yada <b>error > debug</b> değeri true olduğunda tüm php hataları <dfn>set_exception_handler()</dfn> fonksiyonu ile Obullo\Error\Exception sınıfı içerisinden exception hatalarına dönüştürülür. Çevre ortamı <b>production</b> olarak ayarlandığında <b>log > enabled</b> anahtarı aktif ise log servisi tarafından hatalar log sürücülerine yazılır ve hatalar gösterilmez eğer <b>log > enabled</b> anahtarı konfigürasyon dosyasından aktif değilse doğal php hataları uygulamada görüntülenmeye müsait olur.


```php
return array(
      
    'error' => [
        'debug' => true,
    ],

```

Uygulamada <b>error > debug</b> değeri true olduğunda her arayüz ( Http istekleri, Ajax ve Cli istekleri ) için farklı türde hata çıktıları oluşturulur. Aşağıda Http İstekleri için oluşmuş örnek bir hata çıktısı görüyorsunuz.

![Http Errors](/Error/Docs/images/error-debug.png?raw=true "Http Errors")

### Evrensel Hata Yönetimi

Uygulamada evrensel hata yönetimi <kbd>app/errors.php</kbd> dosyasından kontrol edilir. Hata durumunda ne yapılacağı bir isimsiz fonksiyon tarafından belirlenerek uygulama tarafında php error handler fonksiyonlarına kayıt edilir. İsimsiz fonksiyon parametresi önüne istisnai hata tipine ait sınıf ismi yazılarak filtreleme yapılmalıdır. Aksi durumda her bir istisnai hata için bütün error fonksiyonları çalışacaktır.

#### Php Hataları ve İstisnai Hatalar

Aşağıdaki örnekte <b>istisnai hatalara</b> dönüştürülmüş <b>doğal php hataları</b> yakalanıp log olarak kaydediliyor.

```php
/*
|--------------------------------------------------------------------------
| Php Native Errors
|--------------------------------------------------------------------------
*/
$c['app']->error(
    function (ErrorException $e) use ($c) {
        $c['logger']->error($e);
        return ! $continue = false;   // Whether to continue native errors
    }
);
```

Error metodu içerisine girilen isimsiz fonksiyonu kendi ihtiyaçlarınıza göre özelleştirebilirsiniz. İsimsiz fonksiyonlar uygulama çalıştığında fonksiyon parametresi önüne yazılan istisnai hata tipine göre filtrelenir ve application sınıfı içerisinde <dfn>set_exception_handler()</dfn> fonksiyonu içerisine bir defalığına kayıt edilir. 

Bu örnekte fonksiyon sonucu <kbd>$continue</kbd> değişkenine döner ve bu değişken php hatalarının devam edilerek gösterilip gösterilmeyeceğine karar verir. Değişken değeri <b>true</b> olması durumunda hatalar gösterilmeye devam eder <b>false</b> durumda ise <b>fatal error</b> hataları hariç diğer hatalar gösterilmez.

```php
/*
|--------------------------------------------------------------------------
| Logic Exceptions
|--------------------------------------------------------------------------
*/
$c['app']->error(
    function (LogicException $e) use ($c) {
        $c['logger']->error($e);
    }
);
```

Eğer fonksiyon içerisindeki hatalar log sınıfı herhangi bir metodunun içerisine exception nesnesi olarak gönderilirse log sınıfı tarafından istisnai hata çözümlenerek log dosyalarına kayıt edilir.

#### İstisnai Hatalar Hiyerarşisi

Hataları yakalarken uygulamaya tüm exception isimleri yazmanıza <b>gerek yoktur</b>. Sadece en üst hiyerarşideki istisnai hata isimlerini girerek aynı kategorideki hataların hepsini yakalayabilirsiniz.


```php
- Exception
    - ErrorException
    - LogicException
        - BadFunctionCallException
            - BadMethodCallException
        - DomainException
        - InvalidArgumentException
        - LengthException
        - OutOfRangeException
    - RuntimeException
        - PDOException
        - OutOfBoundsException
        - OverflowException
        - RangeException
        - UnderflowException
        - UnexpectedValueException
```

İstisnai hatalar ile ilgili bu kaynağa bir gözatın. <a href="http://nitschinger.at/A-primer-on-PHP-exceptions">Php Exceptions</a>

#### Veritabanı ve Diğer İstisnai Hatalar Yönetimi

Uygulama hataları varsayılan olarak log sürücülerine kaydedilirler.

```php
/*
|--------------------------------------------------------------------------
| Database and Other Runtime Exceptions
|--------------------------------------------------------------------------
*/
$c['app']->error(
    function (RuntimeException $e) use ($c) {
        $c['logger']->error($e);
    }
);
```

Bununla beraber <a href="http://php.net/manual/tr/internals2.opcodes.instanceof.php" target="_blank">instanceof</a> yöntemi ile <b>exception</b> ( $e ) nesnesine  sınıf kontrolü yapılarak yönetilebilirler. Örneğin uygulamadan dönen veritabanı hatalarını yönetmek istiyorsanız aşağıdaki kod bloğu size yardımcı olabilir.


```php
$c['app']->error(
    function (RuntimeException $e) use ($c) {

        if ($e instanceof PDOException) {

            $this->c['translator']->load('database');

            echo $this->c['response']->status(200)->showError(
                $this->c['translator']['OBULLO:TRANSACTION:ERROR'],
                'System Unavailable'
            );
        }
        $c['logger']->error($e);
    }
);
```

#### Ölümcül Hatalar

Aşağıdaki örnekte ise php fatal error türündeki hatalar kontrol altına alınarak log sınıfına gönderiliyor.

```php
/*
|--------------------------------------------------------------------------
| Php Fatal Errors
|--------------------------------------------------------------------------
*/
$c['app']->fatal(
    function (ErrorException $e) use ($c) {
        $c['logger']->error($e);
    }
);
```

Fatal error örneğinde ölümcül hata türündeki hatalar fatal metodu ile php <a href="http://php.net/manual/en/function.register-shutdown-function.php" target="_blank">register_shutdown</a> fonksiyonuna gönderilerek kontrol edilirler. Bir ölümcül hata oluşması durumunda isimsiz fonksiyon çalışarak fonksiyon içerisindeki görevleri yerine getirir. Fatal error metodu uygulamanın en alt seviyesinde çalışır.


> **Not:** İstisnai hatalardan faklı olarak $c['app']->fatal() metodu errors.php dosyası içerisinde yalnızca <b>bir kere</b> tanımlanabilir.


### İstisnai Hataları Yakalamak

------

Uygulamanıza özgü istisnai hataları yakalamak için <kbd>try/catch</kbd> bloğu kullanılır.

```php
try
{
	$this->db->beginTransaction();
	$this->db->query("INSERT INTO users (name) VALUES('John')");
	$this->db->commit();

} catch(Exception $e)
{
	$this->db->rollBack();
    echo $e->getMessage();
}
```

### Özel Http Hataları Göndermek

Kimi durumlarda uygulamaya özgü http hataları göstermeniz gerekebilir bu durumda Http paketi içerisindeki response sınıfına ait metotları uygulamanızda kullanabilirsiniz.

##### $this->response->showError('message')

```php
$this->response->status(500)->showError('There is an error occured');
```

Bu fonksiyon <kbd>app/templates/errors/general.php</kbd> hata şablonunu kullanarak özel hata mesajları gösterir. Hata şablonunu ihtiyaçlarınıza göre özelleştirebilirsiniz. Opsiyonal parametre <kbd>$status</kbd> ise hata ile birlikte hangi HTTP durum kodunun gönderileceğini belirler varsayılan değer <b>500 iç sunucu hatası</b> dır.


##### $this->response->show404('message')

```php
$this->response->show404('Page not found')
```

Bu fonksiyon <kbd>app/templates/errors/404.php</kbd> hata şablonunu kullanarak 404 http durum kodu ile birlikte sayfa bulunamadı hatası gösterir. Hata şablonunu ihtiyaçlarınıza göre özelleştirebilirsiniz.
