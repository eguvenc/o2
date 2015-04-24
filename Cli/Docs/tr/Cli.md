
## Konsol Arayüzü ( Cli )

------

Cli paketi yani Command Line Interface komut satırından yürütülen işlemler için yardımcı paketler içerir. Framework konsol arayüzü projenizin ana dizinindeki **task** dosyası üzerinden çalışır.

### Cli Sınıfı

Cli sınıfı <kbd>.modules/tasks</kbd> dizini içerisindeki komutlar aracılığı ile "--" sembolü ile gönderilen konsol argümanlarını çözümlemek için kullanılır.Bu sınıfın en sık kullanıldığı yerler task controller dosyalarıdır. Sınıf task komutu ile gönderilen isteklere ait argümanları çözümleyerek <kbd>$this->cli</kbd> ( vs. <kbd>$this->c['cli']</kbd> ) nesnesi ile bu argümanların  yönetilmesini kolaylaştırır. Cli arayüzünde argüman çözümleme esnasında Cli nesnesi <kbd>Obullo\Uri\Uri</kbd> sınıfı içerisinden uygulama içerisine kendiliğinden dahil edilir.

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

> **Not:** Herhangi bi task controller sınıfı içerisinden http controller sınıfında olduğu gibi gerekli ise <b>load()</b> metodunu kullanabilirsiniz.

### Konsoldan Komut Çalıştırmak

Konsol arayüzüne gönderilen her url task komutu http arayüzüne benzer bir şekilde <b>module/controller/method</b> olarak çözümlenir. Konsol komutlarındaki url çözümlemesinin http arayüzünden farkı argümanları "--" öneki key => value olarak da gönderebilmemize olanak sağlayarak konsol işlerini kolaylaştırmasıdır. Diğer bir fark konsol komutlarında adres çözümlemesi için forward slash "/" yerinde boşluk " " karakteri kullanılır.

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

### Argümanlar

Argümanlar method çözümlemesinin hemen ardından gönderilirler. Aşağıdaki örnekte uygulamaya bir middleware eklemek için add metodu çözümlemesinden sonra <b>name</b> adlı arguman ve Csrf değeri gönderiliyor.

```php
php task middleware add --name=Csrf
```

Bir kuyruğu dinlemek için kullanılan konsol komutuna bir örnek.

```php
php task queue listen --channel=Logs --route=localhost.Logger --memory=128 --timeout=0 --sleep=3 --debug=1
```

### Log Komutu

Eğer <kbd>app/config/local/config.php</kbd> dosyasındaki log > enabled sekmesi "true" olarak ayarlandı ise uygulamayı gezdiğinizde konsol dan uygulama loglarını eş zamanlı takip edebilirsiniz.

Bunun için terminalinizi açın ve aşağıdaki komutu yazın.

```php
php task log
```

Yukarıdaki komut <kbd>modules/tasks/Log</kbd> sınıfını çalıştırır ve <kbd>.resources/data/logs/http.log</kbd> dosyasını okuyarak uygulamaya ait http isteklerinin loglarını ekrana döker.


```php
php task log --dir=ajax
```

Yukarıdaki komut <kbd>modules/tasks/Log</kbd> sınıfını çalıştırır ve <kbd>.resources/data/logs/ajax.log</kbd> dosyasını okuyarak uygulama ait ajax isteklerinin loglarını ekrana döker.

```php
php task log --dir=ajax
```

Help metodunu çalıştırdığınızda bir yardım ekranı ile karşılaşırsınız help metodu tüm task controller dosyalarında bulunur.

```php
php task log help
```
```php
                _           _ _       
           ___ | |__  _   _| | | ___  
          / _ \| '_ \| | | | | |/ _ \ 
         | (_) | |_) | |_| | | | (_) |
          \___/|_.__/ \__,_|_|_|\___/  

       Welcome to Log Manager (c) 2015
You are displaying logs. For more help type $php task log help.

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

Read log data from '/var/www/framework/resources/data/logs' folder.
```

Clear metodunu çalıştırdığınızda komut <kbd>.resources/data/logs</kbd> dizininden tüm log kayıtlarını siler.

```php
php task log clear
```

> **Not:** Diğer Task komutları hakkında daha fazla bilgiye Obullo\Task paketi dökümentasyonundan ulaşabilirsiniz


### Kendi Komutlarınızı Çalıştırmak

Http arayüzündeki controller sınıfına benzer bir şekilde bir controller dosyası yaratın ve namespace bölümünü <b>Tasks</b> olarak değiştirin.

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

#### Cli Sınıfı Referansı

------

##### $this->cli->argument(string $name, string $defalt = '');

Girilen isme göre konsol komutundan gönderilen argümanın değerini verir.

##### $this->cli->argumentArray();

Çözümlenen argüman listesine "--key=value" olarak bir dizi içerisinde geri döner.

##### $this->cli->segment(integer $n, string $default = '');

Argüman değerini anahtarlar yerine sayılarla alır ve elde edilen argüman değerine döner.

##### $this->cli->segmentArray();

Çözümlenen argümanların listesine sadece "value" olarak bir dizi içerisinde geri döner.

##### $this->cli->clear();

Cli sınıf içerisindeki tüm değişkenlerin değerlerini başa döndürür.