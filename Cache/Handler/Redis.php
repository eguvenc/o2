<?php

namespace Obullo\Cache\Handler;

use Obullo\Cache\ArrayContainer,
    RunTimeException;

/**
 * Redis Caching Class
 *
 * @category  Cache
 * @package   Redis
 * @author    Obullo Framework <obulloframework@gmail.com>
 * @copyright 2009-2014 Obullo
 * @license   http://opensource.org/licenses/MIT
 * @link      http://obullo.com/package/cache
 */
Class Redis implements HandlerInterface
{
    const SERIALIZER_PHP      = 'SERIALIZER_PHP';
    const SERIALIZER_NONE     = 'SERIALIZER_NONE';
    const SERIALIZER_IGBINARY = 'SERIALIZER_IGBINARY';
    const OPTION_SERIALIZER   = 1;      // Redis::OPT_SERIALIZER

    /**
     * Serializer types
     * 
     * @var array
     */
    public $serializerTypes = array(
        self::SERIALIZER_NONE     => 0, // Redis::SERIALIZER_NONE
        self::SERIALIZER_PHP      => 1, // Redis::SERIALIZER_PHP
        self::SERIALIZER_IGBINARY => 2  // Redis::SERIALIZER_IGBINARY
    );

    /**
     * Redis object
     * 
     * @var object
     */
    public $redis;

    /**
     * Connection settings
     * 
     * @var array
     */
    public $params = array();

    /**
     * Current serializer name
     * 
     * @var string
     */
    public $serializer;
    
    /**
     * Array container
     * 
     * @var object
     */
    protected $container;

    /**
     * Constructor
     * 
     * @param array $c          container
     * @param array $serializer serializer type
     */
    public function __construct($c, $serializer = null)
    {
        $this->params = $c->load('config')['cache']['redis'];
        $this->container = new ArrayContainer;

        if ( ! extension_loaded('redis')) {
            throw new RunTimeException(
                sprintf(
                    ' %s driver is not installed.', get_class()
                )
            );
        }
        if ( ! $this->connect()) {
            throw new RunTimeException(
                sprintf(
                    ' %s cache connection failed.', get_class()
                )
            );
        }
        $serializer = empty($serializer) ? $this->params['serializer'] : $serializer;
        $this->setOption($serializer);
    }

    /**
     * Connect to Redis
     * 
     * @return boolean
     */
    public function connect()
    {
        $this->redis = new \Redis;
        $i = 0;
        foreach ($this->params['servers'] as $servers) {
            $i++;
            if ( ! isset($servers['hostname']) AND ! isset($servers['port'])) {
                throw new RunTimeException(
                    sprintf(
                        ' %s connection configuration items hostname or port can\'t be empty.', get_class()
                    )
                );
            }
            $expiration  = (isset($servers['timeout'])) ? $servers['timeout'] : 0;
            if ($i == 1) {
                if ($this->params['persistentConnect'] == 1) {
                    $this->redis->pconnect($servers['hostname'], $servers['port'], $expiration, null, $this->params['reconnectionAttemps']);
                } else {
                    $this->redis->connect($servers['hostname'], $servers['port'], $expiration);
                }
            } else {
                $this->redis->slaveof($servers['hostname'], $servers['port'], $expiration);
            }
            if (isset($this->params['auth']) AND ! empty($this->params['auth'])) {
                $this->auth($this->params['auth']);
            }
        }
        if ($this->isConnected()) {
            return true;
        }
        return false;
    }

    /**
     * Sets serializer options 
     * 
     * 'SERIALIZER_NONE'
     * 'SERIALIZER_PHP'
     * 'SERIALIZER_JSON'
     * 'SERIALIZER_IGBINARY'
     * 
     * @param array $serializer serializer
     * 
     * @return boolean true or false
     */
    public function setOption($serializer)
    {
        switch ($serializer) {
        case static::SERIALIZER_NONE: // don't serialize data
            $this->serializer = $serializer;
            return $this->redis->setOption(static::OPTION_SERIALIZER, $this->serializerTypes[static::SERIALIZER_NONE]);
            break;
        case static::SERIALIZER_PHP: // use built-in serialize/unserialize
            $this->serializer = $serializer;
            $this->redis->setOption(static::OPTION_SERIALIZER, $this->serializerTypes[static::SERIALIZER_PHP]);
            return true;
            break;
        case static::SERIALIZER_IGBINARY: // use igBinary serialize/unserialize
            $this->serializer = $serializer;
            return $this->redis->setOption(static::OPTION_SERIALIZER, $this->serializerTypes[static::SERIALIZER_IGBINARY]);
            break;
        default:
            return false;
            break;
        }
    }

    /**
     * Get current serializer name
     * 
     * @return string serializer name
     */
    public function getSerializer()
    {
        return $this->serializer;
    }

    /**
     * Get client option.
     * 
     * @return string value
     */
    public function getOption()
    {
        return $this->redis->getOption(static::OPTION_SERIALIZER);
    }

    /**
     * Method to determine if a phpredis object thinks it's connected to a server
     * 
     * @return boolean true or false
     */
    public function isConnected()
    {
        return $this->redis->isConnected();
    }

    /**
     * Get last error
     * 
     * @return string with the last returned script based error message, or NULL if there is no error
     */
    public function getLastError()
    {
        return $this->redis->getLastError();
    }

    /**
     * Get last save
     * 
     * @return timestamp the timestamp of the last disk save.
     */
    public function getLastSave()
    {
        return $this->redis->lastSave();
    }

    /**
     * Returns the type of data pointed by a given type key.
     * 
     * @param string $typeKey string set
     * 
     * @return Depending on the type of the data pointed by the type key
     */
    public function setType($typeKey)
    {
        return $this->redis->type($typeKey);
    }

    /**
     * Sets an expiration date (a timeout) on an item. pexpire requires a TTL in milliseconds.
     *
     * @param string $key redis key.
     * @param int    $ttl expiration time.
     * 
     * @return boolean true or false
     */
    public function setTimeout($key, $ttl)
    {
        return $this->redis->setTimeout($key, $ttl);
    }

    /**
     * Get cache data.
     * 
     * @param string $key cache key.
     * 
     * @return mix
     */
    public function get($key)
    {
        if ($value = $this->container->get($key)) {
            return $value;
        }
        $value = $this->redis->get($key);
        $this->container->set($key, $value); // Set to array container
        return $value;
    }

    /**
     * Remove all keys from all databases.
     * 
     * @return boolean always true
     */
    public function flushAll()
    {
        return $this->redis->flushAll();
    }

    /**
     * Remove all keys from the current database.
     * 
     * @return boolean always true
     */
    public function flushDB()
    {
        return $this->redis->flushDB();
    }

    /**
     * Append specified string to the string stored in specified key.
     * 
     * @param string $key  cache key.
     * @param string $data cache data.
     * 
     * @return boolean true or false
     */
    public function append($key, $data)
    {
        return $this->redis->append($key, $data);
    }

    /**
     * Verify if the specified key exists.
     * 
     * @param string $key cache key.
     * 
     * @return boolean true or false
     */
    public function keyExists($key)
    {
        return $this->redis->exists($key);
    }

    /**
     * Get the values of all the specified keys. If one or more keys dont exist, the array will contain
     * 
     * @param array $key cache key.
     * 
     * @return array containing the list of the keys
     */
    public function getMultiple($key)
    {
        if ( ! is_array($key)) {
            return false;
        }
        return $this->redis->mGet($key);
    }

    /**
     * Sets a value and returns the previous entry at that key.
     * 
     * @param string $key  cache key.
     * @param string $data cache data.
     * 
     * @return string the previous value located at this key.
     */
    public function getSet($key, $data)
    {
        return $this->redis->getSet($key, $data);
    }

    /**
     * Renames a key.
     * 
     * @param string $key    cache key.
     * @param string $newKey cache key.
     * 
     * @return boolean true or false
     */
    public function renameKey($key, $newKey)
    {
        return $this->redis->rename($key, $newKey);
    }

    /**
     * Returns the keys that match a certain pattern.
     * 
     * @param string $pattern pattern symbol
     * 
     * @return array the keys that match a certain pattern.
     */
    public function getAllKeys($pattern = '*')
    {
        return $this->redis->keys($pattern);
    }

    /**
     * Get All Data
     * 
     * @return array return all the key and data
     */
    public function getAllData()
    {
        $keys = $this->redis->keys('*');
        if (sizeof($keys) == 0) {
            return $keys;
        }
        foreach ($keys as $v) {
            $getData = $this->redis->get($v);
            if (empty($getData)) {
                $getData = $this->sGetMembers($v);
            }
            $data[$v] = $getData;
        }
        return $data;
    }

    /**
     * Sort the elements in a list, set or sorted set.
     * 
     * @param string $key  cache key.
     * @param array  $sort optional
     * 
     * @return array the keys that match a certain pattern.
     */
    public function sort($key, $sort = array())
    {
        if (count($sort) > 0) {
            return $this->redis->sort($key, $sort);
        }
        return $this->redis->sort($sort);
    }

    /**
     * Adds a value to the set value stored at key. If this value is already in the set, FALSE is returned.
     * 
     * @param string $key  cache key.
     * @param string $data cache data.
     * 
     * @return long the number of elements added to the set.
     */
    public function sAdd($key, $data)
    {
        if (is_array($data)) {
            $data = "'" . implode("','", $data) . "'";
        }
        return $this->redis->sAdd($key, $data);
    }

    /**
     * Returns the cardinality of the set identified by key.
     * 
     * @param string $key cache key.
     * 
     * @return long the cardinality of the set identified by key, 0 if the set doesn't exist.
     */
    public function sSize($key)
    {
        return $this->redis->sCard($key);
    }

    /**
     * Returns the members of a set resulting from the intersection of all the sets held at the specified keys.
     * 
     * @param array $keys cache keys.
     * 
     * @return array contain the result of the intersection between those keys.
     * If the intersection beteen the different sets is empty,
     * the return value will be empty array.
     */
    public function sInter($keys = array())
    {
        if (count($keys) > 0 AND is_array($keys)) {
            return $this->redis->sInter("'" . implode("','", $keys) . "'");
        }
        return false;
    }

    /**
     * Returns the contents of a set.
     * 
     * @param string $key cache key.
     * 
     * @return array of elements, the contents of the set.
     */
    public function sGetMembers($key)
    {
        return $this->redis->sMembers($key);
    }

    /**
     * Adds a value to the hash stored at key. If this value is already in the hash, FALSE is returned.
     * 
     * @param string $key     cache key.
     * @param string $hashKey hash key.
     * @param string $data    cache data.
     * @param int    $ttl     expiration time
     * 
     * @return LONG 1 if value didn't exist and was added successfully, 0 if the value was already present and was replaced, FALSE if there was an error.
     */
    public function hSet($key, $hashKey, $data, $ttl = 0)
    {
        $hSet = $this->redis->hSet($key, $hashKey, $data);
        if ($hSet AND (int)$ttl > 0) {
            $this->redis->setTimeout($key, (int)$ttl);
        }
        return $hSet;
    }

    /**
     * Adds a value to the hash stored at key only if this field isn't already in the hash.
     * 
     * @param string $key     cache key.
     * @param string $hashKey hash key.
     * 
     * @return string The value, if the command executed successfully BOOL FALSE in case of failure.
     */
    public function hGet($key, $hashKey)
    {
        return $this->redis->hGet($key, $hashKey);
    }

    /**
     * Returns the length of a hash, in number of items
     * 
     * @param string $key cache key.
     * 
     * @return LONG the number of items in a hash, FALSE if the key doesn't exist or isn't a hash.
     */
    public function hLen($key)
    {
        return $this->redis->hLen($key);
    }

    /**
     * Removes a value from the hash stored at key. If the hash table doesn't exist, or the key doesn't exist, FALSE is returned.
     * 
     * @param string $key     cache key.
     * @param string $hashKey hash key.
     * 
     * @return boolean TRUE in case of success, FALSE in case of failure
     */
    public function hDel($key, $hashKey)
    {
        return $this->redis->hDel($key, $hashKey);
    }  

    /**
     * Returns the keys in a hash, as an array of strings.
     * 
     * @param string $key cache key.
     * 
     * @return An array of elements, the keys of the hash. This works like PHP's array_keys().
     */
    public function hKeys($key)
    {
        return $this->redis->hKeys($key);
    }

    /**
     * Returns the values in a hash, as an array of strings.
     * 
     * @param string $key cache key.
     * 
     * @return An array of elements, the values of the hash. This works like PHP's array_values().
     */
    public function hVals($key)
    {
        return $this->redis->hVals($key);
    }

    /**
     * Verify if the specified member exists in a key.
     * 
     * @param string $key cache key.
     * 
     * @return boolean If the member exists in the hash table, return TRUE, otherwise return FALSE.
     */
    public function hGetAll($key)
    {
        return $this->redis->hGetAll($key);
    }

    /**
     * Increments the value of a member from a hash by a given amount.
     * 
     * @param string $key    cache key.
     * @param string $member member.
     * @param int    $value  value.
     * 
     * @return long the new value
     */
    public function hIncrBy($key, $member, $value)
    {
        return $this->redis->hIncrBy($key, $member, $value);
    }

    /**
     * Increments the value of a hash member by the provided float value
     * 
     * @param string $key    cache key.
     * @param string $member member.
     * @param float  $value  value.
     * 
     * @return float the new value
     */
    public function hIncrByFloat($key, $member, $value)
    {
        return $this->redis->hIncrByFloat($key, $member, $value);
    }   

    /**
     * Fills in a whole hash. Non-string values are converted to string, using the standard (string) cast. NULL values are stored as empty strings.
     * 
     * @param string $key     cache key.
     * @param array  $members key - value array.
     * @param int    $ttl     expiration
     * 
     * @return bool
     */
    public function hMSet($key, $members, $ttl = 0)
    {
        $hMSet = $this->redis->hMSet($key, $members);
        if ($hMSet AND (int)$ttl > 0) {
            $this->redis->setTimeout($key, (int)$ttl);
        }
        return $hMSet;
    }

    /**
     * Retrieve the values associated to the specified fields in the hash.
     * 
     * @param string $key        cache key.
     * @param array  $memberKeys key - value array
     * 
     * @return Array An array of elements, the values of the specified fields in the hash, with the hash keys as array keys.
     */
    public function hMGet($key, $memberKeys)
    {
        return $this->redis->hMGet($key, $memberKeys);
    }

    /**
     * Authenticate the connection using a password. Warning: The password is sent in plain-text over the network.
     * 
     * @param string $password auth password
     * 
     * @return boolean true or false
     */
    public function auth($password)
    {
        return $this->redis->auth($password);
    }

    /**
     * Set Array
     * 
     * @param array $data cache data.
     * @param int   $ttl  expiration time.
     * 
     * @return boolean
     */
    public function setArray($data, $ttl)
    {
        if (is_array($data)) {
            foreach ($data as $k => $v) {
                $this->redis->set($k, $v, $ttl);
            }
            return $this;
        }
        return false;
    }

    /**
     * Set cache data.
     * 
     * @param mix $key  cache key or data.
     * @param mix $data cache data or default expiration time.
     * @param int $ttl  expiration time
     * 
     * @return boolean
     */
    public function set($key = '', $data = 60, $ttl = 60) // If empty $ttl default timeout unlimited
    {
        if ( ! is_array($key)) {
            return $this->redis->set($key, $data, $ttl);
        }
        return $this->setArray($key, $data);
    }

    /**
     * Remove specified keys.
     * 
     * @param string $key cache key.
     * 
     * @return boolean
     */
    public function delete($key)
    {
        return $this->redis->delete($key);
    }

    /**
     * Replace key value
     * 
     * @param string $key  redis key
     * @param mix    $data cache data
     * @param int    $ttl  sec
     * 
     * @return boolean
     */
    public function replace($key = '', $data = 60, $ttl = 60)
    {
        if ( ! is_array($key)) {
            return $this->redis->set($key, $data, $ttl);
        }
        return $this->setArray($key, $data);
    }

    /**
     * Get software information installed on your server.
     * 
     * For example:
     *     redis_version:2.4.10
     *     used_memory:47015904
     *     used_memory_human:44.84M
     * 
     * @return object
     */
    public function info()
    {
        return $this->redis->info();
    }

    /**
     * Close the connection
     * 
     * @return void
     */
    public function close()
    {
        $this->redis->close();
    }

}

// END Redis Class

/* End of file Redis.php */
/* Location: .Obullo/Cache/Handler/Redis.php */