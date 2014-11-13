
## Queue Logger Configuration

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

### Setup

------

Open your <kbd>app/Classes/Log/Env/LocalLogger.php</kbd> then update which handler you want to send log data onto the queue.
Please look at following example.

```php
<?php

namespace Log\Env;

use Service\ServiceInterface,
    Obullo\Log\LogService,
    Obullo\Log\Handler\NullHandler;

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
Class LocalLogger implements ServiceInterface
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

            if ( ! $c->load('config')['log']['enabled']) {  // Use null handler if config disabled.
                return new NullHandler;
            }
            $log = new LogService($c, $c->load('config')['log']);
            /*
            |--------------------------------------------------------------------------
            | Register Filters
            |--------------------------------------------------------------------------
            */
            $log->registerFilterPath('Log\Filters');
            $log->registerFilter('priority', 'PriorityFilter')->registerFilter('input', 'InputFilter');
            /*
            |--------------------------------------------------------------------------
            | Register Handlers
            |--------------------------------------------------------------------------
            */
            $log->registerHandlerPath('Log\QueueLogger');
            $log->registerHandler('file', 'FileHandler', 5)->registerHandler('mongo', 'MongoHandler', 4)->registerHandler('email', 'EmailHandler', 3);
            /*
            |--------------------------------------------------------------------------
            | Add Writer - Primary file writer should be available on local server.
            |--------------------------------------------------------------------------
            */
            if (defined('STDIN')) { 
                $log->addWriter('file')->filter('priority.notIn', array(LOG_DEBUG, LOG_INFO)); // Cli
            } else {
                $log->addWriter('file')->filter('priority.notIn', array(LOG_INFO)); // Http
            }
            return $log;
        };
    }
}

// END LocalLogger class

/* End of file LocalLogger.php */
/* Location: .classes/Log/Env/LocalLogger.php */
```

### Queue Handler Setup

------

Below the example setup file handler and priority.

```php
<?php
$log->registerHandler('file', 'FileHandler', $priority = 4);
```

### Workers ( Job Handler ) Setup

------

QueueLogger class listen your <b>logger queue</b> data then consume them using <b>Job Handlers</b>.

### Available Job Process Handlers

* File
* Mongo
* Email


```php
<?php

namespace Workers;

use Obullo\Queue\Job,
    Obullo\Queue\JobInterface;

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
Class QueueLogger implements JobInterface
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
        $JobHandlerClass = '\\Obullo\QueueLogger\JobHandler\JobHandler'.$handlerName;
        $JobHandlerName = strtolower($handlerName);

        switch ($JobHandlerName) {

        case 'file':
            $handler = new $JobHandlerClass($this->c, $this->c->load('config')['log']);
            break;

        case 'email':
            $handler = new $JobHandlerClass(
                $this->c,
                $this->c->load('service/mailer'),
                array(
                    'from' => '<noreply@example.com> Server Admin',
                    'to' => 'obulloframework@gmail.com',
                    'cc' => '',
                    'bcc' => '',
                    'subject' => 'Server Logs',
                    'message' => 'Detailed logs here --> <br /> %s',
                )
            );
            break;

        case 'mongo':
            $handler = new $JobHandlerClass($this->c,
                $this->c->load('service/provider/mongo', 'db'),
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
            $handler = null;
            break;
        }

        if ($handler != null) {
            $handler->write($data);  // Do job
            $handler->close();

            $job->delete();  // Delete job from queue
        }
    }

}

/* End of file QueueLogger.php */
/* Location: .app/classes/Workers/QueueLogger.php */
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


### Installing Framework as a Completely Worker Application

If you want to setup a completely worker application open config file and set "queue => workers => true" otherwise "AbstractHandler" class will 
not allow your data send to writers.

```php
'queue' => array(
    'workers' => array(
        'logging' => true  // On / Off Queue workers logging functionality.
    ), 
)
```

This enables logging process of workers ( background logging ) and disables http logging. It is normally disabled for normal applications which they work with http requests.