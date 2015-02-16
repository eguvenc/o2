<?php

namespace Obullo\ServiceProviders;

use Obullo\Container\Container;

/**
 * Database Service Provider
 *
 * @category  ServiceProvider
 * @package   ServiceProviders
 * @author    Obullo Framework <obulloframework@gmail.com>
 * @copyright 2009-2014 Obullo
 * @license   http://opensource.org/licenses/MIT MIT license
 * @link      http://obullo.com/docs/service_providers
 */
Class DatabaseServiceProvider
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
        $connector = new DatabaseConnectionProvider($c);
        $connector->register();
        return $connector->getConnection($params);  // Get existing connection
    }
}

// END DatabaseServiceProvider Class

/* End of file DatabaseServiceProvider.php */
/* Location: .Obullo/ServiceProviders/DatabaseServiceProvider.php */
