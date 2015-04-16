
## Dipnotlar ( Annotations )

------

Bir dipnot aslında bir metadata yı (örneğin yorum,  açıklama, tanıtım biçimini) yazıya, resime veya diğer veri türlerine tutturmaktır.Dipnotlar genellikle orjinal bir verinin belirli bir bölümümü refere ederler. 

Şu anki sürümde biz dipnotları sadece filtreleri tuturmak için kullanıyoruz.

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
            <td><b>@middleware->assign("name");</b></td>
            <td>Bir middleware filtresini app->middleware metodu ile dinamik olarak uygulamaya ekler.</td>
        </tr>

        <tr>
            <td><b>@middleware->method("post","get");</b></td>
            <td>Http protokolü tarafından gönderilen istek metodu bu metot içerisine yazılan metotlardan biri ile eşleşmez ise bu dipnotun kullanıldığı controller a erişime izin verilmez.</td>
        </tr>
         <tr>
            <td><b>@middleware->when("post","get")->assign("name")</b></td>
            <td>Filtreyi çalıştırır eğer http protokolü tarafından gönderilen istek metodu when metodu içerisine yazılan metotlardan biri ile eşleşmez ise bu dipnotun kullanıldığı controller a erişime izin verilmez.</td>
        </tr>
        <tr>
            <td><b>@event->subscribe("Class");</b></td>
            <td>Event sınıfını çağırarak subscribe metodu ile varsayılan controller için dinleyici atamanızı sağlar.</td>
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
 * @middleware->when("get", "post")->assign("Activity")
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
 * @middleware->assign("Csrf");
 * @middleware->method("get", "post");
 *
 * @return void
 */
```

Controller sınıfı metodunun çalışma seviyesinden önce <b>csrf</b> filtresini çalıştırır ve sadece <b>get</b> ve <b>post</b> metotlarına erişime izin verir.

```php
/**
 * Index
 *
 * @middleware->when("post")->assign("Csrf");
 * 
 * @return void
 */
```

Sadece http <b>post</b> işlemlerinde controller sınıfının çalışma seviyesinden önce <b>csrf</b> filtresini çalıştırır.


```php
/**
 * Index
 *
 * @middleware->when("get", "post")->assign("Csrf");
 * @middleware->when("get", "post")->assign("Auth");
 *
 * @return void
 */
```

Sadece http <b>post</b> ve <b>get</b> işlemlerinde controller sınıfının çalışma seviyesinden önce <b>auth</b> filtresini çalıştırır.


#### Filtreleri tüm sınıf metotlarında geçerli kılmak

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


#### Dipnotları kullanmadan middleware tanımlayabilmek

Eğer dipnotları kullanmadan filtreleri basitçe çağırmanız gereken bir durum sözkonusu ise bunu uygulama sınıfını içerisinden aşağıdaki gibi gerçekleştirebilirsiniz.

```php
namespace Welcome;

Class Welcome extends \Controller
{
    /**
     * Loader
     * 
     * @return void
     */
    public function load()
    {
        $this->c['url'];
        $this->c['app']->middleware('MyMiddleware', $params = array());
    }

    /**
     * Index
     * 
     * @return void
     */
    public function index()
    {
        $this->view->load(
            'welcome',
            [
                'title' => 'Welcome to Obullo !',
            ]
        );
    }

}

/* End of file welcome.php */
/* Location: .modules/welcome/welcome.php */
```