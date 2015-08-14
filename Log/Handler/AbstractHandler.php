<?php

namespace Obullo\Log\Handler;

use Obullo\Log\Formatter\LineFormatter;
use Obullo\Container\ContainerInterface;

/**
 * Abstract Log Handler
 * 
 * @category  Log
 * @package   Handler
 * @author    Obullo Framework <obulloframework@gmail.com>
 * @copyright 2009-2015 Obullo
 * @license   http://opensource.org/licenses/MIT MIT license
 * @link      http://obullo.com/package/log
 */
abstract class AbstractHandler
{
    /**
     * Logger
     * 
     * @var logger
     */
    protected $logger;
    
    /**
     * Config
     * 
     * @var array
     */
    protected $config = array();

    /**
     * Constructor
     */
    public function __construct()
    {
        global $c;
        $this->config = $c['config']->load('logger');
        $this->logger = $c['logger'];
    }

    /**
     * Check log writing is allowed, deny not allowed
     * requests.
     *
     * @param string $data log records
     * 
     * @return boolean
     */
    public function isAllowed(array $data)
    {
        if (isset($_GET['o_debugger'])) {  //  Disable http debugger logs
            return false;
        }
        if ($data['request'] == 'worker') {
            return $this->logger->isRegisteredAsWorker();
        }
        if (in_array($data['request'], array(null, 'http','ajax','cli'))) {
            return true;
        }
        return false;
    }

    /**
    * Format log records
    *
    * @param string $data              all data
    * @param array  $unformattedRecord current log record
    * 
    * @return array formatted record
    */
    public function arrayFormat($data, $unformattedRecord)
    {
        $record = array(
            'datetime' => date($this->config['format']['date'], $data['time']),
            'channel'  => $unformattedRecord['channel'],
            'level'    => $unformattedRecord['level'],
            'message'  => $unformattedRecord['message'],
            'context'  => null,
            'extra'    => null,
        );
        if (isset($unformattedRecord['context']['extra']) && count($unformattedRecord['context']['extra']) > 0) {
            $record['extra'] = var_export($unformattedRecord['context']['extra'], true);
            unset($unformattedRecord['context']['extra']);     
        }
        if (count($unformattedRecord['context']) > 0) {
            $str = var_export($unformattedRecord['context'], true);
            $record['context'] = strtr($str, array("\r\n" => '', "\r" => '', "\n" => ''));
        }
        return $record; // formatted record
    }

    /**
     * Format the line defined in config/env.$env/config.php
     * 
     * @param array $record one log data
     * 
     * @return string
     */
    public function lineFormat(array $record)
    {
        return LineFormatter::format($record, $this->config);
    }

    /**
     * Write log data
     *
     * @param array $records all log data
     * 
     * @return boolean
     */
    abstract public function write(array $records);

    /**
     * Close connection
     * 
     * @return void
     */
    abstract public function close();

}