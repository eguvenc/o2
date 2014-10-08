<?php

namespace Obullo\Log\Handler;

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
Class EmailHandler implements HandlerInterface
{
    /**
     * Container
     * 
     * @var object
     */
    public $c;

    /**
     * Writer class name
     * 
     * @var string
     */
    public $writer;

    /**
     * Config Constructor
     *
     * @param object $c      container
     * @param object $writer writer 
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
            'level'    => $unformattedRecord['level'],
            'message'  => $unformattedRecord['message'],
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
        return $record; // formatted record
    }

    /**
     * Write processor output to file
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
            $lines = '';
            while ($pQ->valid()) {    // Prepare Lines
                $lines.= $formatter->format($pQ->current());
                $pQ->next(); 
            }
            $this->writer->batch($lines);
        }
    }

    /**
     * Close handler connection
     * 
     * @return void
     */
    public function close() 
    {
        return $this->writer->close();
    }
}

// END EmailHandler class

/* End of file EmailHandler.php */
/* Location: .Obullo/Log/Handler/EmailHandler.php */