<?php

namespace Obullo\Sociality;

use LogicException;
use Obullo\Config\ConfigInterface;
use Obullo\Session\SessionInterface;

/**
 * Socality Connector
 *
 * This package modeled after Laravel sociality package 
 * 
 * @category  Socality
 * @package   Connect
 * @author    Obullo Framework <obulloframework@gmail.com>
 * @copyright 2009-2015 Obullo
 * @license   http://opensource.org/licenses/MIT MIT license
 * @link      http://obullo.com/package/sociality
 */
class Connect
{
    /**
     * Config
     * 
     * @var object
     */
    protected $config;

    /**
     * Storage
     * 
     * @var object
     */
    protected $storage;

    /**
     * Constructor
     * 
     * @param object $config  \Obullo\Config\ConfigInterface
     * @param object $session \Obullo\Session\SessionInterface
     */
    public function __construct(ConfigInterface $config, SessionInterface $session)
    {
        $this->config  = $config;
        $this->storage = $session;
    }

    /**
     * Get provider
     * 
     * @param string $name provider name
     * 
     * @return provider instance
     */
    public function __get($name)
    {
        $name = strtolower($name);
        if (isset($this->{$name})) {
            return $this->{$name};
        }
        return $this->{$name} = $this->buildProvider(
            'Obullo\Sociality\Provider\\'. ucfirst($name),
            $name
        );
    }

    /**
     * Build an OAuth 2 provider instance.
     *
     * @param string $provider provider class
     * @param array  $name     provider config name
     * 
     * @return \Obullo\Sociality\Provider\Abstract
     */
    protected function buildProvider($provider, $name)
    {
        if (! class_exists($provider)) {
            throw new LogicException("No Sociality driver was specified.");
        }
        return new $provider(
            $this->storage,
            $this->config->load('sociality/'. $name)
        );
    }
}