<?php

namespace Obullo\Service\Providers;

use Obullo\Container\ContainerInterface;
use Obullo\Service\ServiceProviderInterface;
use Obullo\Service\Providers\Connections\MongoConnectionProvider;

/**
 * Mongo Service Provider
 *
 * @category  Provider
 * @package   MongoServiceProvider
 * @author    Obullo Framework <obulloframework@gmail.com>
 * @copyright 2009-2014 Obullo
 * @license   http://opensource.org/licenses/MIT MIT license
 * @link      http://obullo.com/package/service
 */
class MongoServiceProvider implements ServiceProviderInterface
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
        $this->connector = new MongoConnectionProvider($c);
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
     * Create unnamed connection
     *
     * @param array $params array
     *
     * @return object
     */
    public function factory($params = array())
    {
        return $this->connector->factory($params);  // Get existing connection
    }
}

// END MongoServiceProvider Class

/* End of file MongoServiceProvider.php */
/* Location: .Obullo/Service/Providers/MongoServiceProvider.php */
