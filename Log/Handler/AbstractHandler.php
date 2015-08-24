<?php

namespace Obullo\Log\Handler;

use Obullo\Config\ConfigInterface;
use Obullo\Application\Application;
use Obullo\Log\Formatter\LineFormatter;

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
     * Application
     * 
     * @var object
     */
    protected $app;
    
    /**
     * Config
     * 
     * @var object
     */
    protected $config;

    /**
     * Constructor
     * 
     * @param object $app    \Obullo\Application\Application
     * @param object $config \Obullo\Config\ConfigInterface
     */
    public function __construct(Application $app, ConfigInterface $config)
    {
        $this->app = $app;
        $this->config = $config;
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
        if (in_array($data['request'], array(null, 'http','ajax','cli'))) { // Disable logs if request not allowed
            return true;
        }
        if ($data['request'] == 'worker') {
            return $this->config['logger']['app']['worker']['log'];  // Disable / enable worker logs
        }
        if ($this->app->uri->segment(0) == 'debugger') {  // Disable http debugger logs
            return false;
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
            'datetime' => date($this->config['logger']['format']['date'], $data['time']),
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