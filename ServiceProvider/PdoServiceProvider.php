<?php

namespace Obullo\ServiceProvider;

use Obullo\ServiceProvider\PdoConnectionProvider,
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
Class PdoServiceProvider
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
        if ( ! PdoConnectionProvider::isRegistered()) {         // Just one time register the shared objects
            $connector = PdoConnectionProvider::getInstance($c);    // Register all Connectors as shared services
            $connector->register();
        }
        $connector = PdoConnectionProvider::getInstance($c);    // Register all Connectors as shared services

        if ( ! isset($params['connection'])) {                        // Do factory ( creates new connection ) if we have new match
            return $connector->factory($params);
        }
        return $connector->getConnection($params);              // Get a Connector instance before we registered
    }
}

// END MongoServiceProvider Class

/* End of file MongoServiceProvider.php */
/* Location: .Obullo/ServiceProvider/MongoServiceProvider.php */
