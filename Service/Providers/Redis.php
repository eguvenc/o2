<?php

namespace Obullo\Service\Providers;

use RuntimeException;
use UnexpectedValueException;
use Obullo\Container\ContainerInterface;
use Obullo\Service\ServiceProviderInterface;

/**
 * Redis Connection Provider
 * 
 * @category  Connections
 * @package   Service
 * @author    Obullo Framework <obulloframework@gmail.com>
 * @copyright 2009-2015 Obullo
 * @license   http://opensource.org/licenses/MIT MIT license
 * @link      http://obullo.com/package/service
 */
class Redis extends AbstractProvider implements ServiceProviderInterface
{
    /**
     * Container
     * 
     * @var object
     */
    protected $c;

    /**
     * Redis config array
     * 
     * @var array
     */
    protected $config;

    /**
     * Redis extension
     * 
     * @var object
     */
    protected $redis;

    /**
     * Default connection items in redis config
     *  
     * @var array
     */
    protected $defaultConnection = array();

    /**
     * Constructor
     * 
     * Automatically check if the Redis extension has been installed.
     * 
     * @param string $c container
     */
    public function __construct(ContainerInterface $c)
    {
        $this->c = $c;
        $this->config = $this->c['config']->load('cache/redis');  // Load redis configuration file
        $this->defaultConnection = $this->config['connections'][key($this->config['connections'])];

        $this->setKey('redis.connection.');

        if (! extension_loaded('redis')) {
            throw new RuntimeException(
                'The redis extension has not been installed or enabled.'
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
                        'Check your redis configuration, "host" or "port" key seems empty.'
                    );
                }
                return $this->createConnection($val);
            };
        }
    }

    /**
     * Creates Redis connections
     * 
     * @param array $value connection values
     * 
     * @return object
     */
    protected function createConnection(array $value)
    {
        $this->redis = new \Redis;
        $timeout = (empty($value['options']['timeout'])) ? 0 : $value['options']['timeout'];

        if (isset($value['options']['persistent']) && $value['options']['persistent']) {
            $this->redis->pconnect($value['host'], $value['port'], $timeout, null, $value['options']['attempt']);
        } else {
            $this->redis->connect($value['host'], $value['port'], $timeout);
        }
        if (! empty($this->defaultConnection['options']['auth'])) {         // Do we need reauth for slaves ? 
            $auth = $this->redis->auth($this->defaultConnection['options']['auth']);
            if (! $auth) {
                throw new RuntimeException("Redis authentication error, wrong password.");
            }
        }
        $this->setOptions($value['options']);
        return $this->redis;
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
        $database = $this->getValue($options, 'database');
        $serializer = $this->getValue($options, 'serializer');

        if ($database) {
            $this->redis->select($database);
        }
        if ($serializer == 'php') {
            $this->redis->setOption(Redis::OPT_SERIALIZER, Redis::SERIALIZER_PHP);
        }
        if ($serializer == 'igbinary') {
            $this->redis->setOption(Redis::OPT_SERIALIZER, Redis::SERIALIZER_IGBINARY);
        }
        if ($prefix) {
            $this->redis->setOption(Redis::OPT_PREFIX, $prefix);
        }
    }

    /**
     * Get redis config options
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
     * Retrieve shared Redis connection instance from connection pool
     *
     * @param array $params provider parameters
     * 
     * @return object Redis
     */
    public function get($params = array())
    {
        if (empty($params['connection'])) {
            throw new RuntimeException(
                sprintf(
                    "Redis provider requires connection parameter. <pre>%s</pre>",
                    "\$c['app']->provider('redis')->get(['connection' => 'default']);"
                )
            );
        }
        if (! isset($this->config['connections'][$params['connection']])) {
            throw new UnexpectedValueException(
                sprintf(
                    'Connection key %s does not exist in your redis configuration.',
                    $params['connection']
                )
            );
        }
        return $this->c[$this->getKey($params['connection'])];  // return to shared connection
    }

    /**
     * Create a new Redis connection
     * 
     * If you don't want to add it to config file and you want to create new one.
     * 
     * @param array $params connection parameters
     * 
     * @return object Redis client
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
            if ($this->c->active($key)) {
                $this->c[$key]->close();
            }
        }
    }
}