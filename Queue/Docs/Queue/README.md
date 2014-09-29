
## Queues

Queues allow you to defer the processing of a time consuming task, such as sending an e-mail, until a later time, thus drastically speeding up the web requests to your application.

### Initializing the Class

------

```php
$c->load('service/queue');
$this->queue->method();
```

## Installing Your AMQP Client On Ubuntu

------

To install the AMQP PHP extension you should first choose your AMQP handler. We use rabbitMQ in this example.

## Example Installation of RabbitMQ

You must first install the <a href="https://github.com/alanxz/rabbitmq-c">librabbitmq</a> library. You will need the 0-9-1 version of the rabbitmq-c client library, linked to the rabbitmq-codegen version that corresponds to the version of the broker you are using. Use the following steps to download and install the library:

There are a lot of queue handlers which they use AMQP interface below the example we install and test RabbitMQ.

### Download the rabbitmq-c library

```php
git clone git://github.com/alanxz/rabbitmq-c.git
cd rabbitmq-c
```

### Enable and update the codegen git submodule

```php
git submodule init
git submodule update
```

### Configure, compile and install

```php
autoreconf -i && ./configure && make && sudo make install
```

```php
sudo apt-get install librabbitmq-dev
```

```php
pecl install amqp
```

You should add "extension=amqp.so" to your php.ini extension

```php
cd /etc/php5/mods-available
```

Create ini file using your text editor

```php
vim amqp.ini
```

Paste below the lines

```php
; configuration for php AMQP module
; priority=20
extension=amqp.so
```

Enable amqp extension

```php
php5enmod amqp
```

```php
sudo service apache2 restart
```

### Installing RabitMQ and Management Plugin

```php
sudo apt-get remove rabbitmq-server
sudo apt-get install python-software-properties
sudo add-apt-repository "deb http://www.rabbitmq.com/debian/ testing main"
wget http://www.rabbitmq.com/rabbitmq-signing-key-public.asc
sudo apt-key add rabbitmq-signing-key-public.asc
sudo apt-get update
sudo apt-get install rabbitmq-server
sudo service rabbitmq-server start
sudo rabbitmq-plugins enable rabbitmq_management
sudo service rabbitmq-server restart
```

## Console Tips

Php <b>task</b> file which is located in your project root that helps you to run your console tasks.

```php
+ app
+ assets
+ obullo
+ public
  components.php
  .
  .
  task
```

In your project root open your shell console and type

```php
php task help
```

Each task command resolves a task <b>controller</b>.

```php
- app
	+ config
	- tasks
		- controller
			help.php
```

### Logs

In your project root open your shell console and type

#### Following Logs

```php
php task log
```

Above the command follow your application logs by reading your <b>app/data/logs/app.log</b> file.

<b>Log</b> segment is a controller that is located in your <b>app/tasks</b> folder.

```php
- app
	+ config
	- tasks
		- controller
			clear.php
			log.php
```

#### Clear Logs

In your project root open your shell console and type

```php
php task clear
```

Above the command deletes log files from <b>app/data/logs</b> folder.

<b>Clear</b> segment is a controller that is located in your <b>app/tasks</b> folder.

```php
- app
	+ config
	- tasks
		- controller
			clear.php
			log.php
```

### Hello Queue !

This tutorial simply demonstrate <b>pushing your data</b> to queue using your queue handler.

```php
<?php

/**
 * $app hello_world
 * 
 * @var Controller
 */
$app = new Controller(
    function ($c) {
        $c->load('service/queue');
        $this->queue->channel('Log');
        $this->queue->push('SendLog', 'Server1.logger', array('log' => array('debug' => 'Test')));
        $this->queue->push('SendLog', 'Server1.logger', array('message' => 'This is my message'));
        $this->queue->push('SendLog', 'Server1.logger', array('log' => array('debug' => 'Test')));
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

### Following Queue Data

To follow your Queue data <b>Open your console and type</b>

```
php task queue list --channel=Log --route=Server1.logger
```

Then you will see your Queue data here

```
            ______  _            _  _
           |  __  || |__  _   _ | || | ____
           | |  | ||  _ || | | || || ||  _ |
           | |__| || |_||| |_| || || || |_||
           |______||____||_____||_||_||____|

            Welcome to Task Manager You are running $php task queue

Following queue data ...

Channel : Log
Route   : localhost.logger
------------------------------------------------------------------------------------------
 Job ID | Job Name             | Data 
------------------------------------------------------------------------------------------
 1      | SendLog              | {"log":{"debug":"test"}}
 2      | SendLog              | {"message":"this is my message"}
 3      | SendLog              | {"log":{"debug":"test"}}
```


## Queue Control ( Command Line Interface )

------

Queue control allow to us <b>display, listen, delete</b> the queues also do <b>test</b> for workers.

### Display Queues

```php
php task queue list --channel=Logs --route=locahost.logger
```

### Clear Queue Data

```php
php task queue list --channel=Logs --route=locahost.logger --clear
```

### Running Your Queue Workers

```php
php task queue listen --channel=Logs --route=localhost.logger --delay=0 --memory=128 --timeout=0 --sleep=3 --maxTries=0 --debug=1
```

### Console Parameters

<table>
<thead>
<tr>
<th>Parameter</th>
<th>Description</th>
</thead>
<tbody>
<tr>
<td>--channel</td>
<td>Sets queue exchange ( Channel ).</td>
</tr>
<tr>
<td>--route</td>
<td>Sets queue name.</td>
</tr>
<tr>
<td>--delay</td>
<td>Sets delay time for uncompleted jobs.</td>
</tr>
<tr>
<td>--memory</td>
<td>Sets maximum allowed memory for current job.</td>
</tr>
<tr>
<td>--timeout</td>
<td>Sets time limit execution of the current job.</td>
</tr>
<tr>
<td>--sleep</td>
<td>If we have not job on the queue sleep the script for a given number of seconds.</td>
</tr>
<tr>
<td>--maxTries</td>
<td>If we have not job on the queue sleep the script for a given number of seconds.</td>
</tr>
<tr>
<td>--debug</td>
<td>Debug queue output and any possible exceptions. ( Designed for local environment  )</td>
</tr>
</tbody>
</table>


## Queue Service

------

The Queue class provides a interface for variety of different queue handlers.

Push examle

```php
<?php
$c->load('service/queue');

$this->queue->channel('Logs');
$this->queue->push($route = 'MyHostname.Logger.File', $job = 'SendLog', array('log' => array('debug' => 'Test')));
$this->queue->push($route = 'MyHostname.Logger.Email', $job = 'SendLog', array('message' => 'This is my message'));
```

Push example with delivery mode

```php
<?php
$c->load('service/queue');

$this->queue->channel('Logs');
$this->queue->push($route = 'MyHostname.Logger.File', $job = 'SendLog',
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

#### $this->queue->push(string $queueName, string $job, array $data, int $delay = 0, array $options = array());

Push a new job onto the queue.

#### $this->queue->pop(string $queueName);

Pop the next job off of the queue.

#### $this->queue->purgeQueue(string $channel, string $queueName);

Clear the contents of a queue.

#### $this->queue->deleteQueue(string $channel, string $queueName);

Delete a queue and its contents.


## Queue Service Libraries

### Job Class

Job class organize your jobs and send them to worker class. Look at Job Class documentation.

### Worker Class

Queue Worker class works in application background and do jobs using Job class. Look at Worker Class documentation.

### Listener Class

Listener class listen console parameters from Command Line Interface then launch the worker process using process library.


## Using Supervisor for Multitasks ( Multi Thread )

------

Supervisor is a client/server system that allows its users to control a number of processes on UNIX-like operating systems.

<a href="http://supervisord.org/">http://supervisord.org/</a>

## Installing Supervisor On Ubuntu

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

### Config Folder

Enter to config folder

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

### Creating your first worker

```php
vi myMailer.conf
```

```php
[program:myMailer]
process_name=%(program_name)s_%(process_num)02d
command=php /var/www/project/task queue listen --channel:MAILER --route:EMAILQUEUE --memory:128 --delay=0 --timeout=3
numprocs=3
autostart=true
autorestart=true
stdout_logfile=/var/www/project/data/logs/myMailerProcess.log
stdout_logfile_maxbytes=1MB
```

<b>numprocs=3</b> means 3 workers will do same process at same time.

#### Creating multiple workers

You can create multiple programs for different jobs.

```php
vi myImages.conf
```

```php
[program:myImages]
process_name=%(program_name)s_%(process_num)02d
command=php /var/www/project/task queue listen --channel:IMAGERESIZER --route:IMAGEQUEUE --memory=256 
numprocs=10
autostart=true
autorestart=true
stdout_logfile=/var/www/project/data/logs/myImageResizerProcess.log
stdout_logfile_maxbytes=1MB
```

### Starting all workers

```php
supervisorctl start all

myMailer_02: started
myMailer_01: started
myMailer_00: started

myImages_02: started
myImages_01: started
myImages_00: started
```

### Displaying Process

```php
supervisorctl

myMailer:myMailer_00           RUNNING    pid 16847, uptime 0:01:41
myMailer:myMailer_01           RUNNING    pid 16846, uptime 0:01:41
myMailer:myMailer_02           RUNNING    pid 16845, uptime 0:01:41
```

### Stopping all workers

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