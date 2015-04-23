<?php

namespace Obullo\ServiceProviders;

use Obullo\Container\Container;
use Obullo\ServiceProviders\Connections\AMQPConnectionProvider;

/**
 * AMQP Service Provider
 *
 * @category  ServiceProvider
 * @package   ServiceProviders
 * @author    Obullo Framework <obulloframework@gmail.com>
 * @copyright 2009-2014 Obullo
 * @license   http://opensource.org/licenses/MIT MIT license
 * @link      http://obullo.com/docs/serviceProviders
 */
class AMQPServiceProvider implements ServiceProviderInterface
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
        $this->connector = new AMQPConnectionProvider($c);
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
     * Get undefined new connection
     * 
     * @param array $params array
     * 
     * @return object
     */
    public function factory($params = array())
    {
        return $this->connector->factory($params);  // Get new unknown connection
    }

}

// END AMQPServiceProvider Class

/* End of file AMQPServiceProvider.php */
/* Location: .Obullo/ServiceProviders/AMQPServiceProvider.php */
