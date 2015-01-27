<?php

namespace Obullo\ServiceProvider;

use Obullo\ServiceProvider\CacheConnectionProvider,
    Obullo\Container\Container;

/**
 * Mongo Service Provider
 *
 * @category  Provider
 * @package   Mongo
 * @author    Obullo Framework <obulloframework@gmail.com>
 * @copyright 2009-2014 Obullo
 * @license   http://opensource.org/licenses/MIT MIT license
 * @link      http://obullo.com/docs/service_providers
 */
Class CacheServiceProvider
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
        $c = null;
        if ( ! CacheConnectionProvider::isConnected($params['driver'])) {  // Just one time register the shared objects
            
            $connector = CacheConnectionProvider::getInstance();  // Register all Connectors as shared services
            $connection = $connector->factory($params);
            $connector->connect();
            $connection = $connector->getConnection();
        }

        // if (isset()) {

        // }


        if ( ! empty($matches['new'])) {
            $connector = CacheConnectionProvider::getInstance();  // Register all Connectors as shared services
            $connection = $connector->getConnection($params);
            $connection = $connection->setParameters();
        }      
        return $connection;
    }
}

// END MongoServiceProvider Class

/* End of file MongoServiceProvider.php */
/* Location: .Obullo/ServiceProvider/MongoServiceProvider.php */