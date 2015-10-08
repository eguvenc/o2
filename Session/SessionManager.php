<?php

namespace Obullo\Session;

use Obullo\Container\ContainerInterface;

/**
 * SessionManager Class
 * 
 * @category  Manager
 * @package   SessionManager
 * @author    Obullo Framework <obulloframework@gmail.com>
 * @copyright 2009-2015 Obullo
 * @license   http://opensource.org/licenses/MIT MIT license
 * @link      http://obullo.com/package/session
 */
class SessionManager
{
    /**
     * Container class
     * 
     * @var object
     */
    protected $c;

    /**
     * Service Parameters
     * 
     * @var array
     */
    protected $params;

    /**
     * Session
     * 
     * @var object
     */
    protected $session;

    /**
     * Create classes
     * 
     * @param object $c container
     * 
     * @return object
     */
    public function __construct(ContainerInterface $c)
    {
        $this->c = $c;
    }

    /**
     * Set parameters
     * 
     * @param array $params parameters
     *
     * @return void
     */
    public function setParameters(array $params)
    {
        $session = $this->c['config']->load('session');
        $session['locale']['timezone'] = $this->c['config']['locale']['timezone'];
        $this->params = array_merge($params, $session);
    }

    /**
     * Set logger
     * 
     * @param object $logger LoggerInterface
     *
     * @return void
     */
    public function setLogger($logger)
    {
        $this->logger = $logger;
    }

    /**
     * Returns to session object
     * 
     * @return object
     */
    public function getClass()
    {
        if ($this->session == null) {

            $provider = $this->c['app']->provider($this->params['provider']['name']);

            return $this->session = new Session(
                $provider,
                $this->logger,
                $this->params
            );
        }
        return $this->session;
    }
}