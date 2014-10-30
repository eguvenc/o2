<?php

namespace Obullo\Log\Handler\Simple;

use Obullo\Log\PriorityQueue,
    Obullo\Log\Formatter\LineFormatter,
    Obullo\Log\Handler\AbstractHandler,
    Obullo\Log\Handler\HandlerInterface;

use Exception,
    MongoDate,
    MongoCollection,
    MongoClient,
    RunTimeException,
    InvalidArgumentException;

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
Class MongoHandler extends AbstractHandler implements HandlerInterface
{
    /**
     * Container
     * 
     * @var object
     */
    public $c;

    /**
     * Config params
     * 
     * @var array
     */
    public $config;

    /**
     * mongoClient object
     * 
     * @var object
     */
    public $mongoClient;

    /**
     * mongoCollection object
     * 
     * @var object
     */
    public $mongoCollection;

    /**
     * Mongo save options
     * 
     * @var array
     */
    public $saveOptions;

    /**
     * Constructor
     * 
     * @param object $c      container
     * @param array  $params array
     */
    public function __construct($c, $params)
    {
        $this->c = $c;
        $this->config = $params;
        
        parent::__construct($params);

        $database = isset($params['database']) ? $params['database'] : null;
        $collection = isset($params['collection']) ? $params['collection'] : null;
        $saveOptions = isset($params['save_options']) ? $params['save_options'] : null;

        if (null === $collection) {
            throw new InvalidArgumentException('The collection parameter cannot be empty');
        }
        if (null === $database) {
            throw new InvalidArgumentException('The database parameter cannot be empty');
        }
        if (get_class($mongo) != 'MongoClient') {
            throw new InvalidArgumentException('Parameter of type %s is invalid; must be MongoClient or Mongo', (is_object($mongo) ? get_class($mongo) : gettype($mongo)));
        }
        $this->mongoClient = $mongo;
        $this->mongoCollection = $mongo->selectCollection($database, $collection);
        $this->saveOptions = $saveOptions;
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
            'context'  => null,
            'extra'    => null,
        );
        if (isset($unformattedRecord['context']['extra']) AND count($unformattedRecord['context']['extra']) > 0) {
            
            $record['extra'] = $unformattedRecord['context']['extra']; // Default extra data format is array.

            if ($this->config['format']['extra'] == 'json') { // if extra data format json ?
                $record['extra'] = json_encode($unformattedRecord['context']['extra'], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE); 
            }
            unset($unformattedRecord['context']['extra']);
        }
        if (count($unformattedRecord['context']) > 0) {
            $record['context'] = $unformattedRecord['context'];
            if ($this->config['format']['context'] == 'json') {
                $record['context'] = json_encode($unformattedRecord['context'], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
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
    public function exec(PriorityQueue $pQ)
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
            $this->batch($records);
        }
    }

    /**
     * Write output
     *
     * @param string $record single  record data
     * @param string $type   request types ( app, cli, ajax )
     * 
     * @return mixed
     */
    public function write($record, $type = null)
    {
        if ( ! $this->isAllowed($type)) {
            return;
        }
        return $this->mongoCollection->insert($record);
    }

    /**
     * Batch Operation
     *
     * @param string $records multiline record data
     * @param string $type    request   types ( app, cli, ajax )
     * 
     * @return mixed
     */
    public function batch(array $records, $type = null)
    {
        if ( ! $this->isAllowed($type)) {
            return;
        }
        return $this->mongoCollection->batchInsert($records);
    }

    /**
     * Close mongo connection
     * 
     * @return void
     */
    public function close()
    {
        $this->mongoClient->close();
    }

}

// END MongoHandler class

/* End of file MongoHandler.php */
/* Location: .Obullo/Log/Handler/MongoHandler.php */