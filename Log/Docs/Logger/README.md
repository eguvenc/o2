
## Logger Class

------

The Logger class assists you to <kbd>write messages</kbd> to your log handlers. The logger class use php SplPriorityQueue class to manage your handler proirities.

**Note:** This class defined as service in your app/clases/Service folder. The <b>logger</b> package uses <kbd>Disabled</kbd> handler as default.

### Available Log Hanlers

* DisabledHandler
* FileHandler
* MongoHandler
* SyslogHandler
* EmailHandler

### Syslog Constants

```php
/*
|--------------------------------------------------------------------------
| Log Constants
|--------------------------------------------------------------------------
| @see Syslog Protocol http://tools.ietf.org/html/rfc5424
|
| Constants:
|
| 0  LOG_EMERG: System is unusable
| 1  LOG_ALERT: Action must be taken immediately
| 2  LOG_CRIT: Critical conditions
| 3  LOG_ERR: Error conditions
| 4  LOG_WARNING: Warning conditions
| 5  LOG_NOTICE: Normal but significant condition
| 6  LOG_INFO: Informational messages
| 7  LOG_DEBUG: Debug-level messages
*/
```

### Log Severities:

<table class="span9">
<thead>
<tr>
<th>Severity</th>
<th>Level</th>
<th>Constant</th>
<th>Desciription</th>
</tr>
</thead>
<tbody>
<tr>
<td>emergency</td>
<td>0</td>
<td>LOG_EMERG</td>
<td>Emergency: System is unusable.</td>
</tr>

<tr>
<td>alert</td>
<td>1</td>
<td>LOG_ALERT</td>
<td>Action must be taken immediately. Example: Entire website down, database unavailable, etc. This should trigger the SMS alerts and wake you up.</td>
</tr>

<tr>
<td>critical</td>
<td>2</td>
<td>LOG_CRIT</td>
<td>Critical conditions. Example: Application component unavailable, unexpected exception.</td>
</tr>

<tr>
<td>error</td>
<td>3</td>
<td>LOG_ERR</td>
<td>Runtime errors that do not require immediate action but should typically be logged and monitored.</td>
</tr>

<tr>
<td>warning</td>
<td>4</td>
<td>LOG_WARNING</td>
<td>Exceptional occurrences that are not errors. Examples: Use of deprecated APIs, poor use of an API, undesirable things that are not necessarily wrong.</td>
</tr>

<tr>
<td>notice</td>
<td>4</td>
<td>LOG_NOTICE</td>
<td>Normal but significant events.</td>
</tr>

<tr>
<td>info</td>
<td>6</td>
<td>LOG_INFO</td>
<td>Interesting events. Examples: User logs in, SQL logs, Application Benchmarks.</td>
</tr>

<tr>
<td>debug</td>
<td>7</td>
<td>LOG_DEBUG</td>
<td>Detailed debug information.</td>
</tr>
</tbody>
</table>

### Enable / Disable Logger

As default framework comes with logging disabled <kbd>false</kbd>. You can enable logger setting <kbd>enabled to true.</kbd>

On your local environment config file  set <kbd>threshold</kbd> level <b>1</b> to <b>7</b>.

```php
<?php
'log' =>   array(
        'enabled'   => true,  // On / Off logging.
        'output'    => false, // On / Off debug html output. When it is enabled all handlers will be disabled.
        'channel'   => 'system', // Default channel name should be general.
        'line'      => '[%datetime%] %channel%.%level%: --> %message% %context% %extra%\n',
        'path'      => array(
            'app'   => 'data/logs/app.log',    // File handler application log path
            'cli'   => 'data/logs/cli.log', // File handler cli log path  
            'ajax'  => 'data/logs/ajax.log', // File handler ajax log path
            'worker'  => 'data/logs/worker.log', // Queue workers log path  
        ),
        'format'    => 'Y-m-d H:i:s', // Date format
        'queries'   => true,          // If true "all" SQL Queries gets logged.
        'benchmark' => true,          // If true "all" Application Benchmarks gets logged.
),
```
#### Explanation of Settings:

* <b>enabled</b> - On / Off logging
* <b>debug</b> - On / Off html output, logger gives html output bottom of the current page.
* <b>channel</b> - Default channel name should be general.
* <b>line</b> - Logging line format for line based handlers.
* <b>path</b> - File handler paths
* <b>format</b> - Date format for each records.
* <b>queries</b> - If true all Database SQL Queries gets logged.
* <b>benchmark</b> - If true all framework benchmarks gets logged.


#### $this->logger->level($message = string,  $context = array(), $priority = 0);

First choose your channel and set log level, you can send your additinonal context data using second parameter.

### Logging

```php
<?php
$this->logger->channel('security');
$this->logger->alert('Possible hacking attempt !', array('username' => $username));
```

### Priorities

```php
<?php
$this->logger->alert('Alert', array('username' => $username), 3);
$this->logger->notice('Notice', array('username' => $username), 2);
$this->logger->notice('Another Notice', array('username' => $username), 1);
```

### Push

Below the example load push handler then do filter for LOG_ALERT levels. Logger only writes alert level logs.

```php
<?php
$this->logger->load(LOGGER_EMAIL)->filter('priority', array(LOG_ALERT));
$this->logger->channel('security');               
$this->logger->alert('Possible hacking attempt !', array('username' => $username));
$this->logger->push(LOGGER_MONGO);
```
or you can use multiple push handlers.

```php
<?php
$this->logger->load(LOGGER_EMAIL)->filter('priority.notIn', array(LOG_DEBUG, LOG_INFO, LOG_NOTICE));
$this->logger->load(LOGGER_MONGO);  

$this->logger->channel('security');
$this->logger->alert('Something went wrong !', array('username' => $username));

$this->logger->push(LOGGER_EMAIL);
$this->logger->push(LOGGER_MONGO); // Sends all log level data to mongo handler

$this->logger->info('User login attempt.', array('username' => $username));  // Continue logging with default handler
```

<b>IMPORTANT:</b> For a live site you'll usually only enable for LOG_EMERG,LOG_ALERT,LOG_CRIT,LOG_ERR,LOG_WARNING,LOG_NOTICE levels to be logged otherwise your log files will fill up very fast. This feature is configurable from your logger service.

## Logger Service

### Primary Handler

Open your <b>app/classes/Service/Logger.php</b> then switch mongo database as a primary handler this will replace your "file" handler as "mongo".

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
```

* TIP: If you have a high traffic web site use one log handler or use QUEUE handler as primary for best performance.


### Handler Priority

Priority method sets your handler priority.

```php
<?php
$logger->addWriter(LOGGER_FILE, $FILE_HANDLER)->priority(2);
```

High numbers means your handler important than others.


### Global Filters

If you we want to use a filter first you need to register it to a class with registerFilter(); method.

An example prototype:

```php
$logger->registerFilter('class.method', 'Namespace/Class');
```

```php
<?php
$logger->registerFilter('priority', 'Obullo\Log\Filter\Priority');
```
or you can define our own 

```php
<?php
$logger->registerFilter('filtername', 'MyNamespace/MyFilterClass');
```

Then you can use your filters.

```php
<?php
$logger->addHandler(LOGGER_EMAIL, $EMAIL_HANDLER)->priority(2)->filter('priority', array(LOG_NOTICE, LOG_ALERT));
```

or you can use methods

```php
<?php
$logger->addHandler(LOGGER_EMAIL, $EMAIL_HANDLER)->priority(2)->filter('priority.notIn', array(LOG_DEBUG));
```
Above the example executes <b>Priority</b> class <b>notIn</b> method.

### Page Filters

Below the example we create a log filter for <b>hello_world</b> page then we push log data to mongo. And the LOGGER_MONGO page filter <b>remove the debug level</b> logs.

```php
<?php

/**
 * $app hello_world
 * 
 * @var Controller
 */
$app = new Controller(
    function ($c) {
        $c->load('view');

        $this->logger->load(LOGGER_MONGO)->filter('priority.notIn', array(LOG_DEBUG)); // Do not write debugs.

        $this->logger->info('Hello World !');
        $this->logger->notice('Hello Notice !');
        $this->logger->alert('Hello alert !');

        $this->logger->push(LOGGER_MONGO);
    }
);
```

**Note:** You can create your own filters in your <kbd>app/classes/</kbd> folder then you need set it with <b>$logger->registerFilter('myPriority', 'Filters\MyfilterClass');</b> method in your logger service.

#### Displaying Logs

You can follow the all log messages using below the command.

```php
php task log
```
You can set the log level filter using the level argument.

```php
php task log level info
```

This command display only log messages which are flagged as debug.

```php
php task log level debug
```

#### Clear All Log Data

```php
php task clear
```

### Handlers Reference

------

#### $this->logger->load(string $handler = LOGGER_EMAIL);

Load a log handler for push method. Handler constants are defined in your root constants file.

#### $this->logger->addWriter(string $name, Closure $handler);

Add a log writer.

#### $this->logger->removeWriter(string $name);

Remove a log writer.

#### $this->logger->addHandler(string $name, Closure $handler);

Add a push handler.

#### $this->logger->removeHandler(string $name);

Remove a push handler.

#### $this->logger->registerFilter(string $name, string $namespace);

Register your filter namespace, class and methods.

#### $this->logger->filter(string $name, array $params = array());

Execute your filter before releated method.

#### $this->logger->push(string $handler = LOGGER_MONGO, $threshold = null, integer $priority = 0);

Push current page log data to log handlers.

#### $this->logger->printDebugger(string $handler = LOGGER_FILE);

On / Off debug html output. When it is enabled all push handlers will be disabled.


### Function Reference

------

#### $this->logger->channel(string $channel);

Sets log channel.

#### $this->logger->emergency(string $message = '', $context = array(), integer $priority = 0);

Create <b>LOG_EMERG</b> level log message.

#### $this->logger->alert(string $message = '', $context = array(), integer $priority = 0);

Create <b>LOG_ALERT</b> level log message.

#### $this->logger->critical(string $message = '', $context = array(), integer $priority = 0);

Create <b>LOG_CRIT</b> level log message.

#### $this->logger->error(string $message = '', $context = array(), integer $priority = 0);

Create <b>LOG_ERROR</b> level log message.

#### $this->logger->warning(string $message = '', $context = array(), integer $priority = 0);

Create <b>LOG_WARNING</b> level log message.

#### $this->logger->notice(string $message = '', $context = array(), integer $priority = 0);

Create <b>LOG_NOTICE</b> level log message.

#### $this->logger->info(string $message = '', $context = array(), integer $priority = 0);

Create <b>LOG_INFO</b> level log message.
    
#### $this->logger->debug(string $message = '', $context = array(), integer $priority = 0);

Create <b>LOG_DEBUG</b> level log message.
