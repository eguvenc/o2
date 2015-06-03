<?php

namespace Obullo\Service\Providers;

use Obullo\Container\ContainerInterface;
use Obullo\Service\ServiceProviderInterface;
use Obullo\Service\Providers\Connections\MemcachedConnectionProvider;

/**
 * Memcached Service Provider
 *
 * @category  Provider
 * @package   MemcachedServiceProvider
 * @author    Obullo Framework <obulloframework@gmail.com>
 * @copyright 2009-2014 Obullo
 * @license   http://opensource.org/licenses/MIT MIT license
 * @link      http://obullo.com/package/service
 */
class MemcachedServiceProvider implements ServiceProviderInterface
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
    public function register(ContainerInterface $c)
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
/* Location: .Obullo/Service/Providers/MemcachedServiceProvider.php */