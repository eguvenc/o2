<?php

namespace Obullo\Session;

use Obullo\Container\ServiceInterface;
use Obullo\Container\ContainerInterface;

/**
 * Session Manager
 * 
 * @author    Obullo Framework <obulloframework@gmail.com>
 * @copyright 2009-2015 Obullo
 * @license   http://opensource.org/licenses/MIT MIT license
 */
class SessionManager implements ServiceInterface
{
    /**
     * Container class
     * 
     * @var object
     */
    protected $c;

    /**
     * Constructor
     * 
     * @param ContainerInterface $c      container
     * @param array              $params service parameters
     */
    public function __construct(ContainerInterface $c, array $params)
    {
        $this->c = $c;
        $session = $this->c['config']->load('session');
        $session['locale']['timezone'] = $this->c['config']['locale']['timezone'];
        $this->c['session.params'] = array_merge($params, $session);
    }

    /**
     * Register
     * 
     * @return object logger
     */
    public function register()
    {
        $this->c['session'] = function () {

            $name = $this->c['session.params']['provider']['name'];

            return $this->session = new Session(
                $this->c[$name],  // Service Provider
                $this->c['request'],
                $this->c['logger'],
                $this->c['session.params']
            );

        };
    }

}