
## QUEUE Logger Configuration

Some times application need to send some logging data to background for heavy <b>async</b> operations. Forexample to sending an email with smtp is very long process. So when we use <b>Email Handler</b> in application first we need to setup a <b>Queue Writer</b> for it.

### Terms

------

<table>
<thead>
<tr>
<th>Term</th>
<th>Description</th>
</thead>
<tbody>
<tr>
<td>Push handler</td>
<td>Allows copy of application log data and send them to another handler in the current page.</td>
</tr>
<tr>
<td>Queue writer</td>
<td>Allows send a new log data onto the queue. ( Uses queue service )</td>
</tr>
<tr>
<td>Queue Job handler</td>
<td>Listen queued logging jobs and consume them using <b>QueueLogger</b> class which is located in <b>app/Classes</b> folder.</td>
</tr>
</tbody>
</table>

### Push Handler Setup

------

Open your <kbd>app/Classes/Service/Logger.php</kbd> then update which handler you want to send log data onto the queue.
Please look at following example.

```php
<?php

namespace Service;

define('LOGGER_NAME', '.Logger.');
define('LOGGER_CHANNEL', 'Logs');
define('LOGGER_JOB', 'QueueLogger');

use Obullo\Log\Handler\DisabledHandler,
    Obullo\Log\Handler\FileHandler,
    Obullo\Log\Handler\MongoHandler,
    Obullo\Log\Handler\EmailHandler,
    Obullo\Log\Logger as OLogger,
    Obullo\Log\Writer\FileWriter,
    Obullo\Log\Writer\MongoWriter,
    Obullo\Log\Writer\EmailWriter,
    Obullo\Log\Writer\QueueWriter;

/**
 * Log Service
 *
 * @category  Service
 * @package   Logger
 * @author    Obullo Framework <obulloframework@gmail.com>
 * @copyright 2009-2014 Obullo
 * @license   http://www.gnu.org/licenses/gpl-3.0.html GPL Licence
 * @link      http://obullo.com/docs/services
 */
Class Logger implements ServiceInterface
{
    /**
     * Registry
     *
     * @param object $c container
     * 
     * @return void
     */
    public function register($c)
    {
        $c['logger'] = function () use ($c) {

            if ($c->load('config')['log']['enabled'] == false) {  // Use disabled handler if config disabled.
                return new Disabled;
            }
            $logger = new OLogger($c, $c->load('config')['log']);
            /*
            |--------------------------------------------------------------------------
            | Filters
            |--------------------------------------------------------------------------
            | Register your filters here
            */
            $logger->registerFilter('priority', 'Obullo\Log\Filter\Priority');
            /*
            |--------------------------------------------------------------------------
            | File Handler
            |--------------------------------------------------------------------------
            */
            $FILE_HANDLER = function () use ($c) { 
                    return new FileHandler(
                        $c,
                        new FileWriter(
                            $c->load('config')['log']
                        )
                    );
            };
            /*
            |--------------------------------------------------------------------------
            | Mongo Handler
            |--------------------------------------------------------------------------
            */
            $MONGO_HANDLER = function () use ($c) { 
                return new MongoHandler(
                    $c,
                    new MongoWriter(
                        $c->load('service/provider/mongo', 'db'),  // Mongo client instance
                        array(
                        'database' => 'db',
                        'collection' => 'logs',
                        'save_options' => null
                        )
                    )
                );
            };
            /*
            |--------------------------------------------------------------------------
            | Email Handler
            |--------------------------------------------------------------------------
            */
            $EMAIL_HANDLER = function () use ($c) { 
                return new EmailHandler(
                    $c,
                    new QueueWriter(
                        $c->load('service/queue'),
                        array(
                            'channel' =>  LOGGER_CHANNEL,
                            'route' => gethostname(). LOGGER_NAME .'Email',
                            'job' => LOGGER_JOB,
                            'delay' => 0,
                        )
                    )
                );
            };
            /*
            |--------------------------------------------------------------------------
            | Writers
            |--------------------------------------------------------------------------
            | Primary file writer should be available on local server.
            */
            $logger->addWriter(LOGGER_FILE, $FILE_HANDLER)->priority(2);
            /*
            |--------------------------------------------------------------------------
            | Handlers
            |--------------------------------------------------------------------------
            | Add your available log handlers
            */
            $logger->addHandler(LOGGER_MONGO, $MONGO_HANDLER)->priority(1);
            $logger->addHandler(LOGGER_EMAIL, $EMAIL_HANDLER)->priority(2)->filter('priority', array(LOG_ERR, LOG_CRIT, LOG_ALERT, LOG_WARNING, LOG_EMERG));
            /*
            |--------------------------------------------------------------------------
            | Removes file handler and uses second handler as primary 
            | in "production" env.
            |--------------------------------------------------------------------------
            */
            if (ENV == 'prod') {
                $logger->removeWriter(LOGGER_FILE);
                $logger->removeHandler(LOGGER_MONGO);
                $logger->addWriter(LOGGER_MONGO, $MONGO_HANDLER);  //  your production log writer
            }
            return $logger;
        };
    }
}

// END Logger class

/* End of file Logger.php */
/* Location: .classes/Service/Logger.php */
```

### Queue Writer Setup

------

Below the example replace your file writer with Queue.

```php
<?php
$FILE_HANDLER = function () use ($c) { 
        return new FileHandler(
            $c,
            new QueueWriter(
                $c->load('service/queue'),
                array(
                    'channel' =>  LOGGER_CHANNEL,
                    'route' => gethostname(). LOGGER_NAME .'File',
                    'job' => LOGGER_JOB,
                    'delay' => 0,
                )
            )
            // new FileWriter(
            //     $c->load('config')['log']
            // )
        );
};
```

### Job Handler Setup

------

QueueLogger class listen your logger queue data then consume them using <b>Job Handlers</b>.

### Available Job Process Handlers

* File
* Mongo
* Email


```php
<?php

use Obullo\Queue\Job;

/**
 * Queue Logger
 *
 * @category  Queue
 * @package   QueueLogger
 * @author    Obullo Framework <obulloframework@gmail.com>
 * @copyright 2009-2014 Obullo
 * @license   http://www.gnu.org/licenses/gpl-3.0.html GPL Licence
 * @link      http://obullo.com/docs/queue
 */
Class QueueLogger
{
    /**
     * Container
     * 
     * @var object
     */
    public $c;

    /**
     * Environment
     * 
     * @var string
     */
    public $env;

    /**
     * Constructor
     * 
     * @param object $c   container
     * @param string $env environments
     */
    public function __construct($c, $env)
    {
        $this->c = $c;
        $this->env = $env;
    }

    /**
     * Fire the job
     * 
     * @param Job   $job  object
     * @param array $data data array
     * 
     * @return void
     */
    public function fire(Job $job, $data)
    {
        $exp = explode('.', $job->getName());  // File, Mongo, Email ..
        $handlerName = ucfirst(end($exp));
        $JobHandlerClass = '\\Obullo\Log\Queue\JobHandler\JobHandler'.$handlerName;
        $JobHandlerName = strtolower($handlerName);

        switch ($JobHandlerName) {
        case LOGGER_FILE:
            $writer = new $JobHandlerClass($this->c);
            break;
        case LOGGER_EMAIL:
            $writer = new $JobHandlerClass(
                $this->c,
                array(
                'from' => '<noreply@example.com> Server Admin',
                'to' => 'example@example.com',
                'cc' => '',
                'bcc' => '',
                'subject' => 'Server Logs',
                'message' => 'Detailed logs here --> <br /> %s',
                )
            );
            break;  
        case LOGGER_MONGO:
            $writer = new $JobHandlerClass($this->c,
                array(
                'database' => 'db',
                'collection' => 'logs',
                'save_options' => null,
                'format' => array(
                        'context' => 'array',  // json
                        'extra'   => 'array'   // json
                    ),
                )
            );
            break;
        default:
            $writer = null;
            break;
        }
        if ($writer != null) {
            $writer->write($data);  // Do job
            $writer->close();
            $job->delete();  // Delete job from queue
        }
    }

}

/* End of file QueueLogger.php */
/* Location: .app/classes/QueueLogger.php */
```

### Listing Queues

```php
php task queue list --channel=Logs --route=MyHostname.Logger.Email
```

```php
   			______  _            _  _
           |  __  || |__  _   _ | || | ____
           | |  | ||  _ || | | || || ||  _ |
           | |__| || |_||| |_| || || || |_||
           |______||____||_____||_||_||____|

            Welcome to Task Manager (c) 2014
    You are running $php task queue command which is located in app / tasks folder.

Following queue data ...

Channel : Logs
Route   : MyHostname.Logger.Email
------------------------------------------------------------------------------------------
 Job ID | Job Name             | Data 
------------------------------------------------------------------------------------------
 1      | QueueLogger          |  {"type":"app","record":"[2014-08-15 19:11:50] system.notice: --> test email notice !   \n[2014-08-15 19:11:50] system.alert: --> test email alert  array (  'test' => 'example data 123',) \n"}

```


### Listening Queues from Command Line

Open your console and write below the command

```php
php task queue listen --channel=Logs --route=MyHostname.Logger.Email --delay=0 --memory=128 --timeout=0 --sleep=0 --maxTries=0
```

Above the command listen <b>Logs</b> channel and <b>Email</b> queue.

### Changing Route

You can listen a <b>different route</b> by changing the route name like below.

```php
php task queue listen --channel=Logs --route=MyHostname.Logger.File --delay=0 --memory=128 --timeout=0 --sleep=0 --maxTries=0
php task queue listen --channel=Logs --route=MyHostname.Logger.Mongo --delay=0 --memory=128 --timeout=0 --sleep=0 --maxTries=0
```

### Enabling Debbuger

Put <b>--debug</b> end of your command with debug variable you can enable console debug to see errors and queues.

```php
php task queue listen --channel=Logs --route=MyHostname.Logger.Email --delay=0 --memory=128 --debug=1
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