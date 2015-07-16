<?php

namespace Obullo\Authentication;

use Obullo\Config\Config;

/**
 * AuthConfig Class
 * 
 * @category  Auth
 * @package   AuthConfig
 * @author    Obullo Framework <obulloframework@gmail.com>
 * @copyright 2009-2014 Obullo
 * @license   http://opensource.org/licenses/MIT MIT license
 * @link      http://obullo.com/package/authentication
 */
class AuthConfig
{
    /**
     * Config parameters
     * 
     * @var $params;
     */
    public static $params;

    /**
     * Set auth configuration
     * 
     * @param object $config \Obullo\Config\Config
     * @param array  $params parameters
     *
     * @return array
     */
    public static function setConfiguration(Config $config, array $params)
    {
        $auth  = $config->load('auth');
        $table = $auth['tables'][$params['db.tablename']];

        $auth['db.id'] = $table['db.id'];
        $auth['db.identifier'] = $table['db.identifier'];
        $auth['db.password'] = $table['db.password'];
        $auth['db.rememberToken'] = $table['db.rememberToken'];

        return self::$params = array_merge($params, $auth);
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
        if ($key == null) {
            return self::$params;
        }
        return isset(self::$params[$key]) ? self::$params[$key] : false;
    }

}