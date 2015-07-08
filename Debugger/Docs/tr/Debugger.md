
## Debugger

Debugger paketi uygulamanın geliştirilmesi esnasında uygulama isteklerinden sonra oluşan ortam bileşenleri ve arka plan log verilerini görselleştirir. Debugger modülü aktifken uygulama ziyaret edildiğinde sayfa html frameset etiketleri ile iki ayrı çerçeveye ayrılır orta pencerede uygulama çalışırken, alt çerçeve de; http, konsol, ajax log verileri ve ortam bilgileri ( $_POST, $_SERVER, $_GET, $_SESSION, $_COOKIE, http başlıkları, http gövdesi ) websocket bağlantısı ile dinamik olarak görüntülenir.

### Konfigürasyon

<kbd>config/env/local/config.php</kbd> dosyasından debugger modülü websocket bağlantısını aktif edin.

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

File sürücüsünün logger servisinizde <kbd>$logger->registerHandler(5, 'file');</kbd> metodu ile aşağıdaki gibi tanımlı olması gerekir.

```php
namespace Service\Logger\Env;

use Obullo\Service\ServiceInterface;
use Obullo\Container\ContainerInterface;

class Local implements ServiceInterface
{
    public function register(ContainerInterface $c)
    {
        $c['logger'] = function () use ($c) {
            
            $logger = $c['app']->provider('logger')->get(
                [
                    'queue' => [
                        'enabled' => false,
                    ]
                ]
            );
            /*
            |--------------------------------------------------------------------------
            | Register Handlers
            |--------------------------------------------------------------------------
            */
            $logger->registerHandler(5, 'file');
            $logger->registerHandler(4, 'mongo')->filter('priority@notIn', array(LOG_DEBUG));
            /*
            |--------------------------------------------------------------------------
            | Add Writers - Primary file writer should be available on local server
            |--------------------------------------------------------------------------
            */
            $logger->addWriter('file')->filter('priority@notIn', array());
            return $logger;
        };
    }
}
```

### Linux Kullanıcıları

#### Kurulum

Aşağıdaki komutu konsoldan çalıştırın.

```php
php task module add debugger
```

#### Kaldırma

```php
php task module remove debugger
```

İşlem bittiğinde debugger modülüne ait dosyalar <kbd>modules/debugger</kbd>  ve <kbd>modules/tasks</kbd> klasörü altına kopyalanırlar.

#### Çalıştırma

Debugger ın çalışabilmesi için debug task dosyasını debugger sunucusu olarak arka planda çalıştırmanız gerekir. Bunun için konsolunuza aşağıdaki komutu girin.

```php
php task debugger
```

Html sayfası ziyaret edildiğinde javascript kodu istemci olarak websocket sunucusuna bağlanır. Şimdi tarayıcınıza gidip debugger sayfasını ziyaret edin.

```php
http://mylocalproject/debugger
```

Eğer debugger kurulumu doğru gerçekleşti ise aşağıdaki gibi bir panel uygulamanızın altında belirmiş olmalı.

![Debugger](/Debugger/Docs/images/debugger.png?raw=true "Debugger Ekran Görüntüsü")

Websocket bağlantısı bazı tarayıcılarda kendiliğinden kopabilir panel üzerindeki ![Closed](/Debugger/Docs/images/socket-closed.png?raw=true "Socket Closed") simgesi debugger sunucusuna ait bağlantının koptuğunu ![Open](/Debugger/Docs/images/socket-open.png?raw=true "Socket Open") simgesi ise bağlantının aktif olduğunu gösterir. Eğer bağlantı koparsa verileri sayfa yenilemesi olmadan takip edemezsiniz. Böyle bir durumda debugger sunucunuzu ve tarayıcınızı yeniden başlatmayı deneyin.

### Windows Kullanıcıları

Bu örnekte Xampp Programı baz alınmıştır.

#### Kurulum

Aşağıdaki komutu konsoldan çalıştırın.

```php
C:\xampp\php\php.exe -f "C:\xampp\htdocs\myproject\task" module add debugger
```

#### Kaldırma

```php
C:\xampp\php\php.exe -f "C:\xampp\htdocs\myproject\task" module remove debugger
```

İşlem bittiğinde debugger modülüne ait dosyalar <kbd>modules/debugger</kbd>  ve <kbd>modules/tasks</kbd> klasörü altına kopyalanırlar.

#### Çalıştırma

Debugger ın çalışabilmesi için debug task dosyasını debugger sunucusu olarak arka planda çalıştırmanız gerekir. Bunun için konsolunuza aşağıdaki komutu girin.

```php
C:\xampp\php\php.exe -f "C:\xampp\htdocs\myproject\task" debugger
```

Html sayfası ziyaret edildiğinde javascript kodu istemci olarak websocket sunucusuna bağlanır. Şimdi tarayıcınıza gidip debugger sayfasını ziyaret edin.

```php
http://mylocalproject/debugger
```

Eğer debugger kurulumu doğru gerçekleşti ise aşağıdaki gibi bir panel uygulamanızın altında belirmiş olmalı.

![Debugger](/Debugger/Docs/images/debugger.png?raw=true "Debugger Ekran Görüntüsü")

Websocket bağlantısı bazı tarayıcılarda kendiliğinden kopabilir panel üzerindeki ![Closed](/Debugger/Docs/images/socket-closed.png?raw=true "Socket Closed") simgesi debugger sunucusuna ait bağlantının koptuğunu ![Open](/Debugger/Docs/images/socket-open.png?raw=true "Socket Open") simgesi ise bağlantının aktif olduğunu gösterir. Eğer bağlantı koparsa verileri sayfa yenilemesi olmadan takip edemezsiniz. Böyle bir durumda debugger sunucunuzu ve tarayıcınızı yeniden başlatmayı deneyin.