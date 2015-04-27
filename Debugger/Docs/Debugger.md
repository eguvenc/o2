
### Debugger Modülü

Debugger modülü log paketi <b>file</b> sürücüsü için hazırlanmış yerel ortamda uygulama isteklerinden sonra oluşan ortam bileşenleri ve arka plan log larını görmenizi sağlayan bir araçtır.

##### Konfigürasyon

Config.php dosyasından debugger modülünü aktif edin.

```php
'debugger' => [
    'enabled' => true,
    'socket'  => 'ws://127.0.0.1:9000'  // Port
],
```

##### Kurulum

Aşağıdaki komutu proje kök dizininde çalıştırın.

```php
php task module add --name=debugger
```

İşlem bittiğinde debugger modülüne ait dosyalar <b>modules/debugger</b>  ve <b>modules/tasks</b> klasörü altına kopyalanırlar.

##### Çalıştırma

Debugger ın çalışabilmesi için debug sunucusunu arka planda çalıştırmalısınız. Bunun için aşağıdaki komutu girin.

```php
php task debugger
```

Şimdi tarayıcınıza gidip debugger sayfasını ziyaret edin.

```php
http://mylocalproject/debugger
```