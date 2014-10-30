<?php

namespace Obullo\Log\Queue\Handler;

use Obullo\Log\PriorityQueue,
    Obullo\Log\Formatter\LineFormatter;

/**
 * Email Handler Class
 *
 * You should use this handler for emergency, alerts or rarely used important notices.
 * 
 * @category  Log
 * @package   Handler
 * @author    Obullo Framework <obulloframework@gmail.com>
 * @copyright 2009-2014 Obullo
 * @license   http://www.gnu.org/licenses/gpl-3.0.html GPL Licence
 * @link      http://obullo.com/package/log
 */
Class EmailHandler extends AbstractHandler implements HandlerInterface
{
    /**
     * Config
     * 
     * @var array
     */
    public $config;

    /**
     * Request Type ( app, cli, ajax )
     * 
     * @var string
     */
    public $type;

    /**
     * Queue object
     * 
     * @var object
     */
    public $queue;

    /**
     * Log channel
     * 
     * @var string
     */
    public $channel = 'Logs';

    /**
     * Log route
     * 
     * @var string
     */
    public $route;

    /**
     * Job name
     * 
     * @var string
     */
    public $job = 'QueueLogger';

    /**
     * DateTime or integer push job onto the queue after a delay.
     * 
     * @var integer
     */
    public $delay = 0;

    /**
     * Constructor
     * 
     * @param array $queue  object
     * @param array $params configuration
     */
    public function __construct($queue, $params)
    {
        parent::__construct($params);

        $this->config = $params;
        $this->queue = $queue;
        $this->channel = $params['channel']; // Log channel
        $this->route = $params['route'];     // Server1.Logger.File
        $this->job = $params['job'];  // Logging
        $this->delay = $params['delay'];  // Delay

        $this->type = 'app';    // Http requests
        if ( ! empty($_SERVER['HTTP_X_REQUESTED_WITH']) AND strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
            $this->type ='ajax';  // Ajax requests
        }
        if (defined('STDIN')) {
            $this->type = 'cli';  // Cli requests
        }
    }

    /**
    * Format log records and build lines
    *
    * @param string $dateFormat        log date format
    * @param array  $unformattedRecord log data
    * 
    * @return array formatted record
    */
    public function format($dateFormat, $unformattedRecord)
    {
        $record = array(
            'datetime' => date($dateFormat),
            'channel'  => $unformattedRecord['channel'],
            'level'    => $unformattedRecord['level'],
            'message'  => $unformattedRecord['message'],
            'context'  => null,
            'extra'    => null,
        );
        if (isset($unformattedRecord['context']['extra']) AND count($unformattedRecord['context']['extra']) > 0) {
            $record['extra'] = var_export($unformattedRecord['context']['extra'], true);
            unset($unformattedRecord['context']['extra']);
        }
        if (count($unformattedRecord['context']) > 0) {
            $record['context'] = preg_replace('/[\r\n]+/', '', var_export($unformattedRecord['context'], true));
        }
        return $record; // formatted record
    }

    /**
     * Write processor output to file
     *
     * @param object $pQ priorityQueue object
     * 
     * @return boolean
     */
    public function exec(PriorityQueue $pQ)
    {
        $pQ->setExtractFlags(PriorityQueue::EXTR_DATA); // Queue mode of extraction 
        $formatter = new LineFormatter($this->c);

        if ($pQ->count() > 0) {
            $pQ->top();  // Go to Top
            $records = array();
            $i = 0;
            while ($pQ->valid()) {    // Prepare Lines
                $i++;
                $records[$i] = $formatter->format($pQ->current());
                $pQ->next(); 
            }
            $this->batch($records);
        }
    }

    /**
     * Write output
     *
     * @param string $record single record data
     * @param string $type   request types ( app, cli, ajax )
     * 
     * @return mixed
     */
    public function write($record, $type = null)
    {
        if ( ! $this->isAllowed($type)) {
            return;
        }
        $this->queue->channel($this->channel);
        $this->queue->push($this->job, $this->route, array('type' => $this->type, 'record' => $record), $this->delay);
        return true;
    }

    /**
     * Batch Operation
     *
     * @param string $records multiline record data
     * @param string $type    request types ( app, cli, ajax )
     * 
     * @return mixed
     */
    public function batch(array $records, $type = null)
    {
        if ( ! $this->isAllowed($type)) {
            return;
        }
        $this->queue->channel($this->channel);
        $this->queue->push($this->job, $this->route, array('type' => $this->type, 'record' => $records, 'batch' => true), $this->delay);
        return true;
    }

    /**
     * Close connection
     * 
     * @return void
     */
    public function close()
    {
        return;
    }
}

// END EmailHandler class

/* End of file EmailHandler.php */
/* Location: .Obullo/Log/Handler/EmailHandler.php */