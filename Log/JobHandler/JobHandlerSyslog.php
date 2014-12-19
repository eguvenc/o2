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
 * @license   http://opensource.org/licenses/MIT MIT license
 * @link      http://obullo.com/package/log
 */
Class JobHandlerSyslog extends AbstractJobHandler implements JobHandlerInterface
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
        $this->formatter = new LineFormatter($this->c);

        parent::__construct($params);

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
    * @param string $timestamp         unix time
    * @param array  $unformattedRecord log data
    * 
    * @return array formatted record
    */
    public function format($timestamp, $unformattedRecord)
    {
        $record = array(
            'datetime' => date($this->config['log']['format']['date'], $timestamp),
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
        }
        return $record; // Formatted record
    }

    /**
     * Write output
     *
     * @param string $data single record data
     * 
     * @return void
     */
    public function write(array $data)
    {
        foreach ($data['record'] as $record) {
            $record = $this->format($data['time'], $record);
            syslog($record['level'], $this->formatter->format($record));
        }
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