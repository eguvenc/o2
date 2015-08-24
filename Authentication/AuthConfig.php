<?php

namespace Obullo\Authentication;

use Obullo\Config\ConfigInterface;
use Obullo\Cookie\CookieInterface;
use Obullo\Container\ContainerInterface;

/**
 * AuthConfig Class
 * 
 * @category  Auth
 * @package   AuthConfig
 * @author    Obullo Framework <obulloframework@gmail.com>
 * @copyright 2009-2015 Obullo
 * @license   http://opensource.org/licenses/MIT MIT license
 * @link      http://obullo.com/package/authentication
 */
class AuthConfig
{
    /**
     * Config parameters
     * 
     * @var array
     */
    protected static $params;

    /**
     * Set auth configuration
     * 
     * @param object $config \Obullo\Config\ConfigInterface
     * @param array  $params parameters
     *
     * @return array
     */
    public static function setParameters(ConfigInterface $config, array $params)
    {
        return self::$params = array_merge($params, $config->load('auth'));
    }

    /**
     * Returns to all configurations
     * 
     * @return array
     */
    public static function getParameters()
    {
        return self::$params;
    }

    /**
     * Get configuration items
     * 
     * @param string $key key
     * 
     * @return mixed
     */
    public static function get($key = null)
    {
        return isset(self::$params[$key]) ? self::$params[$key] : false;
    }

}