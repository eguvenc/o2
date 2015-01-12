

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
$this->c->bind('model bar'); // yukarıdaki örnekten hemen sonraki çağrımda singleton uygulanır.
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
$this->c->bind('new model bar', 'Foo\Bar', $params = array());  // yeni instance yaratmak
```

Bind metodu modelleri konteyner içerisine kayıt etmenizi sağlar buda uygulamanın her yerinde modellere ulaşabilmeniz anlamına gelir. Container sınıfına kaydedilen modellere direkt olarak konteyner üzerinde de ulaşılabilir.

Konteyner üzerinden modellere erişime bir örnek.

```php
$this->c['bind.model.user']->method();
```

Görüldüğü gibi yüklediğiniz modeller konteyner içerisine kaydedilir ve kayıtlı diğer servislerle karışmaması için <b>bind.</b> öneki ile farklılaştırılır.

> **Note:** Bir modele sadece controller sınıfının yükleme seviyesinde mevcut olmadığı durumlarda konteyner üzerinden erişilmelidir. Controller sınıfının mevcut olduğu durumlarda modellere her zaman aşağıdaki gibi controller üzerinden erişmek gerekir.

Controller üzerinden modellere erişime bir örnek.

```php
$this->model->bar->method();
```

Aşağıdaki örnek modellerin nasıl yazılabileceği hakkında size bir fikir verebilir. Bu örnekte gösterilen <b>entry.php</b>  dosyasının yolu <b>/models/blog/</b> klasörüdür.

```php
+ app
+ controllers
- models
	- Blog
		Entry.php

```

Model sınıflarını yaratırken aynı sınıf yapılarında olduğu gibi dosya adı ve klasör adı büyük harfle yazılmalıdır. Php namespace özelliği tercihe göre gerek duyulmayan yerlerde kullanılmayabilir.




#### Entry.php

```php
<?php

namespace Blog;

use Model;

Class Entry extends Model
{
    public $title;
    public $content;
    public $date;

    /**
     * Loader
     * 
     * @return void
     */
    public function load()
    {
        $this->c->load('service/db');  // Load database object
    }

    /**
     * Get one record
     * 
     * @return array
     */
    public function getOne($id = 1)
    {
    	$this->db->query("SELECT * FROM %s WHERE id = ?", array('users'), array($id));

    	return $this->db->rowArray();
    }

    /**
     * Get last 10 entries
     * 
     * @return array
     */
    public function getAll($limit = 10)
    {
    	$this->db->query("SELECT * FROM %s LIMIT %d", array('users', $limit));

    	return $this->db->resultArray();
    }

    /**
     * Insert entry
     * 
     * @return void
     */
    public function insert()
    {
    	$data = array(
            'title' => $this->title,
            'content' => $this->content,
            'date' => (int)$this->date,
            );

    	$this->db->insert('users', $data);
    }

    /**
     * Update entry
     * 
     * @param integer $id id
     * 
     * @return void
     */
    public function update($id)
    {
    	$data = array(
            'title' => $this->title,
            'content' => $this->content,
            'date' => (int)$this->date,
            );

    	$this->db->update('entries', $data, array('entry_id' => $id));
    }

}


/* End of file entry.php */
/* Location: .models/Blog/Entry.php */
```

Şimdi entry modelini controller sınıfı içerisinde nasıl kullanacağımıza bir bakalım.


```php
<?php

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
        $this->c->bind('model entry', 'Blog\Entry');
    }

    /**
     * Index
     * 
     * @return void
     */
    public function index()
    {
    	$rowArray = $this->model->entry->getOne(1);     // Modeller ile çalışmaktan çok mutluyum !

		print_r($rowArray);
    }
}

/* End of file welcome.php */
/* Location: .controllers/welcome/welcome.php */
```