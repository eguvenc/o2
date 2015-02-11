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
     * Config
     * 
     * @var array
     */
    public $config = array();

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
     * Check log writing is allowed, 
     * don't allow log writing for cli commands
     *
     * @param string $request request type
     * 
     * @return boolean
     */
    public function isAllowed($request)
    {
        if ($request == 'worker') {  //  If worker logs allowed from config file.
            return $this->c['config']['logger']['queue']['workers']['logging'];
        }
        if (in_array($request, array(null, 'http','ajax','cli'))) {
            return true;
        }
        return false;
    }

    /**
    * Format log records
    *
    * @param string $timestamp         unix time
    * @param array  $unformattedRecord log data
    * 
    * @return array formatted record
    */
    public function arrayFormat($timestamp, $unformattedRecord)
    {
        $record = array(
            'datetime' => date($this->c['config']['logger']['format']['date'], $timestamp),
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
            $str = var_export($unformattedRecord['context'], true);
            $record['context'] = strtr($str, array("\r\n" => '', "\r" => '', "\n" => ''));
        }
        return $record; // formatted record
    }

    /**
     * Write processor output to file
     *
     * @param array $records log data
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