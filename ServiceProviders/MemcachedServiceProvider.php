<?php

namespace Obullo\ServiceProviders;

use Obullo\Container\Container;
use Obullo\ServiceProviders\Connections\MemcachedConnectionProvider;

/**
 * Memcached Service Provider
 *
 * @category  ServiceProvider
 * @package   ServiceProviders
 * @author    Obullo Framework <obulloframework@gmail.com>
 * @copyright 2009-2014 Obullo
 * @license   http://opensource.org/licenses/MIT MIT license
 * @link      http://obullo.com/docs/service_providers
 */
Class MemcachedServiceProvider implements ServiceProviderInterface
{
    /**
     * Connector
     * 
     * @var object
     */
    protected $connector;

    /**
     * Registry
     *
     * @param object $c container
     * 
     * @return void
     */
    public function register(Container $c)
    {
        $this->connector = new MemcachedConnectionProvider($c);    // Register all Connectors as shared services
        $this->connector->register();
    }

    /**
     * Get connection
     * 
     * @param array $params array
     * 
     * @return void
     */
    public function get($params = array())
    {
        return $this->connector->getConnection($params);     // Get existing connection
    }

    /**
     * Create unnamed connection
     * 
     * @param array $params array
     * 
     * @return void
     */
    public function factory($params = array())
    {
        return $this->connector->factory($params);
    }

}

// END MemcachedServiceProvider Class

/* End of file MemcachedServiceProvider.php */
/* Location: .Obullo/ServiceProviders/MemcachedServiceProvider.php */

