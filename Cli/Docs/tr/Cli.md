
## Konsol Arayüzü ( Cli )

Cli paketi yani Command Line Interface komut satırından yürütülen işlemler için yardımcı paketler içerir. Framework konsol arayüzü projenizin ana dizinindeki **task** dosyası üzerinden çalışır.

<ul>
<li><a href="#flow">İşleyiş</a></li>

<li>
    <a href="#running-console-commands">Konsol Komutlarını Çalıştırmak</a>
    <ul>
        <li><a href="#arguments">Argümanlar</a></li>
        <li><a href="#log-command">Log Komutu</a></li>
        <li><a href="#help-commands">Help Komutları</a></li>
        <li><a href="#middleware-command">Middleware Komutu</a></li>
        <li><a href="#module-command">Module Komutu</a></li>
        <li><a href="#domain-command">Domain Komutu</a></li>
        <li><a href="#debugger-command">Debugger Komutu</a></li>
        <li><a href="#queue-command">Queue Komutu</a></li>
        <li><a href="#run-your-commands">Kendi Komutlarınızı Çalıştırmak</a></li>
    </ul>
</li>


<li><a href="#method-reference">Fonksiyon Referansı</a></li>
</ul>

<a name="flow"></a>

### İşleyiş

Cli sınıfı <kbd>.modules/tasks</kbd> dizini içerisindeki komutlar aracılığı ile "--" sembolü ile gönderilen konsol argümanlarını çözümlemek için kullanılır.Bu sınıfın en sık kullanıldığı yerler task controller dosyalarıdır. Sınıf task komutu ile gönderilen isteklere ait argümanları çözümleyerek <kbd>$this->cli</kbd> ( veya <kbd>$this->c['cli']</kbd> ) nesnesi ile bu argümanların yönetilmesini kolaylaştırır. Cli arayüzünde argüman çözümleme esnasında Cli nesnesi <kbd>Obullo\Uri\Uri</kbd> sınıfı içerisinden uygulama içerisine kendiliğinden dahil edilir.

Sınıfı daha iyi anlamak için aşağıdaki gibi <kbd>.modules/tasks</kbd> dizini altında bir task controller yaratın ve yaratığınız task komutuna bir argüman gönderin.

```php
namespace Tasks;

use Controller;
use Obullo\Cli\Console;

class Hello extends Controller {
  
    /**
     * Index
     * 
     * @return void
     */
    public function index()
    {
        echo Console::logo("Welcome to Hello Controller");
        echo Console::description("This is my first task controller.");

        $planet = $this->cli->argument('planet');

        echo Console::text("Hello ".$planet, 'yellow');
        echo Console::newline(2);
    }
}

/* End of file hello.php */
/* Location: .modules/tasks/Hello.php */
```

Konsoldan hello komutunu <b>planet</b> argümanı ile aşağıdaki gibi çalıştırdığınızda bir **Hello World** çıktısı almanız gerekir.

```php
php task hello --planet=World
```

> **Not:** Herhangi bir task controller sınıfı içerisinden http controller sınıfında olduğu gibi gerekli durumlarda <b>load()</b> metodunu kullanabilirsiniz.

Standart parametrelerde desteklenmektedir.

```php
class Hello extends Controller {

    public function index($planet = '')
    {
        echo Console::logo("Welcome to Hello Controller");
        echo Console::description("This is my first task controller.");

        echo Console::text("Hello ".$planet, 'yellow');
        echo Console::newline(2);
    }
}
```

Konsoldan hello komutunu <b>planet</b> argümanı ile aşağıdaki gibi çalıştırdığınızda bir **Hello World** çıktısı almanız gerekir.

```php
php task hello World
```

<a name="running-console-commands"></a>

### Konsol Komutlarını Çalıştırmak

Konsol arayüzüne gönderilen her url task komutu http arayüzüne benzer bir şekilde <b>directory/controller/method</b> olarak çözümlenir. Konsol komutlarındaki url çözümlemesinin http arayüzünden farkı argümanları "--" öneki key => value olarak da gönderebilmenize olanak sağlayarak konsol işlerini kolaylaştırmasıdır. Diğer bir fark ise konsol komutlarında adres çözümlemesi için forward slash "/" yerine boşluk " " karakteri kullanılmasıdır.

Daha iyi anlamak için terminalinizi açıp aşağıdaki komutu çalışırın.

```php
php task help
```

Yukarıdaki komut ana dizindeki task dosyasına bir istek göndererek <kbd>.modules/tasks/</kbd> klasörü altındaki <b>Help</b> adlı controller dosyasının <b>index</b> metodunu çalıştırır.

```php
- modules
  - tasks
      Help.php
```

> **Not:** Eğer bir method ismi yazmazsanız varsayılan method her zaman "index" metodudur.

<a name="arguments"></a>

#### Argümanlar

Argümanlar method çözümlemesinin hemen ardından gönderilirler. Aşağıdaki örnekte uygulamaya bir middleware eklemek için add metodu çözümlemesinden sonra <b>name</b> adlı argüman ve ona ait Csrf değeri gönderiliyor.

```php
php task middleware add --name=Csrf
```

Bir kuyruğu dinlemek için kullanılan konsol komutuna bir başka örnek.

```php
php task queue listen --channel=Logs --route=localhost.Logger --memory=128 --timeout=0 --sleep=3 --debug=1
```

<a name="log-command"></a>

#### Log Komutu

Eğer <kbd>app/config/local/config.php</kbd> dosyasındaki log > enabled anahtarı true olarak ayarlandı ise uygulamayı gezdiğinizde konsol dan uygulama loglarını eş zamanlı takip edebilirsiniz.

Bunun için terminalinizi açın ve aşağıdaki komutu yazın.

```php
php task log
```

Yukarıdaki komut <kbd>modules/tasks/Log</kbd> sınıfını çalıştırır ve <kbd>.resources/data/logs/http.log</kbd> dosyasını okuyarak uygulamaya ait http isteklerinin loglarını ekrana döker.


```php
php task log --dir=ajax
```

Yukarıdaki komut ise  <kbd>modules/tasks/Log</kbd> sınıfını çalıştırır ve <kbd>.resources/data/logs/ajax.log</kbd> dosyasını okuyarak uygulamaya ait ajax isteklerinin loglarını ekrana döker.

<a name="help-commands"></a>

#### Help Komutları

Help metotlarını çalıştırdığınızda bir yardım ekranı ile karşılaşırsınız ve help metodu standart olarak tüm task kontrolör dosyalarında bulunur. Takip eden örnekte log komutuna ait yardım çıktısı gösteriliyor.

```php
php task log help
```

```php
Help:

Available Commands

    clear    : Clear log data ( also removes the queue logs ).
    help     : Help

Available Arguments

    --dir    : Sets log direction for reader. Directions : cli, ajax, http ( default )
    --db     : Database name if mongo driver used.
    --table  : Collection name if mongo driver used.

Usage:

php task log --dir=value

    php task log 
    php task log --dir=cli
    php task log --dir=ajax
    php task log --dir=http --table=logs

Description:

Read log data from "/var/www/framework/resources/data/logs" folder.
```

Clear metodunu çalıştırdığınızda komut <kbd>.resources/data/logs</kbd> dizininden tüm log kayıtlarını siler.

```php
php task log clear
```

> **Not:** Diğer Task komutları hakkında daha fazla bilgiye Obullo\Task paketi dökümentasyonundan ulaşabilirsiniz

<a name="middleware-command"></a>

#### Middleware Komutu

<kbd>Obullo/Application/Middlewares</kbd> klasörūndaki mevcut bir http katmanını uygulamanızın <kbd>app/classes/Http/Middlewares</kbd> klasörūne kopyalar.

Https katmanı için örnek bir kurulum

```php
php task middleware add https
```

Https katmanı için örnek bir kaldırma

```php
php task middleware remove https
```

> Katmanlar hakkında daha geniş bilgi için [Middlewares.md](/Application/Docs/tr/Middlewares.md) dosyasına gözatın.

<a name="module-command"></a>

#### Module Komutu

<kbd>Obullo/Application/Modules</kbd> klasörūndaki mevcut bir modülü uygulamanızın <kbd>modules/</kbd> klasörūne kopyalar.

Debugger modülü için örnek bir kurulum

```php
php task module add debugger
```

Debugger modülü için örnek bir kaldırma

```php
php task module remove debugger
```

> Modüller hakkında daha geniş bilgi için [Modules.md](/Application/Docs/tr/Modules.md) dosyasına gözatın.

<a name="domain-command"></a>

#### Domain Komutu

Domain komutu maintenance katmanını uygulamaya ekler. Eğer <kbd>app/config/domain.php</kbd> dosyanızda tanımlı olan domain adresleriniz varsa uygulamanızı konsoldan bakıma alma işlevlerini yürütebilirsiniz. 

Maintenance katmanı için örnek bir kurulum

```php
php task middleware add maintenance
```

Maintenance katmanı için örnek bir kurulum

```php
php task middleware remove maintenance
```

Uygulamanızı bakıma almak için aşağıdaki komutu çalıştırın.

```php
php task domain down root
```

Uygulamanızı bakımdan çıkarmak için aşağıdaki komutu çalıştırın.

```php
php task domain up root
```

> Maintenance katmanı hakkında daha geniş bilgi için [MaintenanceMiddleware.md](/Application/Docs/tr/MaintenanceMiddleware.md) dosyasına gözatın.

<a name="debugger-command"></a>

#### Debugger Komutu

Debugger modülü uygulamanın geliştirilmesi esnasında uygulama isteklerinden sonra oluşan ortam bileşenleri ve arka plan log verilerini görselleştirir.

Debugger modülü için örnek bir kurulum

```php
php task module add debugger
```

Debugger modülü için örnek bir kaldırma

```php
php task module remove debugger
```
Debug sunucusunu çalıştırmak için aşağıdaki komutu kullanın.

```php
php task debugger
```

Debugger konsolonu görüntülemek için <kbd>/debugger</kbd> sayfasını ziyaret edin

```php
http://myproject/debugger
```

> Debugger modülü hakkında daha geniş bilgi için Debugger paketi [Debbuger.md](/Http/Docs/tr/Debugger.md) belgesine gözatın.

<a name="queue-command"></a>

#### Queue Komutu

Kuyruğa atılan işleri <kbd>Obullo\Task\QueueController</kbd> sınıfına istek göndererek tüketir.

Örnek bir kuyruk dinleme komutu

```php
php task queue listen --channel=Logger --route=Server1.Logger --memory=128 --sleep=3--tries=0 --debug=1
```

> Queue komutu hakkında daha geniş bilgi için [Queue.md](/Queue/Docs/tr/Queue.md) dosyasına gözatın.

<a name="run-your-commands"></a>

#### Kendi Komutlarınızı Çalıştırmak

Modules task klasörū içerisinde kendinize ait task dosyaları yaratabilirsiniz. Bunun için http arayüzündeki controller sınıfına benzer bir şekilde bir kontrolör dosyası yaratın ve namespace bölümünü <b>Tasks</b> olarak değiştirin.

```php
namespace Tasks;

use Controller;

class Hello extends Controller {
  
    /**
     * Index
     * 
     * @return void
     */
    public function index()
    {
        echo Console::logo("Welcome to Hello Controller");
        echo Console::description("This is my first task controller.");
    }
}

/* End of file hello.php */
/* Location: .modules/tasks/Hello.php */
```

Şimdi oluşturduğunuz komutu aşağıdaki gibi çalıştırın.

```php
php task hello
```

#### Help Komutları


<a name="method-reference"></a>

#### Fonksiyon Referansı

------

##### $this->cli->argument(string $name, string $defalt = '');

Girilen isme göre konsol komutundan gönderilen argümanın değerine geri döner.

##### $this->cli->argumentArray();

Çözümlenen argüman listesine "--key=value" olarak bir dizi içerisinde geri döner.

##### $this->cli->segment(integer $n, string $default = '');

Argüman değerini anahtarlar yerine sayılarla alır ve elde edilen argüman değerine geri döner.

##### $this->cli->segmentArray();

Çözümlenen argümanların listesine sadece "value" olarak bir dizi içerisinde geri döner.

##### $this->cli->clear();

Cli sınıf içerisindeki tüm değişkenlerin değerlerini başa döndürür.