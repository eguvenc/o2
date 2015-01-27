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
        $connector = CacheConnectionProvider::getInstance($c);  // Register all Connectors as shared services
        return $connector->getConnection($params);
    }
}

// END MongoServiceProvider Class

/* End of file MongoServiceProvider.php */
/* Location: .Obullo/ServiceProvider/MongoServiceProvider.php */