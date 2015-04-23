
## Task sınıfı

Task sınıfı **CLI** işlemlerini (shell script vs. çalıştırır) kullanmanızı sağlar, ayrıca php **shell_exec()** fonksiyonu aracılığıyla bazı basit komutları çalıştırmada yardımcı olur.

Tüm task controller sınıfları **app/tasks** dizini altında bulunmaktadır. $this->cliTask->run() methodu controller sınıfını çözümler ve görevleri arka planda çalıştırır.

**Önemli:** Bu sınıf basit işlemlerde çok kullanışlı, örneğin projenizin bir takım ayarlarını önbellekte (cache) tutup yalnızca önbellekten okuduğunuzu varsayalım. Task sınıfıyla projenin çalıştığı sırada ilk işlem olarak ayarları önbelleğe aktararak yalnızca önbellekten çalıştırabilirsiniz.



### Sınıfı yükleme

------

```php
$this->c['task']->run('controller');
```

#### Görevleri (Task) çalıştırma

Task sınıfı framework uri mantığında çalışır. <kbd>controller/index/argument</kbd>

```php
echo $this->task->run('help', true);
```

### Metot Referansları

------

#### $this->task->run('controller/arg1/arg2 ...', $debug = false);

**$this->task->run()** metodunu kullanarak tanımladığınız görevleriniz (task) php shell_exec(); fonksiyonu ile arka planda çalıştırılır.


#### Sürekli Çalıştırılan Görevler

Görevleriniz (task) altındaki komutlar arka planda çalıştırılır. İşlem yapılırken sunucudan her hangi bir cevap gelmesini beklemez.

```php
$this->task->run('controller');
```

#### Log Kayıtlarını Takip Etmek ( Cli Debug )

```php
root@localhost:/var/www/project$ php task log
```

```php
Following log data ...

DEBUG - 2013-09-13 06:39:44 --> Application Controller Class Initialized 
DEBUG - 2013-09-13 06:39:44 --> Html Helper Initialized 
DEBUG - 2013-09-13 06:39:44 --> Url Helper Initialized 
DEBUG - 2013-09-13 06:39:44 --> Application Autorun Initialized 
DEBUG - 2013-09-13 06:39:44 --> View Class Initialized 
DEBUG - 2013-09-13 06:39:44 --> Final output sent to browser 
BENCH - 2013-09-13 06:39:44 --> Memory Usage: 700,752 bytes 
```

#### Clear

Projeniz çalışmaya başlayınca önceki log kayıtlarını temizlemeniz gerekebilir. Bunuda terminalden projenizin bulunduğu dizine gidip **clear** komutunu çalıştırarak yapabilirsiniz.

```php
root@localhost:/var/www/project$ php task clear 
```

#### Update

Obullo'nun yeni versiyonu çıktığında güncellemeleri almanızı sağlar.


```php
root@localhost:/var/www/project$ php task update
```

### Sorun Giderme

------

Framework'ün ana dizinine (root) gidin.

```php
$cd /var/www/project/
```

Komut satırı isteği framework'ün ana dizinindeki (root) task dosyasına gönderir.  
```php
$php task help
```

Yukarıdaki komut **tasks** klasöründen <kbd>app/task/help.php</kbd> dosyasını çalığırır.

```php
         _____ _____ _____ __    __    _____ 
        |     | __  |  |  |  |  |  |  |     |
        |  |  | __ -|  |  |  |__|  |__|  |  |
        |_____|_____|_____|_____|_____|_____|

        Welcome to Task Manager (c) 2014
Please run [$php task help] You are in [ app / tasks ] folder.

Usage:
php task [command] [arguments]

Aktif komutlar:
log        : Proje log dosyalarını takip eder.
clear      : Tanımlı dizindeki log dosyalarını temizler.
help       : Aktif komut listesini gönsterir.
```

Yukardaki ekranı görüyorsanız komut başarıyla çalıştırılmıştır. Eğer başlangıç ekranını gelmediyse php komutlarının çalıştırılacağı yolu kontrol ediniz. 

```php
$which php // komut çıktısı /usr/bin/php 
```
 
Geçerli php yolunuz **/usr/bin/php** değilse contants dosyasını açarak istediğiniz yolu tanımlayabilirsiniz.

```php
define('PHP_PATH', 'your_php_path_that_you_learned_by_which_command'); 
```


## Cli Class

------

Cli sınıfı komut satırı parametrelerini ve argümanlarını ayrıştırmada yardımcı olur.

Örnek komut satırı parametresi
```php
php task queue list
```

Örnek komut satırı parametresi ve argümanları

```php
php task queue listen --channel=Log --route=logger --delay=0 --memory=128 --debug=1
```

> **Note:** Framework sadece tire **(--)** ile başlayan argümanları ayrıştırır.


### Sınıfı Yükleme

------

```php
$this->c['cli'];
```

### Örnek komut

Below the command run <b>app/tasks/queue</b> controller.

**app/tasks/queue** altındaki komutu çalıştıralım.

```php
php task queue list --channel=Log --route=my-hostname.Logger
```

Örnekteki QueueController komut satırından gelen argümanları almak için cli parser sınıfını kullanır.

```php
<?php

namespace Obullo\Cli\Controller;

use Obullo\Process\Process;

/**
 * Queue Controller
 *
 * Listen queue data and workers
 * 
 * @category  Cli
 * @package   Controller
 * @author    Obullo Framework <obulloframework@gmail.com>
 * @copyright 2009-2014 Obullo
 * @license   http://opensource.org/licenses/MIT MIT license
 * @link      http://obullo.com/package/cli
 */
Class QueueController implements CliInterface
{
    /**
     * Listen Queue
     *
     * php task queue listen --channel=Log --route=my-hostname.Logger --memory=128 --delay=0 --timeout=3 --sleep=0 --maxTries=0 --debug=0 --env=prod
     * 
     * @return void
     */
    public function listenQueue()
    {
        $channel = $this->cli->argument('channel', null); // Sets queue exchange
        $route = $this->cli->argument('route', null);     // Sets queue route key ( queue name )
        $memory = $this->cli->argument('memory', 128);    // Sets maximum allowed memory for current job.
        $delay = $this->cli->argument('delay', 0);        // Sets job delay interval
        $timeout = $this->cli->argument('timeout', 0);    // Sets time limit execution of the current job.
        $sleep = $this->cli->argument('sleep', 3);        // If we have not job on the queue sleep the script for a given number of seconds.
        $tries = $this->cli->argument('tries', 0);        // If job attempt failed we push and increase attempt number.
        $debug = $this->cli->argument('debug', 0);        // Enable / Disabled console debug.
        $env = $this->cli->argument('env', 'local');      // Sets environment for current worker.
        $project = $this->cli->argument('project', 'default');  // Sets project name for current worker. 
        $var = $this->cli->argument('var', null);         // Sets your custom variable
        
        $this->emptyControl($channel, $route);

        $cmd = "php task worker --channel=$channel --route=$route --memory=$memory --delay==$delay --timeout=$timeout --sleep=$sleep --tries=$tries --debug=$debug --env=$env --project=$project --var=$var";

        $process = new Process($cmd, ROOT, null, null, $timeout);
        while (true) {
            $process->run();
            if ($debug == 1) {
                echo $process->getOutput();
            }
        }
    }
}

// END QueueController class

/* End of file QueueController.php */
/* Location: .Obullo/Cli/Controller/QueueController.php */
```
### Cli Sınıfı Referansı

------

#### $this->cli->parse(func_get_args());

Geçerli fonksiyon parametrelerini ayrıştırır.

#### $this->cli->segment(int $number);

Tanımlanan komut satırı segmentini döndürür.

#### $this->cli->segmentArray();

Tüm segmentleri döndürür.

#### $this->cli->argument(string $key, string $default = '');

Gets valid command line argument.
Geçerli komut satırı argümanını getirir.

#### $this->cli->argumentArray();

Tüm argümanları döndürür.

## Cli Log Okuyucu

------

Bu paket Cli log kayıtlarını takip etmede yardımcı olur.

### Aktif Sürücüler

------

* FileWriter
* MongoHandler


## Cli Controller Sınıfını Kullanmak

------

Task paketi Cli işlemleriniz de yardımcı olur. (shell script ve unix komutları çalıştırır)
Available Commands

#### Yardım

Aktif komut listesini görmek için.

```php
root@localhost:/var/www/project$ php task

         _____ _____ _____ __    __    _____ 
        |     | __  |  |  |  |  |  |  |     |
        |  |  | __ -|  |  |  |__|  |__|  |  |
        |_____|_____|_____|_____|_____|_____|

        Welcome to Task Manager (c) 2014
Please run [$php task help] You are in [ app / tasks ] folder.

Usage:
php task [command] [arguments]

Available commands:
log        : Follows the application log file.
clear      : Clear application log data. It is currently located in data folder.
help       : See list all of available commands.

```

#### Clear

#### Log

#### Route

#### Queue