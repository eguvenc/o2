<?php

namespace Obullo\Container;

use Obullo\Container\ContainerInterface;

/**
 * Service Provider Interface
 * 
 * @category  Interface
 * @package   ServiceProviderInterface
 * @author    Obullo Framework <obulloframework@gmail.com>
 * @copyright 2009-2015 Obullo
 * @license   http://opensource.org/licenses/MIT MIT license
 * @link      http://obullo.com/package/service
 */
interface ServiceProviderInterface
{
    /**
     * Registry
     *
     * @param object $c \Obullo\Container\ContainerInterface
     * 
     * @return void
     */
    public function __construct(ContainerInterface $c);

    /**
     * Get connection
     *
     * @param array $params array
     *
     * @return object
     */
    public function get($params = array());
}