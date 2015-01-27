<?php

namespace Obullo\ServiceProvider;

use Obullo\ServiceProvider\MongoConnectionProvider,
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
Class MongoServiceProvider
{
    /**
     * Registry
     *
     * @param object $c      container
     * @param array  $params parameters
     * 
     * @return void
     */
    public function register(Container $c, $params = array())
    {
        if ( ! MongoConnectionProvider::isRegistered()) {  // Just one time register the shared objects
            
            $connector = MongoConnectionProvider::getInstance($c);  // Register all connections as shared services
            $connector->register();                     
        }
        $connector = MongoConnectionProvider::getInstance($c);
        return $connector->getConnection($params);   // Get a connection instance before we registered into container
    }
}

// END MongoServiceProvider Class

/* End of file MongoServiceProvider.php */
/* Location: .Obullo/ServiceProvider/MongoServiceProvider.php */