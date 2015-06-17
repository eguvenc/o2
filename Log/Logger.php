<?php

namespace Obullo\Log;

use LogicException;
use Obullo\Container\ContainerInterface;

/**
 * Logger Class
 * 
 * @category  Log
 * @package   Logger
 * @author    Obullo Framework <obulloframework@gmail.com>
 * @copyright 2009-2014 Obullo
 * @license   http://opensource.org/licenses/MIT MIT license
 * @link      http://obullo.com/package/log
 */
class Logger extends AbstractLogger implements LoggerInterface
{
    use LoggerTrait;

    public $c;
    public $config;
    public $params = array();                 // Service parameters
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
     * @param object $c      container
     * @param array  $params parameters
     */
    public function __construct(ContainerInterface $c, $params = array())
    {
        $this->c = $c;
        $this->params = $params;
        $this->enabled = $c['config']['log']['enabled'];
        $this->config  = $c['config']->load('logger');  // Load logger package configuration

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
        if ($this->enabled && $this->connect) {    // Lazy loading for Logger service
                                                   // if connect method executed one time then we open connections and load classes
                                                   // When connect booelan is true we execute standart worker or queue.
            $this->exec();  // Set payload data

            // Queue Logger

            if (isset($this->params['queue']['enabled']) && $this->params['queue']['enabled']) {     // Send data to queue

                $this->c->get('queue')
                    ->channel($this->params['queue']['channel'])
                    ->push(
                        $this->params['queue']['worker'],
                        $this->params['queue']['route'],
                        $this->payload,
                        $this->params['queue']['delay']
                    );

            } else {  // Standart Logger

                $worker = new \Workers\Logger($this->c); // Execute standart logger
                $worker->fire(null, $this->payload);
            }
        }
    }

}

// END Logger class

/* End of file Logger.php */
/* Location: .Obullo/Log/Logger.php */