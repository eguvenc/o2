<?php

namespace Obullo\Log\Handler;

use Obullo\Log\Logger,
    Obullo\Log\PriorityQueue,
    Obullo\Log\Formatter\LineFormatter;

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
Class SyslogHandler implements HandlerInterface
{
    /**
     * Container class
     * 
     * @var object
     */
    public $c;

    /**
     * Logger class
     * 
     * @var object
     */
    public $logger;

    /**
     * Writer class
     * 
     * @var object
     */
    public $writer;

    /**
     * Constructor
     * 
     * @param object $c      container
     * @param array  $writer array
     */
    public function __construct($c, $writer)
    {
        $this->c = $c;
        $this->writer = $writer;   
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
        if (count($unformattedRecord['context']) > 0) {
            $record['context'] = preg_replace('/[\r\n]+/', '', var_export($unformattedRecord['context'], true));
        }
        if (isset($unformattedRecord['context']['extra']) AND count($unformattedRecord['context']['extra']) > 0) {
            $record['extra'] = var_export($unformattedRecord['context']['extra'], true);
            unset($record['context']['extra']);
        }
        return $record; // Formatted record
    }

    /**
     * Write processor output to mongo
     *
     * @param object $pQ priorityQueue object
     * 
     * @return boolean
     */
    public function write(PriorityQueue $pQ)
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
                $level = Logger::$priorities[$records[$i]['level']];
                $this->writer->write($formatter->format($records[$i]), $level);
                $i++;
            }
        }
    }

    /**
     * Close connection
     * 
     * @return void
     */
    public function close()
    {
        $this->writer->close();
    }

}

// END SyslogHandler class

/* End of file SyslogHandler.php */
/* Location: .Obullo/Log/Handler/SyslogHandler.php */