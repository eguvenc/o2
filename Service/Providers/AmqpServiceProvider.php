<?php

namespace Obullo\Service\Providers;

use Obullo\Container\ContainerInterface;
use Obullo\Service\ServiceProviderInterface;
use Obullo\Service\Providers\Connections\AmqpConnectionProvider;

/**
 * Amqp Service Provider
 *
 * @category  Provider
 * @package   AmqpServiceProvider
 * @author    Obullo Framework <obulloframework@gmail.com>
 * @copyright 2009-2015 Obullo
 * @license   http://opensource.org/licenses/MIT MIT license
 * @link      http://obullo.com/docs/service
 */
class AmqpServiceProvider implements ServiceProviderInterface
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
        $this->connector = new AmqpConnectionProvider($c);
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