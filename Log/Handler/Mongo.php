<?php

namespace Obullo\Log\Handler;

use MongoDate;
use InvalidArgumentException;
use Obullo\Container\Container;

/**
 * Mongo Log Handler Class
 * 
 * @category  Log
 * @package   Handler
 * @author    Obullo Framework <obulloframework@gmail.com>
 * @copyright 2009-2014 Obullo
 * @license   http://opensource.org/licenses/MIT MIT license
 * @link      http://obullo.com/package/log
 */
Class Mongo extends AbstractHandler implements HandlerInterface
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
    public function __construct(Container $c, $mongo, array $params = array())
    {
        $this->c = $c;
        $database = isset($params['database']) ? $params['database'] : null;
        $collection = isset($params['collection']) ? $params['collection'] : null;
        $saveOptions = isset($params['save_options']) ? $params['save_options'] : null;

        parent::__construct($c);

        if (null === $collection) {
            throw new InvalidArgumentException('The collection parameter cannot be empty');
        }
        if (null === $database) {
            throw new InvalidArgumentException('The database parameter cannot be empty');
        }
        if (get_class($mongo) != 'MongoClient' OR get_class($mongo) != 'Mongo') {
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
    * @param string $timestamp         unix time
    * @param array  $unformattedRecord log data
    * 
    * @return array formatted record
    */
    public function arrayFormat($timestamp, $unformattedRecord)
    {
        $record = array(
            'datetime' => new MongoDate(strtotime(date($this->c['config']['logger']['format']['date'], $timestamp))),
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
        return $record;
    }

    /**
     * Writer 
     *
     * @param array $data log record
     * 
     * @return void
     */
    public function write(array $data)
    {
        $records = array();
        foreach ($data['record'] as $record) {
            $records[] = $this->arrayFormat($data['time'], $record);
        }
        $this->mongoCollection->batchInsert(
            $records, 
            array_merge(
                $this->saveOptions, 
                ['continueOnError' => true]
            )
        );
    }

    /**
     * Close handler connection
     * 
     * @return void
     */
    public function close() 
    {
        return $this->mongoClient->close();
    }
}

// END Mongo class

/* End of file Mongo.php */
/* Location: .Obullo/Log/Handler/Mongo.php */