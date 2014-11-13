<?php

namespace Obullo\Log\JobHandler;

/**
 * File JobHandler Class
 * 
 * @category  Log
 * @package   JobHandler
 * @author    Obullo Framework <obulloframework@gmail.com>
 * @copyright 2009-2014 Obullo
 * @license   http://www.gnu.org/licenses/gpl-3.0.html GPL Licence
 * @link      http://obullo.com/package/log
 */
Class JobHandlerMongo implements JobHandlerInterface
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
     * Config Constructor
     *
     * @param object $c      container
     * @param object $mongo  $mongo service provider
     * @param array  $params parameters
     */
    public function __construct($c, $mongo, array $params = array())
    {
        $this->c = $c;
        $this->config = $params;

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
            throw new InvalidArgumentException(
                'Parameter of type %s is invalid; must be MongoClient or Mongo instance.', 
                (is_object($mongo) ? get_class($mongo) : gettype($$mongo))
            );
        }
        $this->mongoClient = $mongo;
        $this->mongoCollection = $this->mongoClient->selectCollection($database, $collection);
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
     * Writer 
     *
     * @param array $data log record
     * 
     * @return boolean
     */
    public function write(array $data)
    {
        if ( ! $this->isAllowed($data['type'])) {
            return;
        }
        $this->mongoCollection->batchInsert($data['record'], array('continueOnError' => true));
    }

    /**
     * Close handler connection
     * 
     * @return void
     */
    public function close() 
    {
        return $this->mongoCollection->close();
    }
}

// END JobHandlerMongo class

/* End of file JobHandlerMongo.php */
/* Location: .Obullo/Log/JobHandler/JobHandlerMongo.php */