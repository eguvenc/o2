<?php

namespace Obullo\ServiceProvider;

use Obullo\ServiceProvider\DatabaseConnectionProvider,
    Obullo\Container\Container;

/**
 * Database Service Provider
 *
 * @category  Provider
 * @package   Database
 * @author    Obullo Framework <obulloframework@gmail.com>
 * @copyright 2009-2014 Obullo
 * @license   http://opensource.org/licenses/MIT MIT license
 * @link      http://obullo.com/docs/service_providers
 */
Class DatabaseServiceProvider
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
        $matches = array();
        if ( ! DatabaseConnectionProvider::isRegistered()) {            // Just one time register the shared objects
            $connector = DatabaseConnectionProvider::getInstance($c);   // Register all Connectors as shared services
            $connector->register();
        }
        $connector = DatabaseConnectionProvider::getInstance($c);       // Register all Connectors as shared services

        if ( ! isset($params['connection'])) {  // Just one time register the shared objects
            return $connector->factory($params);
        }
        return $connector->getConnection($params);
    }
}

// END DatabaseServiceProvider Class

/* End of file DatabaseServiceProvider.php */
/* Location: .Obullo/ServiceProvider/DatabaseServiceProvider.php */
