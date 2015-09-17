<?php

namespace Obullo\Service\Providers;

use Obullo\Container\Container;
use Obullo\Service\ServiceProviderInterface;
use Obullo\Service\Providers\Connections\CacheConnectionProvider;

/**
 * Cache Service Provider
 *
 * @category  Provider
 * @package   CacheServiceProvider
 * @author    Obullo Framework <obulloframework@gmail.com>
 * @copyright 2009-2014 Obullo
 * @license   http://opensource.org/licenses/MIT MIT license
 * @link      http://obullo.com/package/service
 */
class CacheServiceProvider implements ServiceProviderInterface
{
    /**
     * Connector
     * 
     * @var object
     */
    public $connector;
    
    /**
     * Registry
     * 
     * @param object $c container
     * 
     * @return void
     */
    public function register(Container $c)
    {
        $this->connector = new CacheConnectionProvider($c);  // Register all Connectors as shared services
    }

    /**
     * Get connection
     * 
     * @param array $params array
     * 
     * @return object
     */
    public function get($params = array())
    {
        return $this->connector->getConnection($params);  // Get existing connection
    }

}

// END CacheServiceProvider Class

/* End of file CacheServiceProvider.php */
/* Location: .Obullo/Service/Providers/CacheServiceProvider.php */