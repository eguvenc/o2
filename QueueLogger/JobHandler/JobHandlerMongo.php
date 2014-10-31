<?php

namespace Obullo\QueueLogger\JobHandler;

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
     * @param array  $params parameters
     */
    public function __construct($c, array $params = array())
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
            throw new InvalidArgumentException('Parameter of type %s is invalid; must be MongoClient or Mongo', (is_object($mongo) ? get_class($mongo) : gettype($mongo)));
        }
        $this->mongoClient = $mongo;
        $this->mongoCollection = $mongo->selectCollection($database, $collection);
        $this->saveOptions = $saveOptions;
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
        if (isset($data['batch'])) {
            return $this->mongoCollection->batchInsert($data['record']);
        }        
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
/* Location: .Obullo/Log/Queue/JobHandler/JobHandlerMongo.php */