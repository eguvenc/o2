
## Dipnotlar ( Annotations )

------

Bir dipnot aslında bir metadata yı (örneğin yorum,  açıklama, tanıtım biçimini) yazıya, resime veya diğer veri türlerine tutturmaktır.Dipnotlar genellikle orjinal bir verinin belirli bir bölümümü refere ederler.

> **Not:** Dipnotarı kullanmak herhangi bir kurulum yapmayı gerektirmez ve uygulamanıza performans açısından ek bir yük getirmez. Php ReflectionClass sınıfı ile okunan dipnotlar çekirdekte herhangi bir düzenli ifade işlemi kullanılmadan kolayca çözümlenir.

Şu anki sürümde biz dipnotları sadece <b>Http Katmanlarını</b> atamak ve <b>Event</b> sınıfına tayin edilen <b>Olayları Dinlemek</b> için kullanıyoruz.

### Mevcut olan dipnotlar

<table>
    <thead>
        <tr>
            <th>Dipnot</th>    
            <th>Açıklama</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td><b>@middleware->add();</b></td>
            <td>Bir middleware katmanını uygulamaya ekler. Virgül ile birden fazla katman ismi gönderebilirsiniz.</td>
        </tr>
        <tr>
            <td><b>@middleware->remove();</b></td>
            <td>Varolan bir middleware katmanını uygulamadan çıkarır. Virgül ile birden fazla katman ismi gönderebilirsiniz.</td>
        </tr>
        <tr>
            <td><b>@middleware->method();</b></td>
            <td>Http protokolü tarafından gönderilen istek metodu belirlenen metotlardan biri ile eşleşmez ise sayfaya erişime izin verilmez. Virgül ile birden fazla katman ismi gönderebilirsiniz.</td>
        </tr>
         <tr>
            <td><b>@middleware->when()->add()</b></td>
            <td>Katmanı koşullu olarak uygulamaya ekler. Eğer http protokolü tarafından gönderilen istek metodu when metodu içerisine yazılan metotlardan biri ile eşleşmez ise bu dipnotun kullanıldığı katman uygulumaya eklenmez.</td>
        </tr>
        <tr>
            <td><b>@event->subscribe();</b></td>
            <td>Event sınıfını çağırarak subscribe metodu ile varsayılan controller için bir dinleyici atamanızı sağlar.</td>
        </tr>
    </tbody>
</table>

### Controller için dipnotları aktif etmek

Config.php konfigürasyon dosyasını açın ve annotations enabled anahtarının değerini <b>true</b> olarak güncelleyin.

```php
'annotations' => array(
    'enabled' => true,
)
```

Artık controller sınıfı metotları üzerinde dipnotları aşağıdaki gibi kullanabilirsiniz.

```php
/**
 * Index
 *
 * @middleware->when("get", "post")->add("Example")
 * 
 * @return void
 */
public function index()
{
    // ..
}


/* End of file welcome.php */
/* Location: .modules/welcome/welcome.php */
```

Aşağıdaki örneklere bir göz atın.


#### Örnekler

```php
/**
 * Index
 *
 * @middleware->add("Example");
 * @middleware->method("get", "post");
 *
 * @return void
 */
```

Yukarıdaki örnek Controller sınıfı index ( middleware call ) metodundan önce <b>Example</b> katmanını çalıştırır ve sadece <b>get</b> ve <b>post</b> isteklerinde erişime izin verir.

```php
/**
 * Index
 *
 * @middleware->when("post")->add("XssFilter");
 * 
 * @return void
 */
```

Yukarıdaki örnek sadece http <b>post</b> isteklerinde ve index() metodunun çalışmasından önce tanımlamış olduğunuz <b>XssFilter</b> gibi örnek bir katmanı çalıştırır.


```php
/**
 * Index
 *
 * @middleware->when("post")->remove("Csrf");
 *
 * @return void
 */
```

Yukarıdaki örnek sadece http <b>post</b> ve <b>get</b> isteklerinde index() metodunun çalışmasından önce varolan <b>Csrf</b> katmanını uygulamadan siler.


```php
/**
 * Index
 *
 * @event->subscribe('Event\Login\Attempt');
 *
 * @return void
 */
```

Bu örnekte ise bu dipnotun yazıldığı Controller sınıfına ait index metodu çalıştığında <kbd>@event->subscribe</kbd> dipnotu arkaplanda <kbd>\Obullo\Event->subscribe()</kbd> metodunu çalıştırır ve uygulama  <kbd>app/classes/Event/Login/Attemp.php</kbd> sınıfı içerisine tanımlanmış olayları dinlemeye başlar.

> **Not:** Olaylar ( Events ) hakkında daha detaylı bilgiye Event paketi dökümentasyonundan ulaşabilirsiniz.


#### Bir katmanı tüm sınıf metotlarında geçerli kılmak

Bazı durumlarda yüklenen controller ın tüm metodlarında geçerli olabilecek bir filtreye ihtiyaç duyulabilir. Bu durumda filtreleri <b>load</b> metodu üzerine yazmanız yeterli olacaktır.

```php
/**
 * Loader
 *
 * @middleware->method("post","get");
 * 
 * @return void
 */
public function load()
{
    // ..
}
```