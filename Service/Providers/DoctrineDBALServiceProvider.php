<?php

namespace Obullo\Service\Providers;

use Obullo\Container\Container;
use Obullo\Service\ServiceProviderInterface;
use Obullo\Service\Providers\Connections\DoctrineDBALConnectionProvider;

/**
 * Doctrine DBAL Service Provider
 *
 * @category  Provider
 * @package   DoctrineServiceProvider
 * @author    Obullo Framework <obulloframework@gmail.com>
 * @copyright 2009-2014 Obullo
 * @license   http://opensource.org/licenses/MIT MIT license
 * @link      http://obullo.com/package/service
 */
class DoctrineDBALServiceProvider implements ServiceProviderInterface
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
        $this->connector = new DoctrineDBALConnectionProvider($c);  // Register all Connectors as shared services
        $this->connector->register();
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

    /**
     * Create undefined connection
     * 
     * @param array $params array
     * 
     * @return object
     */
    public function factory($params = array())
    {
        return $this->connector->factory($params);  // Get new connection
    }
}

// END DoctrineDBALServiceProvider Class

/* End of file DoctrineDBALServiceProvider.php */
/* Location: .Obullo/Service/Providers/DoctrineDBALServiceProvider.php */
