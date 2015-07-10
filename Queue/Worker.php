<?php

namespace Obullo\Queue;

use Exception;
use ErrorException;
use Obullo\Cli\Cli;
use Obullo\Queue\Job;
use Obullo\Log\Logger;
use Obullo\Cli\Console;
use Obullo\Container\ContainerInterface;

/**
 * Queue Worker Class
 *
 * Worker consumes queue data and do jobs using your job handler class
 * 
 * @category  Queue
 * @package   Queue
 * @author    Obullo Framework <obulloframework@gmail.com>
 * @copyright 2009-2014 Obullo
 * @license   http://opensource.org/licenses/MIT MIT license
 * @link      http://obullo.com/package/queue
 */
class Worker
{
    /**
     * Container
     * 
     * @var c
     */
    protected $c;

    /**
     * Job instance
     * 
     * @var object
     */
    protected $job;

    /**
     * Environment
     * 
     * @var string
     */
    protected $env = 'production';

    /**
     * Cli instance
     * 
     * @var object
     */
    protected $cli;

    /**
     * Queue instance
     * 
     * @var object
     */
    protected $queue;

    /**
     * Logger instance
     * 
     * @var object
     */
    protected $logger;

    /**
     * Command line parser
     * 
     * @var object
     */
    protected $parser;

    /**
     * Queue route key ( queue name )
     * 
     * @var string
     */
    protected $route;

    /**
     * Job delay interval
     * 
     * @var int
     */
    protected $delay;

    /**
     * Maximum allowed memory for current job
     * 
     * @var int
     */
    protected $memory;

    /**
     * Max timeout
     * 
     * @var int
     */
    protected $timeout;

    /**
     * Sleep time
     * 
     * @var int
     */
    protected $sleep;

    /**
     * Max attempts
     * 
     * @var int
     */
    protected $tries;

    /**
     * Enable debugger
     * 
     * @var int
     */
    protected $debug;

    /**
     * Your project name
     * 
     * @var string
     */
    protected $project = null;

    /**
     * Your custom variable
     * 
     * @var string
     */
    protected $var = null;

    /**
     * Registered error handler
     *
     * @var bool
     */
    protected static $registeredErrorHandler = false;

    /**
     * Registered exception handler
     *
     * @var bool
     */
    protected static $registeredExceptionHandler = false;

    /**
     * Registered fatal error handler
     * 
     * @var boolean
     */
    protected static $registeredFatalErrorShutdownFunction = false;

    /**
     * Error priorities
     * 
     * @var array
     */
    protected static $priorities = array(
        'emergency' => LOG_EMERG,
        'alert'     => LOG_ALERT,
        'critical'  => LOG_CRIT,
        'error'     => LOG_ERR,
        'warning'   => LOG_WARNING,
        'notice'    => LOG_NOTICE,
        'info'      => LOG_INFO,
        'debug'     => LOG_DEBUG,
    );

    /**
     * Priority Map
     *
     * @var array
     */
    protected static $errorPriorities = array(
        E_NOTICE            => LOG_NOTICE,
        E_USER_NOTICE       => LOG_NOTICE,
        E_WARNING           => LOG_WARNING,
        E_CORE_WARNING      => LOG_WARNING,
        E_USER_WARNING      => LOG_WARNING,
        E_ERROR             => LOG_ERR,
        E_USER_ERROR        => LOG_ERR,
        E_CORE_ERROR        => LOG_ERR,
        E_RECOVERABLE_ERROR => LOG_ERR,
        E_STRICT            => LOG_DEBUG,
        E_DEPRECATED        => LOG_DEBUG,
        E_USER_DEPRECATED   => LOG_DEBUG,
    );

    /**
     * Create a new queue worker.
     *
     * @param object $c   container
     * @param array  $cli Obullo\Cli\Cli
     */
    public function __construct(ContainerInterface $c, Cli $cli)
    {
        $this->c = $c;
        $this->c['config']->load('queue/workers');  // Load queue configuration

        $this->cli = $cli;
        $this->queue = $this->c['queue'];
        $this->logger = $this->c['logger'];

        $this->logger->channel('queue');
        $this->logger->debug('Queue Worker Class Initialized');
    }

    /**
     * Initialize to worker object
     * 
     * @return void
     */
    public function init() 
    {
        $this->registerExceptionHandler();  // If debug closed don't show errors and use worker custom error handlers.
        $this->registerErrorHandler();      // Register worker error handlers.
        $this->registerFatalErrorHandler();
    
        ini_set('error_reporting', 0);      // Disable cli errors on console mode we already had error handlers.
        ini_set('display_errors', 0);
                                               // Don't change here we already catch all errors except the notices.
        error_reporting(E_NOTICE | E_STRICT);  // This is just Enable "Strict Errors" otherwise we couldn't see them.

        $this->queue->channel($this->cli->argument('channel', null));
        $this->route = $this->cli->argument('route', null);
        $this->memory = $this->cli->argument('memory', 128);
        $this->delay  = $this->cli->argument('delay', 0);
        $this->timeout = $this->cli->argument('timeout', 0);
        $this->sleep = $this->cli->argument('sleep', 3);
        $this->tries = $this->cli->argument('tries', 0);
        $this->debug = $this->cli->argument('debug', 0);
        $this->env = $this->cli->argument('env', 'local');
        $this->project = $this->cli->argument('project', 'default');
        $this->var = $this->cli->argument('var', null);

        if ($this->memoryExceeded($this->memory)) {
            die; return;
        }
    }

    /**
     * Pop the next job off of the queue.
     * 
     * @return void
     */
    public function pop()
    {
        $this->job = $this->getNextJob();
        if (! is_null($this->job)) {
            $this->doJob();
            $this->debugOutput($this->job->getRawBody());
        } else {                  // If we have not job on the queue sleep the script for a given number of seconds.
            sleep($this->sleep);  // Sleep the script for a given number of seconds.
        }
    }

    /**
     * Get the next job from the queue connection.
     *
     * @return object job
     */
    protected function getNextJob()
    {
        if (is_null($this->route)) {
            return $this->queue->pop();
        }
        foreach (explode(',', $this->route) as $this->route) {     // If comma seperated queue
            if (! is_null($job = $this->queue->pop($this->route))) { 
                return $job;
            }
        }
    }

    /**
     * Process a given job from the queue.
     * 
     * @return void
     */
    public function doJob()
    {
        if ($this->tries > 0 && $this->job->getAttempts() > $this->tries) {
            $this->job->delete();
            $this->logger->channel('queue');
            $this->logger->warning('The job failed and deleted from queue.', array('job' => $this->job->getName(), 'body' => $this->job->getRawBody()));
            return;
        }
        $this->job->setEnv($this->env);
        $this->job->fire();
    }

    /**
     * Determine if the memory limit has been exceeded.
     *
     * @param integer $memoryLimit sets memory limit
     * 
     * @return bool
     */
    public function memoryExceeded($memoryLimit)
    {
        return (memory_get_usage() / 1024 / 1024) >= $memoryLimit;
    }

    /**
     * Register logging system as an error handler to log PHP errors
     *
     * @param boolean $continueNativeHandler native handler switch
     * 
     * @return mixed Returns result of set_error_handler
     */
    public function registerErrorHandler($continueNativeHandler = false)
    {
        if (static::$registeredErrorHandler) {  // Only register once per instance
            return false;
        }
        $errorPriorities = static::$errorPriorities;    // We need to move priorities in this class.
        $previous = set_error_handler(
            function ($level, $message, $file, $line) use ($errorPriorities, $continueNativeHandler) {
                $iniLevel = error_reporting();
                if ($iniLevel & $level) {
                    $priority = static::$priorities['error'];
                    if (isset($errorPriorities[$level])) {
                        $priority = $errorPriorities[$level];
                    } 
                    $event = array(
                        'error_level' => $level,
                        'error_message' => $message,
                        'error_file' => $file,
                        'error_line' => $line,
                        'error_trace' => debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 10),
                        'error_xdebug' => '',
                        'error_priority' => $priority,
                    );
                    $this->saveFailedJob($event);
                }
                return ! $continueNativeHandler;
            }
        );
        static::$registeredErrorHandler = true;
        return $previous;
    }

    /**
     * Register logging system as an exception handler to log PHP exceptions
     * 
     * @return boolean
     */
    public function registerExceptionHandler()
    {
        if (static::$registeredExceptionHandler) {  // Only register once per instance
            return false;
        }
        $errorPriorities = static::$errorPriorities;  // @see http://www.php.net/manual/tr/errorexception.getseverity.php
        set_exception_handler(
            function ($exception) use ($errorPriorities) {
                $messages = array();
                do {
                    $priority = static::$priorities['error'];
                    $level = LOG_ERR;
                    if ($exception instanceof ErrorException && isset($errorPriorities[$exception->getSeverity()])) {
                        $level = $exception->getSeverity();
                        $priority = $errorPriorities[$level];
                    }
                    $messages[] = array(
                        'level' => $level,
                        'message' => $exception->getMessage(),
                        'file'  => $exception->getFile(),
                        'line'  => $exception->getLine(),
                        'trace'  => $exception->getTrace(),
                        'xdebug' => isset($exception->xdebug_message) ? $exception->xdebug_message : '',
                        'priority' => $priority,
                    );
                    $exception = $exception->getPrevious();
                } while ($exception);

                foreach (array_reverse($messages) as $message) {
                    $data = array(
                        'error_level' => $message['level'],
                        'error_message' => $message['message'], 
                        'error_file' => $message['file'],
                        'error_line' => $message['line'],
                        'error_trace' => $message['trace'],
                        'error_xdebug' => $message['xdebug'],
                        'error_priority' => $message['priority'],
                    );
                    $this->saveFailedJob($data);
                }
                if (! is_null($this->job) && ! $this->job->isDeleted()) { // If we catch an exception we will attempt to release the job back onto
                    $this->job->release($this->delay);  // the queue so it is not lost. This will let is be retried at a later time by another worker.
                }
            }
        );
        static::$registeredExceptionHandler = true;
        return true;
    }

    /**
     * Register a shutdown handler to log fatal errors
     * 
     * @return bool
     */
    public function registerFatalErrorHandler()
    {
        if (static::$registeredFatalErrorShutdownFunction) {  // Only register once per instance
            return false;
        }          
        register_shutdown_function(
            function () {
                if (null != $error = error_get_last()) {
                    $event = array(
                        'error_level' => $error['type'],
                        'error_message' => $error['message'], 
                        'error_file' => $error['file'],
                        'error_line' => $error['line'],
                        'error_trace' => '',
                        'error_xdebug' => '',
                        'error_priority' => 99,
                    );
                    $this->saveFailedJob($event);
                }
            }
        );
        static::$registeredFatalErrorShutdownFunction = true;
        return true;
    }

    /**
     * Save failed job to database
     * 
     * @param array $event failed data
     * 
     * @return void
     */
    protected function saveFailedJob($event)
    {
        global $c;

        // Worker does not well catch failed job exceptions because of we
        // use this function in exception handler.Thats the point why we need to try catch block.
        try {
            $event = $this->prependJobDetails($event);
            if ($this->debug) {
                $this->debugOutput($event);
            }
            if ($c['config']['queue/workers']['failed']['enabled']) {

                $storageClassName = '\\'.ltrim($c['config']['queue/workers']['failed']['storage'], '\\');
                $storage = new $storageClassName($c);
                
                $db = $storage->getConnection();

                $db->beginTransaction();
                $storage->save($event);
                $db->commit();
            }

        } catch (Exception $e) {
            $db->rollBack();
            $this->c['exception']->show($e);
        }
    }

    /**
     * Append job event to valid array
     * 
     * @param array $event array
     * 
     * @return array merge event
     */
    protected function prependJobDetails($event)
    {
        if (! is_object($this->job)) {
            return $event;
        }
        return array_merge(
            $event,
            array(
                'job_id' => $this->job->getJobId(),
                'job_name' => $this->job->getName(),
                'job_body' => $this->job->getRawBody(),
                'job_attempts' => $this->job->getAttempts()
            )
        );
    }

    /**
     * Unregister error handler
     *
     * @return void
     */
    public static function unregisterErrorHandler()
    {
        restore_error_handler();
        static::$registeredErrorHandler = false;
    }

    /**
     * Unregister exception handler
     *
     * @return void
     */
    public function unregisterExceptionHandler()
    {
        restore_exception_handler();
        static::$registeredExceptionHandler = false;
    }

    /**
     * Print errors and output
     * 
     * @param array|string $event output
     * 
     * @return void
     */
    public function debugOutput($event)
    {
        if (is_string($event)) {
            echo Console::text("Output : \n".$event."\n", 'yellow');
        } elseif (is_array($event)) {
            unset($event['error_trace']);
            unset($event['error_xdebug']);
            unset($event['error_priority']);
            echo Console::fail("Error : \n".print_r($event, true));
        }
    }

}

// END Worker class

/* End of file Worker.php */
/* Location: .Obullo/Queue/Worker.php */