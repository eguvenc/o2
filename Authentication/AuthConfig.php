<?php

namespace Obullo\Authentication;

use Obullo\Http\Response;
use Obullo\Config\Config;
use Obullo\Session\Session;

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
    protected static $params;

    /**
     * Session
     * 
     * @var object
     */
    protected static $session;

    /**
     * Set auth configuration
     * 
     * @param object $config   \Obullo\Config\Config
     * @param object $response \Obullo\Http\Response
     * @param object $session  \Obullo\Session\Session
     * @param array  $params   parameters
     *
     * @return array
     */
    public static function setParameters(Config $config, Response $response, Session $session, array $params)
    {
        $auth  = $config->load('auth');
        $tablename = $params['db.tablename'];

        if (empty($auth['tables'][$tablename])) {
            $response->showError('Service configuration table does not exist.');
        }
        $table = $auth['tables'][$tablename];
        $session->set('auth.db.tablename', $tablename);  // Set current table name to session we use tablename as 
                                                         // global variable. Auth and Guest middleware use this variable.
        self::$session = $session;

        $auth['db.id'] = $table['db.id'];
        $auth['db.identifier'] = $table['db.identifier'];
        $auth['db.password'] = $table['db.password'];
        $auth['db.rememberToken'] = $table['db.rememberToken'];
        $auth['url.login'] = $table['url.login'];

        return self::$params = array_merge($params, $auth);
    }

    /**
     * Returns to all configuration params
     * 
     * @return array
     */
    public static function getParameters()
    {
        return self::$params;
    }

    /**
     * Returns to current tablename stored in session
     *
     * @param string $key key
     * 
     * @return string
     */
    public static function session($key)
    {
        if (self::$session == null) {
            global $c;
            self::$session = $c['session'];
        }
        return self::$session->get('auth.'.$key);
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
            return self::getParameters();
        }
        return isset(self::$params[$key]) ? self::$params[$key] : false;
    }

}