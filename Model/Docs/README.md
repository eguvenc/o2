

## Model nedir ?

------

Modeller veritabanı ile haberleşmeyi sağlayan ve veritabanı fonksiyonları için tasarlanmış php sınıflarıdır. Örnek verecek olursak bir blog uygulaması yaptığımızı düşünelim bu uygulamada yer alan model sınıflarınıza <b>insert, update, delete</b> metotlarını ve veritabanı <b>get</b> metotları koymanız beklenir. Model sınıfı size uygulamada ayrı bir katman sağlar ve veritabanı kodlarınızı bu katmanda geliştirmeniz kodlarınızın sürekliliğine, esnekliğine ve test edilebilirliğine yardımcı olur.

Uygulamanızda model katmanı kullandığınızda <b>sorgu önbellekme</b>, <b>testler</b>, <b>veritabanı kodlarının bakımı</b> gibi problemler kolaylıkla çözülür.

### Modelleri yüklemek

------

```php
<?php
$this->c->bind('model bar', 'Foo\Bar');
$this->model->bar->method();
```
Sonraki çağrımda bind metodu singleton yaparak sınıfın ( eski instance ına ) geri dönecektir.

```php
<?php
$this->c->bind('model bar'); // yukarıdaki örnekten hemen sonraki çağrımda singleton
$this->model->bar->method();
```

Model yüklemelerinde konteyner komutlarını da kullabilirsiniz. Örnek bir takmaisim kullanımı.

```php
<?php
$this->c->bind('model bar as takmaisim');
$this->model->takmaisim->method();
```

Yada önceden yüklü bir model den yeni bir instance yaratabilirsiniz.

```php
<?php
$this->c->bind('new model bar', 'Foo\Bar', $params = array());  // yeni instance
```

Bind metodu modelleri konteyner içerisine kayıt etmenizi sağlar buda uygulamanın her yerinde modellere ulaşabilmeniz anlamına gelir. Container sınıfına kaydedilen modellere direkt olarak konteyner üzerinde de ulaşılabilir.

Konteyner üzerinden modellere erişime bir örnek.

```php
$this->c['bind.model.user']->method();
```

Görüldüğü gibi yüklediğiniz modeller konteyner içerisine kaydedilir ve kayıtlı diğer servislerle karışmaması için <b>bind.</b> öneki ile farklılaştırılır.

> **Note:** Bir modele sadece controller sınıfının henüz o yükleme seviyesinde mevcut olmadığı durumlarda konteyner üzerinden erişilmelidir. Controller sınıfının mevcut olduğu durumlarda modellere her zaman aşağıdaki gibi controller üzerinden erişmek gereklidir.

Controller üzerinden modellere erişime bir örnek.

```php
$this->model->bar->method();
```
