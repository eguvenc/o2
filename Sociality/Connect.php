<?php

namespace Obullo\Sociality;

use LogicException;
use Obullo\Container\ContainerInterface;

/**
 * Socality Connector
 *
 * This package modeled after Laravel sociality package 
 * 
 * @category  Socality
 * @package   Connect
 * @author    Obullo Framework <obulloframework@gmail.com>
 * @copyright 2009-2014 Obullo
 * @license   http://opensource.org/licenses/MIT MIT license
 * @link      http://obullo.com/package/session
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
     * @param object $c container
     */
    public function __construct(ContainerInterface $c)
    {
        $this->config  = $c['config'];
        $this->storage = $c['session'];
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

// END Connect.php File
/* End of file Connect.php

/* Location: .Obullo/Sociality/Connect.php */