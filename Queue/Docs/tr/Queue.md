
## Kuyruklama

Kuyruklama paketi uzun sürmesi beklenen işlemlere ( loglama, email gönderme, sipariş alma gibi. ) ait verileri bir mesaj gönderim protokolü ( AMQP ) üzerinden arkaplanda işlem sırasına sokar. Kuyruğa atılan veriler eş zamanlı işlemler (multi threading) ile tüketilirek süreç arkaplanda yürütülür ve böylece uzun süren işlemler ön yüzde sadece işlem sırasına atıldığından uygulamanıza gelen http istekleri yorulmamış olur.

<ul>

<li>
    <a href="#configuration">Konfigürasyon</a>
    <ul>
        <li><a href="#service-configuration">Servis Konfigürasyonu</a></li>
        <li><a href="#server-requirements">Sunucu Gereksinimleri</a></li>
    </ul>
</li>

<li>
    <a href="#running">Çalıştırma</a>
    <ul>
        <li><a href="#loading-service">Servisi Yüklemek</a></li>
    </ul>
</li>

<li>
    <a href="#queue">Kuyruklama</a>
    <ul>
        <li><a href="#queuing-a-job">Bir İşi Kuyruğa Atmak</a></li>
        <li><a href="#delaying-a-job">Bir İşin Çalışmasını Geciktirmek</a></li>
    </ul>
</li>

<li>
    <a href="#workers">İşçiler</a>
    <ul>
        <li><a href="#define-worker">Bir İşçi Tanımlamak</a></li>
        <li><a href="#delete-job">Tamamlanmış Bir İşi Kuyruktan Silmek</a></li>
        <li><a href="#release-job">Bir İşi Kuyruğa Tekrar Atmak</a></li>
        <li><a href="#job-attempt">Bir İşin Denenme Sayısını Almak</a></li>
        <li><a href="#job-id">Bir İşin ID Değerini Almak</a></li>
    </ul>
</li>

<li>
    <a href="#running-workers">Konsoldan İşçileri Çalıştırmak</a>
    <ul>
        <li><a href="#show">Kuyruğu Listelemek</a> (show)</li>
        <li><a href="#listen">Kuyruğu Dinlemek</a> (listen)</li>
        <li>
            <a href="#timeout">İşçi Parametreleri</a>
            <ul>
                <li><a href="#worker">--worker</a></li>
                <li><a href="#job">--job</a></li>
                <li><a href="#output">--output</a></li>
                <li><a href="#timeout">--timeout</a></li>
                <li><a href="#sleep">--sleep</a></li>
                <li><a href="#memory">--memory</a></li>
                <li><a href="#delay">--delay</a></li>
                <li><a href="#attempt">--attempt</a></li>
                <li><a href="#var">--var</a></li>
            </ul>
        </li>
    </ul>
</li>

<li>
    <a href="#threading">Çoklu İş Parçalarını Kontrol Etmek</a> (Multi Threading)</a>
    <ul>
        <li><a href="#supervisor">Ubuntu Altında Supervisor Kurulumu</a></li>
        <li><a href="#creating-first-worker">İlk İşçimizi Yaratalım</a></li>
        <li><a href="#multiple-workers">Birden Fazla İşçiyi Çalıştırmak</a></li>
        <li><a href="#starting-all-workers">Bütün İşçileri Başlatmak</a></li>
        <li><a href="#displaying-all-workers">Bütün İşçileri Görüntülemek</a></li>
        <li><a href="#stopping-all-workers">Bütün İşçileri Durdurmak</a></li>
        <li><a href="#stopping-all-workers">Bütün İşçileri Durdurmak</a></li>
        <li><a href="#additional-info">Ek Bilgiler</a></li>
        <ul>
          <li><a href="#worker-threads">İşlemci Başına Optitumum İş Parçaçığı</a></li>
          <li><a href="#startup-config">Otomatik Başlat İle Çalıştırmak</a></li>
          <li><a href="#worker-logs">İşci Loglarını Görüntülemek</a></li>
          <li><a href="#web-interface">Supervisord İçin Web Arayüzü</a></li>
        </ul>
    </ul>
</li>

<li>
    <a href="#failures">Başarısız İşler</a>
    <ul>
        <li><a href="#save-failures">Başarısız İşleri Veritabanına Kaydetmek</a></li>
        <li><a href="#failure-config">Konfigürasyon</a></li>
        <li><a href="#failure-sql-file">SQL Dosyası</a></li>
    </ul>
</li>

<li>
    <a href="#method-reference">Fonksiyon Referansı</a>
    <ul>
        <li><a href="#queue-reference">Queue Sınıfı Metotları</a></li>
        <li><a href="#job-reference">Job Sınıfı Metotları</a></li>
        <li><a href="#worker-reference">Worker Sınıfı Metotları</a></li>
    </ul>
</li>

</ul>

<a name="configuration"></a>

### Konfigürasyon

Queue servisi ana konfigürasyonu <kbd>config/$env/queue/amqp.php</kbd> dosyasından konfigüre edilir. Dosya içerisindeki <kbd>exchange</kbd> anahtarına AMQP sürücüsüne ait ayarlar konfigüre edilirken <kbd>connections</kbd> anahtarına ise AMQP servis sağlayıcısı için gereken bağlantı bilgileri girilir.

```php
return array(

    'exchange' => [
        'type' => 'AMQP_EX_TYPE_DIRECT',
        'flag' => 'AMQP_DURABLE',
    ],
    
    'connections' => 
    [
        'default' => [
            'host'  => '127.0.0.1',
            'port'  => 5672,
            'username'  => 'root',
            'password'  => $c['env']['AMQP_PASSWORD'],
            'vhost' => '/',
        ]
    ],
);
```
<a name="server-requirements"></a>

#### Sunucu Gereksinimleri

Kuyruklama servisinin çalışabilmesi için php AMQP extension kurulu olması gerekir. Php AMQP arayüzü ile çalışan birçok kuyruklama yazılımı mevcuttur. Bunlardan bir tanesi de <a href="https://www.rabbitmq.com/" target="_blank">RabbitMQ</a> yazılımıdır. Aşağıdaki linkten RabbitMQ yazılımı için Ubuntu işletim sistemi altında gerçekleştilen örnek bir kurulum bulabilirsiniz.

<a href="https://github.com/obullo/warmup/tree/master/AMQP/RabbitMQ">RabbitMQ ve Php AMQP Extension Kurulumu </a>

<a name="service-configuration"></a>

#### Servis Konfigürasyonu

Queue paketini kullanabilmeniz için aşağıdaki gibi servis olarak yapılandırılmış olması gerekir.

```php
namespace Service;

use Obullo\Queue\QueueManager;
use Obullo\Service\ServiceInterface;
use Obullo\Container\ContainerInterface;

class Queue implements ServiceInterface
{
    public function register(ContainerInterface $c)
    {
        $c['queue'] = function () use ($c) {

            $parameters = [
                'class' => '\Obullo\Queue\Handler\AMQP',
                'provider' => [
                    'name' => 'amqp',
                    'params' => [
                        'connection' => 'default'
                    ]
                ]
            ];
            $manager = new QueueManager($c);
            $manager->setParameters($parameters);
            $handler = $manager->getHandler();
            return $handler;
        };
    }
}
```

<a name="running"></a>

### Çalıştırma

Servis konteyner içerisinden çağırıldığında tanımlı olan queue arayüzü üzerinden ( AMQP ) kuyruklama metotlarına ulaşılmış olur. 

<a name="loading-service"></a>

#### Servisi Yüklemek

Queue servisi aracılığı ile queue metotlarına aşağıdaki gibi erişilebilir.

```php
$this->c['queue']->metod();
```

<a name="queue"></a>

### Kuyruklama

<a name="queuing-a-job"></a>

#### Bir İşi Kuyruğa Atmak

Bir işi kuyruğa atmak için <kbd>$this->queue->push()</kbd> metodu kullanılır.

```php
$this->queue->push('Workers\Mailer', 'mailer.1', array('mailer' => 'x', 'message' => 'Hello World !'));
```

Birinci parametreye <kbd>app/classes/Workers/</kbd> klasörü altındaki işçiye ait sınıf yolu, ikinci parametreye kuyruk adı, üçüncü parametreye ise kuyruğa gönderilecek veriler girilir. Opsiyonel olan dördüncü parametreye ise varsa amqp sürücüsüne ait gönderim seçenekleri girilebilir.

<a name="delaying-a-job"></a>

#### Bir İşin Çalışmasını Geciktirmek

```php
$this->queue->later(
    $delay = 60,
    'Workers\Order',
    'order.1',
    array('order_id' => 'x', 'order_data' => [])
);
```

Eğer later metodu kullanılarak ilk parametreye integer türünde (unix time) bir zaman değeri girilirse girilen veri kuyruğa belirlenen süre kadar gecikmeli olarak eklenir.

<a name="workers"></a>

### İşçiler

Uygulamada kuyruğu tüketen her işçi <kbd>app/classes/Workers/</kbd> klasörü altında çalışır. Bir işi kuyruğa gönderirken iş parametresine uygulamanızdaki klasör yolunu vererek kuyruğu tüketecek işçi belirlenir.

```php
$this->queue->push('Workers\Mailer', 'mailer.1', array('mailer' => 'x', 'message' => 'Hello World !'));
```

Yukarıdaki örnekte <kbd>Workers\Mailer</kbd> adlı işe ait girilen veriler <kbd>mailer.1</kbd> adlı kuyruğa gönderilir.

<a name="define-worker"></a>

#### Bir İşçi Tanımlamak

Kuyruğa gönderme esnasında parametre olarak girilen işçi adı <kbd>app/classes/Workers/</kbd> klasörü altında bir sınıf olarak yaratılmalıdır. Sınıf içerisinde tanımlı olan fire metodu ilk parametresine iş sınıfı ikinci parametresine ise işe ait kuyruk verileri gönderilir.

Aşağıda <kbd>Workers\Mailer</kbd> örneği görülüyor.

```php
namespace Workers;

use Obullo\Queue\Job;
use Obullo\Queue\JobInterface;
use Obullo\Container\ContainerInterface;

class Mailer implements JobInterface
{
    protected $c;

    public function __construct(ContainerInterface $c)
    {
        $this->c = $c;
    }

    public function fire($job, array $data)
    {
        switch ($data['mailer']) { 
        case 'x': 

            print_r($data);

            // Send mail message using "x" mail provider

            break;
        }
        if ($job instanceof Job) {
            $job->delete(); 
        }       
    }
}

/* Location: .app/classes/Workers/Mailer.php */
```

> **Not:** Bir iş sınıfı içerisinde sadece <kbd>fire</kbd>  metodu ilan edilmesi job nesnesini ve kuyruğa atılan veriyi almak için yeterlidir.

Mailer işçisi çalıştığında fire metodu ilk parametresine iş sınıfı gönderilir.

```php
php task queue listen --worker=Workers\Mailer --job=mailer.1 --output=1
```

Argümanlar için aşağıdaki gibi kısa yolları da kullanabilirsiniz.

```php
php task queue listen --e=Workers\Mailer --r=mailer.1 --o=1
```

<a name="delete-job"></a>

#### Tamamlanmış Bir İşi Kuyruktan Silmek

Fire metodu ile elde edilen iş nesnesi iş tamamlandıktan sonra <kbd>$job->delete()</kbd> metodu ile silinmelidir. Aksi durumda tüm işler kuyruklama yazılımında birikir.

```php
public function fire($job, array $data)
{
    imageResize($data);  // Do job

    if ($job instanceof Job) {  // Delete completed job
        $job->delete(); 
    }       
}
```

<a name="release-job"></a>

#### Bir İşi Kuyruğa Tekrar Atmak


#### Bir İşin Denenme Sayısını Almak


#### Bir İşin Denenme Sayısını Almak




### Hello Queue !

This tutorial simply demonstrate <b>pushing your data</b> to queue using your queue handler.

```php
/**
 * $app hello_world
 * 
 * @var Controller
 */
$app = new Controller(
    function ($c) {
        $c->load('queue');
      
        $this->queue->push('Workers/Logger', 'Server1.logger', array('log' => array('debug' => 'Test')));
    }
);

$app->func(
    'index',
    function () {

    }
);

/* End of file hello_world.php */
/* Location: .public/tutorials/controller/hello_world.php */
```

### Displayin Queue Data

To follow your Queue data <b>Open your console and type</b>

```
php task queue show --j=logger.1
```

Then you will see your Queue data here

```
Following queue data ...

Worker : Workers/Logger
Job    : logger.1
------------------------------------------------------------------------------------------
 Job ID | Job Name             | Data 
------------------------------------------------------------------------------------------
 1      | Workers/Logger  | {"log":{"debug":"test"}}
 2      | Workers/Logger  | {"message":"this is my message"}
 3      | Workers/Logger  | {"log":{"debug":"test"}}
```


## Queue Control ( Command Line Interface )

------

Queue control allow to us <b>display, listen, delete</b> the queues also do <b>test</b> for workers.

### Display Queues

```php
php task queue show --worker=Workers\Logger --job=logger.1
```

### Clear Queue Data

```php
php task queue show --worker=Workers\Logger --job=logger.1 --clear=1
```

### Running Your Queue Workers

```php
php task queue listen --worker=Workers\Logger --job=logger.1 --output=1
```

Advanced Parameters

```php
php task queue listen --worker=Workers\Logger --job=logger.1 --delay=0 --memory=128 --timeout=0 --sleep=3 --attempt=0 --output=1
```

### Zorunlu Parametreler

<table>
<thead>
<tr>
<th>Parametre</th>
<th>Kısayol</th>
<th>Açıklama</th>
</thead>
<tbody>
<tr>
<td>--worker</td>
<td>--w</td>
<td>Sets queue exchange ( Channel ).</td>
</tr>
<tr>
<td>--job</td>
<td>--j</td>
<td>Sets queue name.</td>
</tr>
</tbody>
</table>

### Opsiyonel Parametreler

<table>
<thead>
<tr>
<th>Parametre</th>
<th>Kısayol</th>
<th>Açıklama</th>
</thead>
<tbody>
<tr>
<td>--output</td>
<td>--o</td>
<td>Print queue output and any possible exceptions. ( Designed for local environment  )</td>
</tr>
<tr>
<td>--delay</td>
<td>--d</td>
<td>Sets delay time for uncompleted jobs.</td>
</tr>
<tr>
<td>--memory</td>
<td>--m</td>
<td>Sets maximum allowed memory for current job.</td>
</tr>
<tr>
<td>--timeout</td>
<td>--t</td>
<td>Sets time limit execution of the current job.</td>
</tr>
<tr>
<td>--sleep</td>
<td>--s</td>
<td>If we have not job on the queue sleep the script for a given number of seconds.</td>
</tr>
<tr>
<td>--attempt</td>
<td>--a</td>
<td>Sets the maximum number of times a job should be attempted.</td>
</tr>
<tr>
<td>--var</td>
<td>--v</td>
<td>Sets a custom variable.</td>
</tr>
<tr>
<td>--e</td>
<td>--env</td>
<td>Sets environment. ( default "local" )</td>
</tr>
</tbody>
</table>

### Deleting A Processed Job

Once you have processed a job, it must be deleted from the queue, which can be done via the delete method on the Job instance:

public function fire($job, $data)
{
    // Process the job...

    $job->delete();
}

### Releasing A Job Back Onto The Queue

If you wish to release a job back onto the queue, you may do so via the release method:

public function fire($job, $data)
{
    // Process the job...

    $job->release();
}

You may also specify the number of seconds to wait before the job is released:

$job->release(5);

### Checking The Number Of Run Attempts

If an exception occurs while the job is being processed, it will automatically be released back onto the queue. You may check the number of attempts that have been made to run the job using the attempts method:

if ($job->attempts() > 3)
{
    //
}

### Accessing The Job ID

You may also access the job identifier:

$job->getJobId();


## Queue Service

------

The Queue class provides a interface for variety of different queue handlers.

Push examle

```php
$c->load('queue');

$this->queue->channel('Logs');
$this->queue->push($job = 'Workers/Logger', $route = 'MyHostname.Logger', array('log' => array('debug' => 'Test')));
$this->queue->push($job = 'Workers/Logger', $route = 'MyHostname.Logger', array('message' => 'This is my message'));
```

Push example with delivery mode

```php
$c->load('queue');

$this->queue->channel('Logs');
$this->queue->push($job = 'Workers/Logger', $route = 'MyHostname.Logger', 
  $data = array('log' => 'test'), 
  $delay = 0, 
  $options = array(
    'delivery_mode' => 1,  // 2 = "Persistent", 1 = "Non-persistent"
    'content_type' => 'text/json'
  )
);
```

### Function Reference

------

#### $this->queue->channel(string $channelName);

Sets your queue exchange.

#### $this->queue->push(string $job, string $queueName, array $data, int $delay = 0, array $options = array());

Push a new job onto the queue.

#### $this->queue->pop(string $queueName);

Pop the next job off of the queue.

#### $this->queue->purgeQueue(string $queueName);

Clear the contents of a queue.

#### $this->queue->deleteQueue(string $queueName);

Delete a queue and its contents.


## Queue Service Libraries

### Job Class

Job class organize your jobs and send them to worker class. Look at Job Class documentation.

### Worker Class

Queue Worker class works in application background and do jobs using Job class. Look at Worker Class documentation.

### Listener Class

Listener class listen console parameters from Command Line Interface then launch the worker process using process library.


## Ubuntu Altında Supervisor Kurulumu

Supervisor is a client/server system that allows its users to control a number of processes on UNIX-like operating systems.

<a href="http://supervisord.org/">http://supervisord.org/</a>

```php
sudo apt-get install supervisor
```

Entering supervisor console

```php
supervisorctl
```

Help for all commands

```php
supervisor> help

default commands (type help <topic>):
=====================================
add    clear  fg        open  quit    remove  restart   start   stop  update 
avail  exit   maintail  pid   reload  reread  shutdown  status  tail  version
```

<a name="creating-first-worker"></a>

### İlk İşçimizi Yaratalım

Konfigürasyon klasörüne girin.

```php
cd /etc/supervisor/conf.d
```

List config files

```php
ll

total 16
drwxr-xr-x 2 root root 4096 May 31 13:19 ./
drwxr-xr-x 3 root root 4096 May 31 13:10 ../
-rw-r--r-- 1 root root  142 May  9  2011 README
```

Favori editörünüzler bir .conf dosyası yaratın.


```php
vi myMailer.conf
```

```php
[program:myMailer]
process_name=%(program_name)s_%(process_num)02d
command=php /var/www/project/task queue listen --worker:MAILER --job:EMAILQUEUE --memory:128 --delay=0 --timeout=3
numprocs=3
autostart=true
autorestart=true
stdout_logfile=/var/www/project/data/logs/myMailerProcess.log
stdout_logfile_maxbytes=1MB
```

<b>numprocs=3</b> means 3 workers will do same process at same time.

<a name="multiple-workers"></a>

#### Birden Fazla İşçiyi Çalıştırmak

You can create multiple programs for different jobs.

```php
vi myImages.conf
```

```php
[program:myImages]
process_name=%(program_name)s_%(process_num)02d
command=php /var/www/project/task queue listen --worker:IMAGERESIZER --job:IMAGEQUEUE --memory=256 
numprocs=10
autostart=true
autorestart=true
stdout_logfile=/var/www/project/data/logs/myImageResizerProcess.log
stdout_logfile_maxbytes=1MB
```

<a name="starting-all-workers"></a>

### Bütün İşçileri Başlatmak

```php
supervisorctl start all

myMailer_02: started
myMailer_01: started
myMailer_00: started

myImages_02: started
myImages_01: started
myImages_00: started
```

<a name="displaying-all-workers"></a>

### Bütün İşçileri Görüntülemek

```php
supervisorctl

myMailer:myMailer_00           RUNNING    pid 16847, uptime 0:01:41
myMailer:myMailer_01           RUNNING    pid 16846, uptime 0:01:41
myMailer:myMailer_02           RUNNING    pid 16845, uptime 0:01:41
```

<a name="stopping-all-workers"></a>

### Bütün İşçileri Durdurmak

```php
supervisorctl stop all

myMailer_02: stopped
myMailer_01: stopped
myMailer_00: stopped
```


### Following Logs

```php
supervisorctl maintail -f
```

### Automatically Run Supervisord on Startup

You need add supervisord to your auto init programs file it depends on your OS.

### Optimal number of threads per core

Technical articles tell us the optimal number of threads is equal to the number of cores in the machine.
Forexample if you have a machine with <b>16 core</b> processor thread value should be <b>numprocs=16</b>.

<a href="http://stackoverflow.com/questions/1718465/optimal-number-of-threads-per-core">Click to see tests: Optimal Number of Threads Per Core</a>


### Web Interface for Supervisord

It has a simple built-in web interface to help you manage processes, Look at below the article.

<a href="http://iambusychangingtheworld.blogspot.com.tr/2013/11/supervisord-using-built-in-web.html">http://iambusychangingtheworld.blogspot.com.tr/2013/11/supervisord-using-built-in-web.html</a>


## FailedJob Class

Queues allow you to defer the processing of a time consuming task, such as sending an e-mail, until a later time, thus drastically speeding up the web requests to your application.

**Tip:** You can extend below these classes and build your own.

### Initializing the Class

------

When the job is fail this class automatically initialize by Queue Worker class.


### Configuration Of Failed Jobs

------

Open your application config file and set your Failed Jobs Storage. Default is <b>Obullo\Queue\Failed\Storage\Database</b> class.

```php
/*
|--------------------------------------------------------------------------
| Queue
|--------------------------------------------------------------------------
*/
'queue' => array(

),
```

### SQL File

FailedJob database sql file is located in <b>Obullo/Queue/Failed/Database.sql</b> path.

```php
CREATE TABLE IF NOT EXISTS `failures` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `job_id` int(11) NOT NULL,
  `job_name` varchar(40) NOT NULL,
  `job_body` text NOT NULL,
  `job_attempts` int(11) NOT NULL DEFAULT '0',
  `error_level` tinyint(3) NOT NULL,
  `error_message` varchar(255) NOT NULL,
  `error_file` varchar(255) NOT NULL,
  `error_line` tinyint(4) NOT NULL,
  `error_trace` text NOT NULL,
  `error_xdebug` text NOT NULL,
  `error_priority` tinyint(4) NOT NULL,
  `failure_repeat` int(11) NOT NULL DEFAULT '0',
  `failure_first_date` int(11) NOT NULL COMMENT 'unix timestamp',
  `failure_last_date` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Failed Jobs' AUTO_INCREMENT=1 ;
```

### Function Reference

------

#### $this->class->method();

Comments


## Emergency Handler Class

Emergency handler send last failed job details to server admin using email library. You can extend this class and build your own.