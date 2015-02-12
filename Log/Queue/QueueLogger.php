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
     * @param object $c     container
     * @param object $queue queue service object
     */
    public function __construct(Container $c, Queue $queue = null)
    {
        $this->c = $c;
        $this->queue = $queue;
        $this->enabled = $this->c['config']['log']['control']['enabled'];
        $this->debug = $this->c['config']['log']['control']['firelog'];

        $this->configureErrorHandlers();
        $this->initialize();

        register_shutdown_function(array($this, 'close'));
    }


     /**
     * Extract queued log handlers data store them into one array
     * 
     * @return void
     */
    protected function exec()
    {
        $i = 0;
        $this->payload['logger'] = 'QueueLogger';
        foreach ($this->writers as $name => $val) {  // Write log data to foreach handlers
            if ( ! isset($this->push[$name]) AND isset($this->loadedHandlers[$name])) {     // If handler available in push data.
                return;
            }
            $this->payload[$i]['request'] = $this->request;
            $this->payload[$i]['handler'] = $name;
            $this->payload[$i]['priority'] = $val['priority'];
            $this->payload[$i]['time'] = time();
            $this->payload[$i]['record'] = $this->extract($name);  // array
            ++$i;
        }
    }

    /**
     * Push ( Write log handlers data )
     * 
     * @return void
     */
    public function push()
    {        
        if ($this->debug OR $this->enabled == false) {
            return;
        }
        foreach ($this->writers as $name => $val) {
            if ($val['type'] == 'handler') {
                if ( ! isset($this->writers[$name])) {
                    throw new LogicException(
                        sprintf(
                            'The push handler %s not available in log writers please first load it using below the command.
                            <pre>$this->logger->load(handler);</pre>', 
                            $name
                        )
                    );
                }
                $this->push[$name] = 1; // Allow push data to valid push handler.
            }
        }
    }

    /**
     * End of the logs and beginning of the handlers.
     *
     * @return void
     */
    public function close()
    {
        if ($this->debug) {         // Debug output for log data if enabled
            $primaryWriter = $this->getWriterName();
            $debugger = new DebugOutput($this->c, $this);
            $debugger->setHandler($primaryWriter);
            echo $debugger->printDebugger($this->getQueue($primaryWriter));
            return;
        }
        if ($this->enabled == false) {  // Check logger is disabled.
            return;
        }
        $this->exec();  // Set payload and

        $this->queue->channel($this->c['config']['logger']['queue']['channel']); //  Push to Queue
        $this->queue->push(
            $this->c['config']['logger']['queue']['worker'],
            $this->c['config']['logger']['queue']['route'],
            $this->payload,
            $this->c['config']['logger']['queue']['delay']
        );
    }

}

// END QueueLogger class

/* End of file QueueLogger.php */
/* Location: .Obullo/Log/Queue/QueueLogger.php */