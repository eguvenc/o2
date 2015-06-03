<?php

namespace Obullo\Service\Providers;

use Obullo\Container\ContainerInterface;
use Obullo\Service\ServiceProviderInterface;
use Obullo\Service\Providers\Connections\DatabaseConnectionProvider;

/**
 * Database Service Provider
 *
 * @category  Provider
 * @package   DatabaseServiceProvider
 * @author    Obullo Framework <obulloframework@gmail.com>
 * @copyright 2009-2014 Obullo
 * @license   http://opensource.org/licenses/MIT MIT license
 * @link      http://obullo.com/package/service
 */
class DatabaseServiceProvider implements ServiceProviderInterface
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
    public function register(ContainerInterface $c)
    {
        $this->connector = new DatabaseConnectionProvider($c);  // Register all Connectors as shared services
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

// END DatabaseServiceProvider Class

/* End of file DatabaseServiceProvider.php */
/* Location: .Obullo/Service/Providers/DatabaseServiceProvider.php */
