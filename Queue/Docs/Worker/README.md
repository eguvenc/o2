
## Worker Class

Queue Worker class works in application background and do jobs using Job class.

### Initializing the Class

------

```php
$c->load('queue/worker as worker');
$this->worker->method();
```

Example worker

```php
<?php

namespace Workers;

use Obullo\Queue\Job,
    Obullo\Queue\JobInterface;

 /**
 * Queue Mailer
 *
 * @category  Queue
 * @package   QueueMailer
 * @author    Obullo Framework <obulloframework@gmail.com>
 * @copyright 2009-2014 Obullo
 * @license   http://www.gnu.org/licenses/gpl-3.0.html GPL Licence
 * @link      http://obullo.com/docs/queue
 */
Class QueueMailer implements JobInterface
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

    	try {

    		// Do your job if success then 
    		// delete it from the queue.
    		
    		$job->delete();

    	} catch (Exception $e) {


    		// Catch the errors
    	}

    }
}

/* End of file QueueMailer.php */
/* Location: .app/classes/QueueMailer.php */
```

### Function Reference

------

#### $this->worker->init($channel = 'Logs', $route = 'myHostname.Log.Driver', $memory = 128, $delay = 0, $timeout = 3, $sleep = 0, $maxTries = 3, $debug = 0, $env = 'production');

#### $this->worker->pop();

#### $this->worker->doJob();