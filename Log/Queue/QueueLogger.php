<?php

namespace Obullo\Log\Queue;

use LogicException;
use Obullo\Queue\Queue;
use Obullo\Log\LoggerTrait;
use Obullo\Log\AbstractLogger;
use Obullo\Container\Container;
use Obullo\Log\Debugger\DebugOutput;

/**
 * QueueLogger Class
 * 
 * @category  Log
 * @package   QueueLogger
 * @author    Obullo Framework <obulloframework@gmail.com>
 * @copyright 2009-2014 Obullo
 * @license   http://opensource.org/licenses/MIT MIT license
 * @link      http://obullo.com/package/log
 */
Class QueueLogger extends AbstractLogger
{
    use LoggerTrait;

    public $queue;                            // Queue service object
    public $debug = false;                    // Debug all outputs to end of the page
    public $debugHandler;                     // Debug handler
    public $enabled = true;                   // On / Off Logging
    public $queries = false;                  // Whether to log sql queries
    public $benchmark = false;                // Whether to log benchmark, Memory usage ..
    public $writers = array();                // Available  writers: file, mongo, syslog & so on ..
    public $channel = 'system';               // Default log channel
    public $track = array();                  // Track data for handlers and writers

    protected $connect = false;               // Lazy connections
    protected $push = array();                // Push data
    protected $payload = array();             // Payload
    protected $priorityQueue = array();       // Log priority queue objects
    protected $filterNames = array();         // Namespaces of defined filters
    protected $registeredHandlers = array();  // Registered log handlers
    public static $priorityValues = array();  // Priority values

    /**
     * Registered error handlers
     *
     * @var bool
     */
    protected static $registeredErrorHandler = false;
    protected static $registeredExceptionHandler = false;
    protected static $registeredFatalErrorShutdownFunction = false;

    /**
     * Constructor
     *
     * @param object $c container
     */
    public function __construct(Container $c)
    {
        $this->c = $c;
        $this->enabled = $this->c['config']['log']['control']['enabled'];
        $this->debug = $this->c['config']['log']['control']['firelog'];

        $this->configureErrorHandlers();
        $this->initialize();

        register_shutdown_function(array($this, 'close'));
    }

    /**
     * End of the logs and beginning of the handlers.
     *
     * @return void
     */
    public function close()
    {
        if ($this->debug) {         // Debug output for log data if enabled
            $primaryWriter = $this->getPrimaryWriter();
            $debugger = new DebugOutput($this->c, $this);
            $debugger->setHandler($primaryWriter);
            echo $debugger->printDebugger($this->getQueue($primaryWriter));
            return;
        }
        if ($this->enabled == false) {  // Check logger is disabled.
            return;
        }
        // Lazy connection for Queue service,
        // if connect method executed one time then we set connect variable to true.
        // When connect booelan is available we open the real connection.
        
        if ($this->connect) {    

            $this->exec();       // Set payload data
            $queue = $this->c['return '.$this->c['config']['logger']['queue']['service']];  // Connect to Queue service
            $queue->channel($this->c['config']['logger']['queue']['channel']); // Push to Queue
            $queue->push(
                $this->c['config']['logger']['queue']['worker'],
                $this->c['config']['logger']['queue']['route'],
                $this->payload,
                $this->c['config']['logger']['queue']['delay']
            );
        }
    }

}

// END QueueLogger class

/* End of file QueueLogger.php */
/* Location: .Obullo/Log/Queue/QueueLogger.php */