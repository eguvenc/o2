<?php

namespace Obullo\Service\Providers;

use RuntimeException;
use UnexpectedValueException;
use Obullo\Container\ContainerInterface;
use Obullo\Service\ServiceProviderInterface;

/**
 * Memcached Connection Provider
 * 
 * @category  Connections
 * @package   Service
 * @author    Obullo Framework <obulloframework@gmail.com>
 * @copyright 2009-2015 Obullo
 * @license   http://opensource.org/licenses/MIT MIT license
 * @link      http://obullo.com/package/service
 */
class Memcached extends AbstractProvider implements ServiceProviderInterface
{
    /**
     * Container
     * 
     * @var object
     */
    protected $c;

    /**
     * Memcached config array
     * 
     * @var array
     */
    protected $config;

    /**
     * Memcached extension
     * 
     * @var object
     */
    protected $memcached;

    /**
     * Constructor
     * 
     * Automatically check if the Memcached extension has been installed.
     * 
     * @param string $c container
     */
    public function __construct(ContainerInterface $c)
    {
        $this->c = $c;
        $this->config = $this->c['config']->load('cache/memcached');  // Load memcached configuration file

        $this->setKey('memcached.connection.');

        if (! extension_loaded('memcached')) {
            throw new RuntimeException(
                'The memcached extension has not been installed or enabled.'
            );
        }
        $this->register();
    }

    /**
     * Register all connections as shared services ( It should be run one time )
     * 
     * @return void
     */
    public function register()
    {
        foreach ($this->config['connections'] as $key => $val) {
            $this->c[$this->getKey($key)] = function () use ($val) {
                if (empty($val['host']) || empty($val['port'])) {
                    throw new RuntimeException(
                        'Check your memcached configuration, "host" or "port" key seems empty.'
                    );
                }
                return $this->createConnection($val);
            };
        }
    }

    /**
     * Creates Memcached connections
     * 
     * @param array $value connection array
     * 
     * @return object
     */
    protected function createConnection(array $value)
    {
        if ($value['options']['persistent'] && ! empty($value['options']['pool'])) {
            $this->memcached = new \Memcached($value['options']['pool']);
        } else {
            $this->memcached = new \Memcached;
        }
        if (! $this->memcached->addServer($value['host'], $value['port'], $value['weight'])) {
            throw new RuntimeException(
                sprintf(
                    "Memcached connection error could not connect to host: %s.",
                    $value['host']
                )
            );
        }
        $this->setOptions($value['options']);
        $this->memcached->setOption(\Memcached::OPT_CONNECT_TIMEOUT, $value['options']['timeout']);
        return $this->memcached;
    }

    /**
     * Set parameters
     * 
     * @param array $options parameters
     * 
     * @return void
     */
    protected function setOptions($options = array())
    {
        $prefix = $this->getValue($options, 'prefix');
        $serializer = $this->getValue($options, 'serializer');

        if ($serializer == 'php') {
            $this->memcached->setOption(\Memcached::OPT_SERIALIZER, \Memcached::SERIALIZER_PHP);
        }
        if ($serializer == 'igbinary') {
            $this->enableIgbinary();
        }
        if ($serializer == 'json') {
            $this->enableJson();
        }
        if ($prefix) {
            $this->memcached->setOption(\Memcached::OPT_PREFIX_KEY, $prefix);
        }
    }

    /**
     * Check igbinary is enabled and if yes set serializer
     * 
     * @return void
     */
    protected function enableIgbinary()
    {
        if (! extension_loaded('igbinary')) {
            throw new RuntimeException("Php igbinary extension not enabled on your server.");
        }
        if (! \Memcached::HAVE_IGBINARY) {
            throw new RuntimeException(
                sprintf(
                    "Memcached igbinary support not enabled on your server.<pre>%s</pre>",
                    "Check memcached is configured with --enable-memcached-igbinary option."
                )
            );
        }
        $this->memcached->setOption(\Memcached::OPT_SERIALIZER, \Memcached::SERIALIZER_IGBINARY);
    }

    /**
     * Check json is enabled and if yes set serializer
     * 
     * @return void
     */
    protected function enableJson()
    {
        if (! extension_loaded('json')) {
            throw new RuntimeException("Php json extension not enabled on your server.");
        }
        if (! \Memcached::HAVE_JSON) {
            throw new RuntimeException(
                sprintf(
                    "Memcached json support not enabled on your server.<pre>%s</pre>",
                    "Check memcached is configured with --enable-memcached-json option."
                )
            );
        }
        $this->memcached->setOption(\Memcached::OPT_SERIALIZER, \Memcached::SERIALIZER_JSON);
    }

    /**
     * Get memcached config options
     * 
     * @param array  $options parameters
     * @param string $item    key
     * 
     * @return string
     */
    protected function getValue($options, $item)
    {
        if (empty($options[$item])) {
            return null;
        }
        if ($options[$item] == 'none') {
            return null;
        }
        return $options[$item];
    }

    /**
     * Retrieve shared Memcached connection instance from connection pool
     *
     * @param array $params provider parameters
     * 
     * @return object Memcached
     */
    public function get($params = array())
    {
        if (empty($params['connection'])) {
            throw new RuntimeException(
                sprintf(
                    "Memcached provider requires connection parameter. <pre>%s</pre>",
                    "\$c['app']->provider('memcached')->get(['connection' => 'default']);"
                )
            );
        }
        if (! isset($this->config['connections'][$params['connection']])) {
            throw new UnexpectedValueException(
                sprintf(
                    'Connection key %s doest not exist in your memcached configuration.',
                    $params['connection']
                )
            );
        }
        return $this->c[$this->getKey($params['connection'])];  // return to shared connection
    }

    /**
     * Create a new Memcached connection
     * 
     * If you don't want to add it to config file and you want to create new one.
     * 
     * @param array $params connection parameters
     * 
     * @return object Memcached client
     */
    public function factory($params = array())
    {
        $cid = $this->getKey($this->getConnectionId($params));

        if (! $this->c->has($cid)) { //  create shared connection if not exists
            $this->c[$cid] = function () use ($params) {
                return $this->createConnection($params);
            };
        }
        return $this->c[$cid];
    }

    /**
     * Close all connections
     */
    public function __destruct()
    {
        foreach (array_keys($this->config['connections']) as $key) {
            $key = $this->getKey($key);
            if ($this->c->active($key)) {   // Close any open connections
                $this->c[$key]->quit();     // http://php.net/manual/en/memcached.quit.php
            }
        }
    }
}