
## Command Line Interface ( Cli )

------

Php **task** dosyası projenizin ana dizinin de (root) bulunmaktadır. Cli görevlerini çalıştırmanızda size yardımcı olur.  

```php
+ app
+ assets
+ o2
  .
  .
  task
```

Terminalinizi açıp aşağıdaki komutu çalıştırabilirsiniz.

```php
php task help
```

Her task komutu bir task **controller** olarak çözümlenir.

```php
- app
  - config
  - tasks
      help.php
```

### Loglar takip etme için

Terminalinizi açın ve aşağıdaki komutu yazınız

```php
php task log
```

Yukarıdaki komut **log** Cli sınıfını çalıştırır ve app.log dosyasını okuyarak uygulama loglarını takip eder.

**Log** parametresi bir controller sınıfıdır, **app/task** dizini altında bulunmaktadır.

```php
- app
- config
- tasks
  - controller
    log.php
```

### Log kayıtlarını temizlemek

Terminalinizi açın ve aşağıdaki komutu yazınız

```php
php task clear
```

Yukarıdaki komut **app/data/logs** dizininden tüm log kayıtlarını siler.

**Clear** parametresi bir controller sınıfıdır, **app/task** dizini altında bulunmaktadır.


```php
- app
- config
- tasks
  - controller
    clear.php
    log.php
```

## Task sınıfı

Task sınıfı **CLI** işlemlerini (shell script vs. çalıştırır) kullanmanızı sağlar, ayrıca php **shell_exec()** fonksiyonu aracılığıyla bazı basit komutları çalıştırmada yardımcı olur.

Tüm task controller sınıfları **app/tasks** dizini altında bulunmaktadır. $this->cliTask->run() methodu controller sınıfını çözümler ve görevleri arka planda çalıştırır.

**Önemli:** Bu sınıf basit işlemlerde çok kullanışlı, örneğin projenizin bir takım ayarlarını önbelleğinde (cache) tutup yalnızca önbellekten okuduğunuzu varsayalım. Task sınıfıyla projenin çalıştığı sırada ilk işlem olarak ayarları önbelleğe aktararak yalnızca önbellekten çalıştırabilirsiniz.



### Sınıfı yükleme

------

```php
<?php
$this->c->load('cli/task as task');
$this->task->run('controller');
```

#### Görevleri (Task) çalıştırma

Task sınıfı framework uri mantığında çalışır. <kbd>controller/index/argument</kbd>

```php
<?php
echo $this->task->run('help', true);
```

### Function Reference

------

#### $this->task->run('controller/arg1/arg2 ...', $debug = false);

Using $this->task->run() function run your tasks as a using php shell_exec(); command in the background.


#### Continious Tasks

Using below the command your task will be done in the background without wait the http server response.

```php
$this->task->run('controller');
```

#### Follow Log Data ( Cli Debug )

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

When you move your project to another server you need to clear log data. Go to your terminal and type your project path then run the clear.

```php
root@localhost:/var/www/project$ php task clear 
```

#### Update

This command upgrade your Obullo if new version available.

```php
root@localhost:/var/www/project$ php task update
```

### Troubleshooting

------

Go to your framework root folder.

```php
$cd /var/www/project/
```

Command line request goes to framework <b>task</b> file which is located in your root.


```php
$php task help
```

Above the command calls the <kbd>app/task/help.php</kbd> class from <b>tasks</b> folder.

```php
        ______  _            _  _
       |  __  || |__  _   _ | || | ____
       | |  | ||  _ || | | || || ||  _ |
       | |__| || |_||| |_| || || || |_||
       |______||____||_____||_||_||____|

        Welcome to Task Manager (c) 2014
Please run [$php task help] You are in [ app / tasks ] folder.

Usage:
php task [command] [arguments]

Available commands:
log        : Follows the application log file.
clear      : Clear application log data. It is currently located in data folder.
help       : See list all of available commands.
```

If you see this screen your command successfully run <b>otherwise</b> check your <b>php path</b> running by this command

```php
$which php // command output /usr/bin/php 
```

If your current php path is not <b>/usr/bin/php</b> open the <b>constants</b> file and define your php path. 

```php
define('PHP_PATH', 'your_php_path_that_you_learned_by_which_command'); 
```