
## Cli Parser Class

------

Cli class helps you <b>parse</b> command line parameters and arguments.

Example commmand line parameters

```php
php task queue list
```

Example commmand line argument

```php
php task queue list --delete
```

Example commmand line parameters and arguments

```php
php task queue listen --channel=Logger --route=logger --delay=0 --memory=128
```

**Note:** Framework only accepts dashes <b>(--)</b> to parse arguments.

### Initializing the Class

------

```php
$this->c->load('cli/parser as parser');
$this->parser->method();
```

### Example Command

Below the command run <b>app/tasks/queue</b> controller.

```php
php task queue list --channel=Logger --route=logger --delay=0 --memory=128
```

```php
<?php

/**
 * Queue controller
 */
Class Queue extends Controller
{
    /**
     * Index
     * 
     * @return void
     */
    public function index()
    {
        $this->c->load('queue/listener', func_get_args());
    }
}
```

```php
<?php

namespace Obullo\Queue;

use Obullo\Process\Process;

/**
 * Queue Listener Class
 * 
 * @category  Queue
 * @package   Queue
 * @author    Obullo Framework <obulloframework@gmail.com>
 * @copyright 2009-2014 Obullo
 * @license   http://www.gnu.org/licenses/gpl-3.0.html GPL Licence
 * @link      http://obullo.com/package/queue
 */
Class Listener
{
    /**
     * Listen Queue
     *
     * php task queue listen --channel=Logger --route=Server1.Logger --memory=128 --delay=0 --timeout=3 --sleep=0 --maxTries=0 --debug=0 --env=prod
     * 
     * @return void
     */
    public function listenQueue()
    {
        $channel = $this->parser->argument('channel', null); // Sets queue exchange
        $route = $this->parser->argument('route', null);     // Sets queue route key ( queue name )
        $memory = $this->parser->argument('memory', 128);    // Sets maximum allowed memory for current job.
        $delay = $this->parser->argument('delay', 0);        // Sets job delay interval
        $timeout = $this->parser->argument('timeout', 0);    // Sets time limit execution of the current job.
        $sleep = $this->parser->argument('sleep', 0);        // If we have not job on the queue sleep the script for a given number of seconds.
        $maxTries = $this->parser->argument('maxTries', 0);  // If job attempt failed we push and increase attempt number.
        $debug = $this->parser->argument('debug', 0);        // Enable / Disabled console debug.
        $env = $this->parser->argument('env', 'local');      // Sets environment for current worker.
        $project = $this->parser->argument('project', 'default');  // Sets project name for current worker. 
        $var = $this->parser->argument('var', null);         // Sets your custom variable
        
        $this->emptyControl($channel, $route);

        $cmd = "php task worker --channel=$channel --route=$route --memory=$memory --delay==$delay --timeout=$timeout --sleep=$sleep --maxTries=$maxTries --debug=$debug --env=$env --project=$project --var=$var";

        $process = new Process($cmd, ROOT, null, null, $timeout);
        while (true) {
            $process->run();
            if ($debug == 1) {
                echo $process->getOutput();
            }
        }
    }
}

// END Listener class

/* End of file Listener.php */
/* Location: .Obullo/Queue/Listener.php */
```
### Function Reference

------

#### $this->parser->parse(func_get_args())

Parse valid function parameters.

#### $this->parser->segment($number)

Gets valid command line segment.

#### $this->parser->segmentArray()

Returns to all segments.

#### $this->parser->argument($key)

Gets valid command line argument.

#### $this->parser->argumentArray()

Returns to all arguments.