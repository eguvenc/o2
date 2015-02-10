
## Filesystem Class

------

Dosya ve klasör işlemlerinin hızlı ve kolay bir şekilde yapılabilmesi için yazılmış bir Obullo kütüphanesidir.

### Initializing the Filesystem Class

------

```php
<?php

$this->c['filesystem'];
$this->filesystem->method();
```


## Giriş

Projelerimiz de bazen dosya ve klasör işlemlerini sıkça kullanabiliyoruz. Bu konuda php'nin çok faydalı fonksiyonları mevcut. Bkz: [Php Filesystem](http://php.net/manual/en/book.filesystem.php) Obullo sıkça kullanabileceğimiz dosya işlemlerini bu class altında topladı ve kullanımını gerek basitleştirdi gerekse keyifli bir kullanım sunmaya çalıştı.
    <br />
    <br />
    Obullo Filesystem kütüphanesi 7 metottan oluşmaktadır. Bunları aşağıdaki başlıkta tek tek inceleyelim



### Function Reference

------

#### $this->filesystem->get(string $path);

#### $this->filesystem->write(string $text, bool $shouldBe);

#### $this->filesystem->append(string $text);

#### $this->filesystem->read(string $path);

#### $this->filesystem->delete(mixed $path);

#### $this->filesystem->move(string $path);

#### $this->filesystem->rename(string $name);