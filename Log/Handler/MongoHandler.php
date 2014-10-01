<?php

namespace Obullo\Log\Handler;

use Obullo\Log\PriorityQueue;

use Exception,
    MongoDate,
    MongoCollection,
    MongoClient,
    RunTimeException;

/**
 * Mongo Handler Class
 * 
 * @category  Log
 * @package   Handler
 * @author    Obullo Framework <obulloframework@gmail.com>
 * @copyright 2009-2014 Obullo
 * @license   http://www.gnu.org/licenses/gpl-3.0.html GPL Licence
 * @link      http://obullo.com/package/log
 */
Class MongoHandler implements HandlerInterface
{
    /**
     * Container
     * 
     * @var object
     */
    public $c;

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
            'datetime' => new MongoDate(strtotime(date($dateFormat))),
            'channel'  => $unformattedRecord['channel'],
            'level'    => $unformattedRecord['level'],
            'message'  => $unformattedRecord['message'],
            'context'  => $unformattedRecord['context'],
            'extra'    => (isset($unformattedRecord['context']['extra'])) ? $unformattedRecord['context']['extra'] : '',
        );
        $config = $this->writer->getConfig();

        if (count($unformattedRecord['context']) > 0) {
            if ($config['format']['context'] == 'json') {
                $record['context'] = json_encode($unformattedRecord['context'], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
            }
        }
        if (isset($unformattedRecord['context']['extra']) AND count($unformattedRecord['context']['extra']) > 0) {
            if ($config['format']['extra'] == 'json') {
                $record['extra'] = json_encode($unformattedRecord['context']['extra'], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE); 
            }
        }
        return $record;  // Formatted record
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

        if ($pQ->count() > 0) {
            $pQ->top();  // Go to Top
            $records = array();
            $i = 0;
            while ($pQ->valid()) {         // Prepare Lines
                $records[$i] = $pQ->current(); 
                $pQ->next();
                $i++;
            }
            $this->writer->batch($records);
        }
    }

    /**
     * Close mongo connection
     * 
     * @return void
     */
    public function close()
    {
        $this->writer->close();
    }

}

// END MongoHandler class

/* End of file MongoHandler.php */
/* Location: .Obullo/Log/Handler/MongoHandler.php */