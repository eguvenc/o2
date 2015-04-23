<?php

namespace Obullo\ServiceProviders\Connections;

use RuntimeException;
use Obullo\Container\Container;

/**
 * Query Connection Provider
 * 
 * @category  ConnectionProvider
 * @package   ServiceProviders
 * @author    Obullo Framework <obulloframework@gmail.com>
 * @copyright 2009-2014 Obullo
 * @license   http://opensource.org/licenses/MIT MIT license
 * @link      http://obullo.com/package/provider
 */
class QueryConnectionProvider extends AbstractConnectionProvider
{
    protected $c; // Container

    /**
     * Constructor
     * 
     * @param string $c container
     */
    public function __construct(Container $c)
    {
        $this->c = $c;
        $this->setKey('query.connection.');  // Set container key
    }

    /**
     * Get shared driver object
     * 
     * @param array $params parameters
     * 
     * @return object
     */
    public function getConnection($params)
    {
        $connection = $this->factory($params);
        return $connection;
    }

    /**
     * Create a new database query connection
     * 
     * if you don't want to add it to config file and you want to create new one.
     * 
     * @param array $params connection parameters
     * 
     * @return object mongo client
     */
    public function factory($params = array())
    {
        if (empty($params['connection'])) {
            throw new RuntimeException(
                sprintf(
                    "Query provider requires connection parameter. <pre>%s</pre>",
                    "\$c['service provider query']->get(['connection' => 'default']);"
                )
            );
        }
        $cid = $this->getKey($this->getConnectionId($params));

        if ( ! $this->c->exists($cid)) {    //  create shared connection if not exists
            $this->c[$cid] = function () use ($params) {  //  create shared connections
                return $this->createClass($params);
            };
        }
        return $this->c[$cid];  // Get registered connection
    }

    /**
     * Creates query builder connections
     * 
     * @param array $options connection options
     * 
     * @return void
     */
    protected function createClass($options)
    {
        $Class = '\\Obullo\Database\Pdo\QueryBuilder';
        return new $Class($this->c['service provider database'], $options);
    }

}

// END QueryConnectionProvider.php class
/* End of file QueryConnectionProvider.php */

/* Location: .Obullo/ServiceProviders/Connections/QueryConnectionProvider.php */