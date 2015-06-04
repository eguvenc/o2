
### Http Debugger

Debugger paketi uygulamanın geliştirilmesi esnasında uygulama isteklerinden sonra oluşan ortam bileşenleri ve arka plan log verilerini görselleştirir. Debugger modülü aktifken uygulama ziyaret edildiğinde sayfa html frameset etiketleri ile iki ayrı çerçeveye ayrılır orta pencerede uygulama çalışırken, alt çerçeve de; http, konsol, ajax log verileri ve ortam bilgileri ( $_POST, $_SERVER, $_GET, $_SESSION, $_COOKIE, http başlıkları, http gövdesi ) websocket bağlantısı ile dinamik olarak görüntülenir.

> ***Not:*** Şu anki sürümde debugger paketi yalnızca logger <b>file</b> sürücüsü log servisinde tanımlı iken log debug verilerini görselleştirebilir.

##### Konfigürasyon

<kbd>app/config/env.local/config.php</kbd> dosyasından debugger modülü websocket bağlantısını aktif edin.

```php
return array(

    'http' => [
        'debugger' => [
            'enabled' => false,
            'socket' => 'ws://127.0.0.1:9000'
        ]
    ],
    
)
```

##### Kurulum

Aşağıdaki komutu konsoldan çalıştırın.

```php
php task module add debugger
```

##### Kaldırma

```php
php task module remove debugger
```

İşlem bittiğinde debugger modülüne ait dosyalar <b>modules/debugger</b>  ve <b>modules/tasks</b> klasörü altına kopyalanırlar.

##### Çalıştırma

Debugger ın çalışabilmesi için debug task dosyasını debugger sunucusu olarak arka planda çalıştırmanız gerekir. Bunun için konsolunuza aşağıdaki komutu girin.

```php
php task debugger
```

Html sayfası ziyaret edildiğinde javascript kodu istemci olarak websocket sunucusuna bağlanır. Şimdi tarayıcınıza gidip debugger sayfasını ziyaret edin.

```php
http://mylocalproject/debugger
```

Eğer debugger kurulumu doğru gerçekleşti ise aşağıdaki gibi bir panel uygulamanızın altında belirmiş olmalı.

![Debugger](/Http/Docs/images/debugger.png?raw=true "Debugger Ekran Görüntüsü")

Websocket bağlantısı bazı tarayıcılarda kendiliğinden kopabilir panel üzerindeki ![Closed](/Http/Docs/images/socket-closed.png?raw=true "Socket Closed") simgesi debugger sunucusuna ait bağlantının koptuğunu ![Open](/Http/Docs/images/socket-open.png?raw=true "Socket Open") simgesi ise bağlantının aktif olduğunu gösterir. Eğer bağlantı koparsa verileri sayfa yenilemesi olmadan takip edemezsiniz. Böyle bir durumda debugger sunucunuzu ve tarayıcınızı yeniden başlatmayı deneyin.