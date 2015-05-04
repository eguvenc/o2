
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

### İstisnai Hataları Yakalamak

------

Uygulamanıza özgü istisnai hataları yakalamak için <kbd>try/catch</kbd> bloğu kullanılır.

```php
try
{
	$this->db->transaction();

	$this->db->query("INSERT INTO users (name) VALUES('John')");

	$this->db->commit();

} catch(Exception $e)
{
	$this->db->rollBack();
    echo $e->getMessage();
}
```

Exception sınıfı <kbd>app/components.php</kbd> dosyasında komponent olarak konfigüre edilmiştir.


```php
/*
|--------------------------------------------------------------------------
| Exception
|--------------------------------------------------------------------------
*/
$c['exception'] = function () {
    return new Obullo\Error\Exception;
};
```

### Http Hataları Göndermek

Kimi durumlarda uygulamaya özgü http hataları göstermeniz gerekebilir bu durumda Http paketi içerisinden response sınıfına ait metotları uygulamanızda kullanabilirsiniz.

##### $this->response->showError('message')

```php
$this->response->status(500)->showError('There is an error occured');
```

Bu fonksiyon <kbd>app/templates/errors/general.php</kbd> hata şablonunu kullanarak özel hata mesajları gösterir. Hata dosyasını ihtiyaçlarınıza göre özelleştirebilirsiniz. Opsiyonal parametre <kbd>$status</kbd> ise hata ile birlikte hangi HTTP durum kodunun gönderileceğini belirler varsayılan değer <b>500 iç sunucu hatası</b> dır.


##### $this->response->show404('message')

```php
$this->response->show404('Page not found')
```

Bu fonksiyon <kbd>app/templates/errors/404.php</kbd> hata şablonunu kullanarak 404 http durum kodu ile birlikte sayfa bulunamadı hatası gösterir. Hata dosyasını ihtiyaçlarınıza göre özelleştirebilirsiniz.