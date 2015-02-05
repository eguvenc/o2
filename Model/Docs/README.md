

## Model nedir ?

------

Modeller veritabanı ile haberleşmeyi sağlayan ve veritabanı fonksiyonları için tasarlanmış php sınıflarıdır. Örnek verecek olursak bir blog uygulaması yaptığımızı düşünelim bu uygulamada yer alan model sınıflarınıza <b>insert, update, delete</b> metotlarını ve veritabanı <b>get</b> metotları koymanız beklenir. Model sınıfı size uygulamada ayrı bir katman sağlar ve veritabanı kodlarınızı bu katmanda geliştirmeniz kodlarınızın sürekliliğine, esnekliğine ve test edilebilirliğine yardımcı olur.

Uygulamanızda model katmanı kullandığınızda <b>sorgu önbellekme</b>, <b>testler</b>, <b>veritabanı kodlarının bakımı</b> gibi problemler kolaylıkla çözülür.

### Modelleri Yüklemek

------


```php
<?php
$this->modelBar = new \Model\Foo\Bar;
$this->modelBar->method();
```

Framework model sınıflarını <b>app/classes/Model</b> klasöründen yükler.

### Modellere Konteyner ile Her Yerden Ulaşmak

```php
<?php
$this->c['model.bar'] = new \Model\Foo\Bar;
$this->c['model.bar']->method();
```

Bazı durumlarda bir model nesnesini birden fazla kullanmak gerekebilir, uygulama içerisinde model birkaç defa farklı sınıflar içerisinde kullanılıyorsa modellerinizi konteyner nesnesine kaydedebilirsiniz. Bir kez kaydedilen model sınıfına daha sonra dilediğiniz yerden <kbd>$this->c['model.modelismi']</kbd> ile ulaşabilirsiniz.

```php
<?php
$this->modelBar = $this->c['model.bar'];
$this->modelBar->method();
```

Konteyner nesnesi nesneleri kayıt etmenizi sağlar buda uygulamanın her yerinde nesnelere ulaşabilmeniz anlamına gelir. Görüldüğü gibi yüklediğiniz modeller konteyner içerisine kaydedilir ve kayıtlı diğer servislerle karışmaması için <b>model.</b> öneki ile farklılaştırılması gerekir.

Aşağıdaki örnek, modellerin nasıl kullanılabileği hakkında size bir fikir verebilir. Bu örnekte gösterilen <b>entry.php</b> dosyasının yolu <b>/model/blog/</b> klasörüdür.

```php
+ app
 - classes
    - Model
	   - Blog
		  Entry.php
```

Model sınıflarını yaratırken aynı sınıf yapılarında olduğu gibi dosya adı ve klasör adı büyük harfle yazılmalıdır.


> Obullo, "Model" kelimesi kullanıldığında bu kütüphaneyi autoloader seviyesinde otomatik olarak yükler. Bu nedenle ana model sınıfına genişlediğinizde Obullo klasörüne ait isim alanını yazmak zorunda kalmazsınız.


#### Entry.php

```php
<?php

namespace Model\Blog;

use Model;

Class Entry extends Model
{
    public $title;
    public $content;
    public $date;

    public function load()
    {
        $this->c['db'];  // Load database object
    }

    /**
     * Get one record
     * 
     * @return array
     */
    public function getOne($id = 1)
    {
    	$this->db->query("SELECT * FROM %s WHERE id = ?", array('entries'), array($id));

    	return $this->db->rowArray();
    }

    /**
     * Get last 10 entries
     * 
     * @return array
     */
    public function getAll($limit = 10)
    {
    	$this->db->query("SELECT * FROM %s LIMIT %d", array('entries', $limit));

    	return $this->db->resultArray();
    }

    /**
     * Insert entry
     * 
     * @return void
     */
    public function insert()
    {
        $this->db->query(
            'INSERT INTO entries (%s,%s,%s) VALUES (?,?,?)', 
            [
                'title',
                'content', 
                $this->db->protect('date')  // example protection for identifiers
            ],
            [
                $this->title, 
                $this->content, 
                (int)$this->date
            ]
        );
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
        $this->db->query(
            "UPDATE entries SET %s=?,%s=?,%s=? WHERE entry_id = ?", 
            [
                'title',
                'content',
                'date'
            ],
            [
                $this->title,
                $this->content,
                (int)$this->date,
                (int)$id
            ]
        );
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
    public function load()
    {
        $this->entry = new \Model\Blog\Entry;
    }

    public function index()
    {
    	$rowArray = $this->entry->getOne(1);     // Modeller ile çalışmaktan çok mutluyum !

		print_r($rowArray);
    }

    public function insert()
    {
        $this->entry->title = 'Insert Test';
        $this->entry->content = 'Hello World';
        $this->entry->date = time();
        $this->entry->insert();

        echo 'New entry added.';
    }

    public function update($id)
    {
        $this->entry->title = 'Update Test';
        $this->entry->content = 'Welcome to my world';
        $this->entry->date = time();
        $this->entry->update($id);

        echo 'Entry updated.';
    }
}

/* End of file welcome.php */
/* Location: .controllers/welcome/welcome.php */
```