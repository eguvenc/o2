<?php

namespace Obullo\Log\JobHandler;

use Obullo\Log\Formatter\LineFormatter;

/**
 * Syslog JobHandler Class
 * 
 * @category  Log
 * @package   JobHandler
 * @author    Obullo Framework <obulloframework@gmail.com>
 * @copyright 2009-2014 Obullo
 * @license   http://www.gnu.org/licenses/gpl-3.0.html GPL Licence
 * @link      http://obullo.com/package/log
 */
Class JobHandlerSyslog implements JobHandlerInterface
{
    /**
     * Container
     * 
     * @var object
     */
    public $c;

    /**
     * Formatter
     * 
     * @var object
     */
    protected $formatter;

    /**
     * Facility used by this syslog instance
     * 
     * @var string
     */
    public $facility = LOG_USER;

    /**
     * Syslog application name
     * 
     * @var string
     */
    public $name = 'QueueLogger.Handler.Syslog';

    /**
     * Config Constructor
     *
     * @param object $c      container
     * @param array  $params parameters
     */
    public function __construct($c, array $params = array())
    {
        $this->c = $c;
        $this->config = $params;
        $this->formatter = new LineFormatter($this->c);

        if (isset($params['facility'])) {
            $this->facility = $params['facility'];  // Application facility
        }
        if (isset($params['name'])) {       // Application name
            $this->name = $params['name'];
        }
        openlog($this->name, LOG_PID, $this->facility);
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
            'level'    => $unformatted_record['level'],
            'message'  => $unformatted_record['message'],
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
            // $record['context'] = preg_replace('/[\r\n]+/', '', var_export($unformattedRecord['context'], true));
        }
        return $record; // Formatted record
    }

    /**
     * Write output
     *
     * @param string $data single record data
     * 
     * @return mixed
     */
    public function write(array $data)
    {
        if (isset($data['batch'])) {
            foreach ($data['record'] as $record) {
                syslog($record['level'], $this->formatter->format($record));
            }
        }
        return true;
    }

    /**
     * Close handler connection
     * 
     * @return void
     */
    public function close() 
    {
        closelog();
    }
}

// END JobHandlerSyslog class

/* End of file JobHandlerSyslog.php */
/* Location: .Obullo/Log/JobHandler/JobHandlerSyslog.php */