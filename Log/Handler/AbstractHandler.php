<?php

namespace Obullo\Log\Handler;

use Obullo\Container\Container;

/**
 * Abstract Log Handler
 * 
 * @category  Log
 * @package   Handler
 * @author    Obullo Framework <obulloframework@gmail.com>
 * @copyright 2009-2014 Obullo
 * @license   http://opensource.org/licenses/MIT MIT license
 * @link      http://obullo.com/package/log
 */
abstract class AbstractHandler
{
    /**
     * Constructor
     *
     * @param object $c container
     */
    public function __construct(Container $c)
    {
        $this->c = $c;
        $this->c['config']->load('logger');
    }

    /**
     * Check log writing is allowed, deny not allowed
     * requests.
     *
     * @param string $record log record
     * 
     * @return boolean
     */
    public function isAllowed(array $record)
    {
        if (isset($_GET['o_debugger'])) {  //  Disable http debugger logs
            return false;
        }
        if ($record['request'] == 'worker') {
            return $this->c['config']['logger']['queue']['workers']['logging'];  //  If worker logs allowed from config file.
        }
        if (in_array($record['request'], array(null, 'http','ajax','cli'))) {
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
            'datetime' => date($this->c['config']['logger']['format']['date'], $data['time']),
            'channel'  => $unformattedRecord['channel'],
            'level'    => $unformattedRecord['level'],
            'message'  => $unformattedRecord['message'],
            'request'  => $data['request'],
            'context'  => null,
            'extra'    => null,
        );
        if (isset($unformattedRecord['context']['extra']) AND count($unformattedRecord['context']['extra']) > 0) {
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

// END AbstractHandler class

/* End of file AbstractLogHandler.php */
/* Location: .Obullo/Log/Handler/AbstractHandler.php */