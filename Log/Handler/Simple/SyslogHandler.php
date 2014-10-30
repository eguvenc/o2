<?php

namespace Obullo\Log\Handler\Simple;

use Obullo\Log\PriorityQueue,
    Obullo\Log\Formatter\LineFormatter,
    Obullo\Log\Handler\AbstractHandler,
    Obullo\Log\Handler\HandlerInterface;

use Exception;

/**
 * Syslog Handler Class
 * 
 * @category  Log
 * @package   Handler
 * @author    Obullo Framework <obulloframework@gmail.com>
 * @copyright 2009-2014 Obullo
 * @license   http://www.gnu.org/licenses/gpl-3.0.html GPL Licence
 * @link      http://obullo.com/package/log
 */
Class SyslogHandler extends AbstractHandler implements HandlerInterface
{
    /**
     * Container class
     * 
     * @var object
     */
    public $c;

    /**
     * Config variable
     * 
     * @var array
     */
    public $config;

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
    public $name = 'Log.Handler.Syslog';

    /**
     * Config Constructor
     *
     * @param object $c      container
     * @param array  $params parameters
     */
    public function __construct($c, $params)
    {
        $this->c = $c;
        $this->config = $params;

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
            $record['context'] = preg_replace('/[\r\n]+/', '', var_export($unformattedRecord['context'], true));
        }
        return $record; // Formatted record
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
            while ($pQ->valid()) {     // Prepare Lines
                $records[$i] = $pQ->current(); 
                $pQ->next();
                $this->write($formatter->format($records[$i]));
                $i++;
            }
        }
    }

    /**
     * Write line to file
     * 
     * @param string $record single  record data
     * @param string $type   request types ( app, cli, ajax )
     * 
     * @return void
     */
    public function write($record, $type = null)
    {       
        if ( ! $this->isAllowed($type)) {
            return;
        }
        syslog($record['level'], $record);
    }

    /**
     * NO batch reuired in syslog
     * 
     * @param string $records multi record data
     * @param string $type    request types ( app, cli, ajax )
     * 
     * @return boolean
     */
    public function batch(array $records, $type = null)
    {
        if ( ! $this->isAllowed($type)) {
            return;
        }
        foreach ($records as $record) {
            syslog($record['level'], $record);
        }
        return true;
    }

    /**
     * Close connection
     * 
     * @return void
     */
    public function close()
    {
        closelog();
    }

}

// END SyslogHandler class

/* End of file SyslogHandler.php */
/* Location: .Obullo/Log/Handler/SyslogHandler.php */