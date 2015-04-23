<?php

namespace Obullo\ServiceProviders;

use Obullo\Container\Container;
use Obullo\ServiceProviders\Connections\QueryConnectionProvider;

/**
 * Query Builder Service Provider
 *
 * @category  QueryServiceProvider
 * @package   ServiceProviders
 * @author    Obullo Framework <obulloframework@gmail.com>
 * @copyright 2009-2014 Obullo
 * @license   http://opensource.org/licenses/MIT MIT license
 * @link      http://obullo.com/docs/service_providers
 */
class QueryServiceProvider implements ServiceProviderInterface
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
        $this->connector = new QueryConnectionProvider($c);  // Register all Connectors as shared services
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

// END QueryServiceProvider Class

/* End of file QueryServiceProvider.php */
/* Location: .Obullo/ServiceProviders/QueryServiceProvider.php */
