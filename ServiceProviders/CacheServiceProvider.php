<?php

namespace Obullo\ServiceProviders;

use Obullo\ServiceProviders\CacheConnectionProvider,
    Obullo\Container\Container;

/**
 * Cache Service Provider
 *
 * @category  CacheServiceProvider
 * @package   ServiceProviders
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
     * @param object $c      container
     * @param array  $params parameters
     * 
     * @return void
     */
    public function register(Container $c, $params = array())
    {
        $connector = CacheConnectionProvider::getInstance($c);  // Register all Connectors as shared services
        return $connector->getConnection($params);   // Get existing connection
    }
}

// END CacheServiceProvider Class

/* End of file CacheServiceProvider.php */
/* Location: .Obullo/ServiceProviders/CacheServiceProvider.php */
