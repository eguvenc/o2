<?php

namespace Obullo\Provider;

use Obullo\Container\Container;

/**
 * Service Provider Interface
 * 
 * @category  Service
 * @package   Provider
 * @author    Obullo Framework <obulloframework@gmail.com>
 * @copyright 2009-2014 Obullo
 * @license   http://opensource.org/licenses/MIT MIT license
 * @link      http://obullo.com/package/provider
 */
interface ServiceProviderInterface
{
    /**
     * Registry
     *
     * @param object $c       Container
     * @param array  $params  parameters
     * @param array  $matches loader commands
     * 
     * @return void
     */
    public function register(Container $c, $params = array(), $matches = array());
}

// END ServiceProviderInterface class

/* End of file ServiceProviderInterface.php */
/* Location: .Obullo/Provider/ServiceProviderInterface.php */