<?php

namespace Obullo\Service\Providers\Connections;

/**
 * Service Connnection Provider Helper
 * 
 * @category  Connections
 * @package   Service
 * @author    Obullo Framework <obulloframework@gmail.com>
 * @copyright 2009-2014 Obullo
 * @license   http://opensource.org/licenses/MIT MIT license
 * @link      http://obullo.com/package/service
 */
class AbstractConnectionProvider
{
    /**
     * Connection id prefix
     * 
     * @var string
     */
    protected $keyPrefix;

    /**
     * Connection ids
     * 
     * @var array
     */
    protected $connections = array();

    /**
     * Sets container connection prefix
     * 
     * @param string $prefix connection prefix
     *
     * @return void
     */
    public function setKey($prefix)
    {
        $this->keyPrefix = $prefix;
    }

    /**
     * Returns to connection prefix
     *
     * e.g. amqp.connection.
     * 
     * @param null|integer $id connection id
     * 
     * @return string
     */
    public function getKey($id = null)
    {
        return ($id == null) ? $this->keyPrefix : $this->keyPrefix.$id;
    }

    /**
     * Returns to connection id
     * 
     * @param string $string serialized parameters
     * 
     * @return integer
     */
    public function getConnectionId($string)
    {
        $connid = sprintf("%u", crc32(serialize($string)));
        $this->connections[$this->keyPrefix][] = $this->keyPrefix.$connid;
        return $connid;
    }

    /**
     * Returns all connections
     * 
     * @return array
     */
    public function getFactoryConnections()
    {
        return $this->connections[$this->keyPrefix];
    }
}

// END AbstractConnectionProvider class

/* End of file AbstractConnectionProvider.php */
/* Location: .Obullo/Service/Providers/Connections/AbstractConnectionProvider.php */