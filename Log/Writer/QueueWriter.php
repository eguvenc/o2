<?php

namespace Obullo\Log\Writer;

use Obullo\Log\Writer\AbstractWriter;

/**
 * Queue Writer Class
 * 
 * @category  Log
 * @package   Writer
 * @author    Obullo Framework <obulloframework@gmail.com>
 * @copyright 2009-2014 Obullo
 * @license   http://www.gnu.org/licenses/gpl-3.0.html GPL Licence
 * @link      http://obullo.com/package/log
 */
Class QueueWriter extends AbstractWriter
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

        $this->type = 'app'; // Http requests
        if ( ! empty($_SERVER['HTTP_X_REQUESTED_WITH']) AND strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
            $this->type ='ajax';  // Ajax requests
        }
        if (defined('STDIN')) {
            $this->type = 'cli';  // Cli requests
        }
    }

    /**
     * Config
     * 
     * @return array
     */
    public function getConfig()
    {
        return $this->config;
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
     * @param string $record multiline record data
     * @param string $type   request types ( app, cli, ajax )
     * 
     * @return mixed
     */
    public function batch(array $record, $type = null)
    {
        if ( ! $this->isAllowed($type)) {
            return;
        }
        $this->queue->channel($this->channel);
        $this->queue->push($this->job, $this->route, array('type' => $this->type, 'record' => $record, 'batch' => true), $this->delay);
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

// END QueueWriter class

/* End of file QueueWriter.php */
/* Location: .Obullo/Log/Writer/QueueWriter.php */