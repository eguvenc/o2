<?php

namespace Obullo\Log\Writer;

use Obullo\Log\Writer\AbstractWriter,
    InvalidArgumentException;

/**
 * File Writer Class
 * 
 * @category  Log
 * @package   Writer
 * @author    Obullo Framework <obulloframework@gmail.com>
 * @copyright 2009-2014 Obullo
 * @license   http://www.gnu.org/licenses/gpl-3.0.html GPL Licence
 * @link      http://obullo.com/package/log
 */
Class MongoWriter extends AbstractWriter
{
    /**
     * Config
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
     * @param array $mongo  provider client instance
     * @param array $params configuration
     */
    public function __construct($mongo, $params)
    {
        parent::__construct($params);

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
     * Config
     * 
     * @return array
     */
    public function getConfig()
    {
        return $this->config;
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

// END MongoWriter class

/* End of file MongoWriter.php */
/* Location: .Obullo/Log/Writer/MongoWriter.php */