<?php

namespace Obullo\Mongo;

use Obullo\Provider\MongoConnector,
    Obullo\Container\Container;

/**
 * Mongo Provider
 *
 * @category  Provider
 * @package   Mongo
 * @author    Obullo Framework <obulloframework@gmail.com>
 * @copyright 2009-2014 Obullo
 * @license   http://opensource.org/licenses/MIT MIT license
 * @link      http://obullo.com/docs/providers
 */
Class MongoProvider
{
    /**
     * Registry
     *
     * @param object $c       Container
     * @param array  $params  parameters
     * @param array  $matches loader commands
     * 
     * @return void
     */
    public function register(Container $c, $params = array(), $matches = array())
    {
        if ( ! Connector::isRegistered()) {  // Just one time register the shared objects
            
            $connector = Connector::getInstance($c);
            $connector->register();          // Register all Connectors as shared services
            return $connector->getConnection($params);
        }

        $connector = Connector::getInstance($c);

        if ( ! empty($matches['new'])) {   // Do factory ( create new Connector ) if we have new match
            return $connector->factory($params);  // dynamically creates new mongo config
        }      
        return $connector->getConnection($params);   // get a Connector instance before we registered
    }
}

// END MongoProvider Class

/* End of file MongoProvider.php */
/* Location: .Obullo/Provider/MongoProvider.php */