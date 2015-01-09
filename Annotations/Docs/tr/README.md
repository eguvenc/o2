
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
            <td><b>filter->before("name");</b></td>
            <td>Before filtresini çalıştırırır, before metodu içerisine yazılan filtre controller sınıfının çalışmasından önceki seviyede çalışır.</td>
        </tr>
        <tr>
            <td><b>filter->after("name");</b></td>
            <td>After filtresini çalıştırırır, after metodu içerisine yazılan filtre controller sınıfının çalışmasından sonraki seviyede çalışır.</td>
        </tr>
        <tr>
            <td><b>filter->load("name");</b></td>
            <td>Load filtresini çalıştırırır, load metodu içerisine yazılan filtre controller load metodunun çalışmasından sonraki seviyede çalışır.</td>
        </tr>
        <tr>
            <td><b>filter->method("post","get");</b></td>
            <td>Http protokolü tarafından gönderilen istek metodu bu metot içerisine yazılan metotlardan biri ile eşleşmez ise bu dipnotun kullanıldığı controller a erişime izin verilmez. Erişim yasağı hallinde meydana gelen durum bir olaya bağlanmıştır. Olay metodu <b>app/classes/Event/Request</b> sınıfından özelleştirilebilir.</td>
        </tr>
         <tr>
            <td><b>filter->before("name")->when("post","get")</b></td>
            <td>Filtreyi çalıştırır eğer http protokolü tarafından gönderilen istek metodu when metodu içerisine yazılan metotlardan biri ile eşleşmez ise bu dipnotun kullanıldığı controller a erişime izin verilmez.</td>
        </tr>

    </tbody>
</table>

### Controller için dipnotları aktif etmek

Config.php konfigürasyon dosyasını açın ve annotations reader anahtarının değerini <b>true</b> olarak güncelleyin.

```php
<?php
/*
|--------------------------------------------------------------------------
| Controller
|--------------------------------------------------------------------------
*/
'controller' => array(
    'annotation' => array(
        'reader' => true,
    )
)
```

Artık controller sınıfı index metotları üzerinde dipnotları aşağıdaki gibi kullanabilirsiniz.

```php
<?php

/**
 * Index
 *
 * @filter->before("activity")->when("get", "post");
 * 
 * @return void
 */
public function index()
{
    // ..
}


/* End of file welcome.php */
/* Location: .public/welcome/controller/welcome.php */
```

Aşağıdaki örneklere bir göz atın.


#### Örnekler

```php
<?php
/**
 * Index
 *
 * @filter->before("csrf");
 * @filter->method("post","get");
 *
 * @return void
 */
```

Controller sınıfının çalışma seviyesinden önce <b>csrf</b> filtresini çalıştırır ve sadece <b>get</b> ve <b>post</b> metotlarına erişime izin verir.

```php
<?php
/**
 * Index
 *
 * @filter->before("csrf")->when("post");
 * 
 * @return void
 */
```

Sadece http <b>post</b> işlemlerinde controller sınıfının çalışma seviyesinden önce <b>csrf</b> filtresini çalıştırır.


```php
<?php
/**
 * Index
 *
 * @filter->before("auth")->when("get", "post");
 *
 * @return void
 */
```

Sadece http <b>post</b> ve <b>get</b> işlemlerinde controller sınıfının çalışma seviyesinden önce <b>auth</b> filtresini çalıştırır.
