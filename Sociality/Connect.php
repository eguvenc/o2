<?php

namespace Obullo\Sociality;

use LogicException;
use Obullo\Container\Container;
use Obullo\Sociality\Provider\Github;
use Obullo\Sociality\Provider\Google;
use Obullo\Sociality\Provider\Facebook;

/**
 * Socality Connector
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
     * Container
     * 
     * @var object
     */
    protected $c;

    /**
     * Constructor
     * 
     * @param object $c container
     */
    public function __construct(Container $c)
    {
        $this->c = $c;
        $this->config = $this->c['config']->load('sociality/google');
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
            $this->config
        );
    }

    /**
     * Get a driver instance.
     *
     * @param string $driver driver name
     * 
     * @return object
     */
    // public function create($driver)
    // {
    //     $driver = strtolower($driver);
    //     return $this->buildProvider(
    //         'Obullo\Sociality\Provider\\'. ucfirst($driver),
    //         $this->config
    //     );
    // }

    /**
     * Build an OAuth 2 provider instance.
     *
     * @param string $provider provider
     * @param array  $config   configuration
     * 
     * @return \Obullo\Sociality\Provider\Abstract
     */
    protected function buildProvider($provider, $config)
    {
        if (! class_exists($provider)) {
            throw new LogicException("No Sociality driver was specified.");
        }
        return new $provider(
            $this->c,
            $config
        );
    }

    // /**
    //  * Format the Twitter server configuration.
    //  *
    //  * @param array $config configuration
    //  * 
    //  * @return array
    //  */
    // public function formatConfig(array $config)
    // {
    //     return [
    //         'identifier'   => $config['client']['id'],
    //         'secret'       => $config['client']['secret'],
    //         'callback_uri' => $config['redirect']['uris'],
    //     ];
    // }
}
