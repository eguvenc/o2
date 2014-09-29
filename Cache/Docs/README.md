
## Cache Class

------

Framework features wrappers around some of the most popular forms of fast and dynamic caching. All but file-based caching require specific server requirements, and a Fatal Exception will be thrown if server requirements are not met.

### Initializing the Class

------

```php
$c->load('cache');
$this->cache->method();
```

Once loaded, the Cache object will be available using: <kbd>$this->cache->method()</kbd>

The following functions are available:

### Creating a Cache Data

The easiest way to create cache with default settings

```php
$key  = 'test';
$data = 'cache test';
$ttl  = 20; // default 60 seconds

$this->cache->set($key, $data, $ttl);
```

###### If you provide array data as first parameter you need use second parameter for cache expiration.

```php
$data = array(
	'test'   => 'cache test',
	'test 1' => 'cache test 1'
);
$ttl = 20; // default 60 seconds

$this->cache->set($data, $ttl);
```

### Getting the cached value.

```php
echo $this->cache->get('test');   // Gives: 'cache test'
echo $this->cache->get('test 1'); // Gives: 'cache test 1'
```
Connection settings can be configured within the config file.

##### Directory of Config File

```php
- app
	- config
		-env
			config.php
```
Example config file

```php
/*
|--------------------------------------------------------------------------
| Cache
|--------------------------------------------------------------------------
*/
'cache' =>  array(
   'servers' => array(
	   				array(
	                  'hostname' => '127.0.0.1',
	                  'port'     => '11211',
	                   // 'timeout'  => '2.5'// 2.5 sec timeout, just for redis cache
	                  'weight'   => '1'      // The weight parameter effects the consistent hashing 
	                                         // used to determine which server to read/write keys from.
	              	),
	            )
    'auth' =>  '',                       // connection password
    'cache_path' =>  '/data/temp/cache/',// cache file storage path .data/temp/cache
    'serializer' =>  'SERIALIZER_PHP',   // SERIALIZER_NONE, SERIALIZER_PHP, SERIALIZER_IGBINARY
),
```

For multi-connection, a new connection as a new array(inside the <kbd>servers</kbd> array)  can be included to the <kbd>servers</kbd> array  

Example:

```php
'cache' =>  array(
   'servers' => array(
	   				array(
	                  'hostname' => 'localhost',
	                  'port'     => '11211',
	                   // 'timeout'  => '2.5',
	                  'weight'   => '1'
	              	),
	   				array(
	                  'hostname' => '127.0.0.1',
	                  'port'     => '11211',
	                   // 'timeout'  => '2.5',
	                  'weight'   => '1'
	              	),
    'auth' =>  '',
    'cache_path' =>  '/data/temp/cache/',
    'serializer' =>  'SERIALIZER_PHP',
	),
),
```

### Drivers

-----

#### Alternative PHP Cache Apc

For more information on APC, please see [http://php.net/apc](http://php.net/apc)

The driver definition in the <kbd>services.php</kbd> file needs to be replaced with <kbd>Apc</kbd>.

```php
/*
|--------------------------------------------------------------------------
| Cache
|--------------------------------------------------------------------------
*/
$c['cache'] = function () use ($c) {
     return new Obullo\Cache\Apc($c->load('config')['cache']);
};
```

#### File Cache

The driver definition in the <kbd>services.php</kbd> file needs to be replaced with <kbd>File</kbd>.

```php
$c['cache'] = function () use ($c) {
     return new Obullo\Cache\File($c->load('config')['cache']);
};
```

##### Temp Cache Directory

```php
+ app
+ assets
- data
	- temp
		cache
```

You can change the path by editing it in 'cache_path' config file.

```php
'cache' =>  array(
    'cache_path' =>  '/data/temp/cache/'
),
```

The cache folder should be given the write permission <kbd>chmod 777</kbd>.

#### Memcache

The driver definition in the <kbd>services.php</kbd> file needs to be replaced with <kbd>File</kbd>.

```php
$c['cache'] = function () use ($c) {
     return new Obullo\Cache\Memcache($c->load('config')['cache']);
};
```

##### Connect configuration for Memcache

If you want to establish a connection without the default settings in the config

```php
'cache' =>  array(
   'servers' => array(
                  'hostname' => '127.0.0.1',
                  'port'     => '11211',
                  'weight'   => '1'
                ),
),
```
Under the array servers, you can create multi connection creating nested arrays. 

```php
'cache' =>  array(
   'servers' => array (
	   				array(
	                  'hostname' => 'localhost',
	                  'port'     => '11211',
	                  'weight'   => '1'
	                ),
	                array(
	                  'hostname' => '127.0.0.1',
	                  'port'     => '11211',
	                  'weight'   => '1'
	                ),
                ),
),
```

#### Memcached

The driver definition in the <kbd>services.php</kbd> file needs to be replaced with <kbd>File</kbd>.

```php
$c['cache'] = function () use ($c) {
     return new Obullo\Cache\Memcached($c->load('config')['cache']);
};
```

##### Connect configuration for Memcached

If you want to establish a connection without the default settings in the config

```php
'cache' =>  array(
   'servers' => array(
                  'hostname' => '127.0.0.1',
                  'port'     => '11211',
                  'weight'   => '1'
                ),
),
```
Under the array servers, you can create multi connection creating nested arrays. 

```php
'cache' =>  array(
   'servers' => array (
	   				array(
	                  'hostname' => 'localhost',
	                  'port'     => '11211',
	                  'weight'   => '1'
	                ),
	                array(
	                  'hostname' => '127.0.0.1',
	                  'port'     => '11211',
	                  'weight'   => '1'
	                ),
                ),
),
```

##### $this->cache->setOption(string $option)

You can use this options: <i>Default option <kbd>'SERIALIZER_PHP'</kbd></i>
```
* 'SERIALIZER_PHP' 		// The default PHP serializer.
* 'SERIALIZER_JSON'     // The JSON serializer.
* 'SERIALIZER_IGBINARY' // The » igbinary serializer.
						// Instead of textual representation it stores PHP data structures
						// ++ in a compact binary form,resulting in space and time gains.
```

Set client option.

```php
$this->cache->setOption('SERIALIZER_JSON');
```

##### $this->cache->getOption(string $option)

You can use this constants follow the link: <a href="http://www.php.net/manual/en/memcached.constants.php">php.net/manual/en/memcached.constants.php</a>

Example:

```php
// Memcached::OPT_SERIALIZER

echo $this->cache->getOption('OPT_SERIALIZER');
Gives : 1 // 1 == 'SERIALIZER_PHP'
```

##### $this->cache->set(mixed $key, mixed $data, int optional $expiration)

Set the string or array value

```php
// Simple key -> string value
$this->cache->set('key', 'value');

// Simple set add ttl
$this->cache->set('key','value', 10); // 10 sec expiration time

// Simple set -> array value
$this->cache->set('key', array('testKey' => 'test value', 'testKey2' => 'test value 2'));
```

Set array key

```php
$this->cache->set('example:key', 'value');

// OR

$this->cache->set(array('example', 'key'), 'value');
```

##### $this->cache->get(mixed $key)

Get the string or array value

```php
// Gives 'value'
$this->cache->get('key');

// Automatically converted to the array.
$this->cache->get('example:key');
//Gives
Array
(
    [example] => Array
        (
            [key] => value
        )
)
// Simple get array key.
$this->cache->get(array('example'));
//Gives
Array
(
    [example] => Array
        (
            [key] => value
        )
)
$this->cache->get(array('example' => 'key')); // Wrong use.
//Gives
Array
(
)
$this->cache->get(array('example', 'key'));   // Correct use.
//Gives
Array
(
    [example] => Array
        (
            [key] => value
        )
)
```

#### Redis

If you want to establish a connection without the default settings in the config

```php
'cache' =>  array(
   'servers' => array(
	   				array(
	                  'hostname' => '127.0.0.1',
                      'port'     => '6379',
	                   // 'timeout'  => '2.5',
	                  'weight'   => '1'
	              	),
	'auth'       =>  'foorbared',
	'cache_path' =>  '/data/temp/cache/',
	'serializer' =>  'SERIALIZER_PHP',
	),
),
```

The driver definition in the <kbd>services.php</kbd> file needs to be replaced with <kbd>File</kbd>.

```php
$c['cache'] = function () use ($c) {
     return new Obullo\Cache\Redis($c->load('config')['cache']);
};
```

##### $this->cache->auth(string $password)

Authenticate the connection using a password. Warning: The password is sent in plain-text over the network.

```php
$this->cache->auth('foobared');
```

##### $this->cache->setOption(string $option)

Option types

```php
'SERIALIZER_NONE' 	  // don't serialize data
'SERIALIZER_PHP'	  // use built-in serialize/unserialize
'SERIALIZER_IGBINARY' // use igBinary serialize/unserialize
```

Set client option.

```php
$this->cache->setOption('SERIALIZER_NONE');
```

##### $this->cache->getOption(string $option)

Get client option.

```php
$this->cache->getOption();
```

##### $this->cache->isConnected()

Connected control, return true or false

```php
$this->cache->isConnected();
```

##### $this->cache->set(mixed $key, mixed $data, int optional $expiration)

Set the string or array value

```php
// Simple key -> string value
$this->cache->set('key', 'value');

// Simple set add ttl
$this->cache->set('key','value', 10); // 10 sec expiration time

// Simple set -> array value
$this->cache->set('key', array('testKey' => 'test value', 'testKey2' => 'test value 2'));
```

Set array key

```php
$this->cache->set('example:key', 'value');

// OR

$this->cache->set(array('example' => 'value'));
```

##### $this->cache->get($key)

```php
// Gives 'value'
$this->cache->get('key');

// Gives 'value'
$this->cache->get('example:key');

// Gives 'value'
$this->cache->get(array('example'));
```

##### $this->cache->getLastError()

The last error message (if any)
```php
$this->cache->getLastError();
// "ERR Error compiling script (new function): user_script:1: '=' expected near '-'"
```
Sets an expiration date (a timeout) on an item. pexpire requires a TTL in milliseconds.

##### $this->cache->setTimeout(string $key, int $ttl)

```php
$this->cache->setTimeout('key','60'); // 60 sec
```
##### $this->cache->setType(string $type)

Set Type - Returns the type of data pointed by a given type key.

string: Redis::REDIS_STRING

set: Redis::REDIS_SET

```php
$this->cache->setType('set');
```

##### $this->cache->flushDB()

Remove all keys from the current database. Return boolean always true
```php
$this->cache->flushDB();
```

##### $this->cache->append(string $key, string or array $data)

Append specified string to the string stored in specified key.
```php
$this->cache->set('key', 'value1');
$this->cache->append('key', 'value2'); /* 12 */
$this->cache->get('key'); /* 'value1value2' */
```

##### $this->cache->keyExists(string $key)

Verify if the specified key exists.

```php
$this->cache->set('key', 'value');
$this->cache->keyExists('key'); /*  true */
$this->cache->keyExists('NonExistingKey'); /* false */
```

##### $this->cache->getMultiple(array $key)

Get the values of all the specified keys. If one or more keys dont exist, the array will contain.

```php
$this->cache->set('key1', 'value1');
$this->cache->set('key2', 'value2');
$this->cache->set('key3', 'value3');
$this->cache->getMultiple(array('key1', 'key2', 'key3')); /* array('value1', 'value2', 'value3');
$this->cache->getMultiple(array('key0', 'key1', 'key5')); /* array(`false`, 'value2', `false`);
```

##### $this->cache->getSet(string $key, string or array $data)

Sets a value and returns the previous entry at that key.

```php
$this->cache->set('foo', '42');
$this->cache->getSet('foo', 'bar'); // return '42', replaces foo by 'bar'
$this->cache->get('foo')'       	// return 'bar'
```

##### $this->cache->renameKey(string $key, string $newKey)

Renames a key.
```php
$this->cache->set('foo', 'bar');
$this->cache->rename('foo', 'newFoo');
$this->cache->get('newFoo'); // → 42
$this->cache->get('foo');    // → `FALSE`
```

##### $this->cache->getAllData()

Return all key and data
```php
$this->cache->set('test1','test 1 value');
$this->cache->set('test2','test 2 value');
$allData = $this->cache->getAllData();
var_dump($allData); // test1 => test 1 value, test2 => test 2 value
```

##### $this->cache->sAdd(string $key, array $sort)

Adds a value to the set value stored at key. If this value is already in the set, FALSE is returned.

```php
$this->cache->sAdd('key1', 'value1'); /* 1, 'key1' => {'value1'} */
$this->cache->sAdd('key1', array('value2', 'value3')); /* 2, 'key1' => {'value1', 'value2', 'value3'}*/
$this->cache->sAdd('key1', 'value2'); /* 0, 'key1' => {'value1', 'value2', 'value3'}*/
```

##### $this->cache->sort(string $key, array $sort)

Sort the elements in a list, set or sorted set.

```php
$this->cache->delete('test');
$this->cache->sAdd('test', 5);
$this->cache->sAdd('test', 4);
$this->cache->sAdd('test', 2);
$this->cache->sAdd('test', 1);
$this->cache->sAdd('test', 3);

var_dump($this->cache->sort('test')); // 1,2,3,4,5
var_dump($this->cache->sort('test', array('sort' => 'desc'))); // 5,4,3,2,1
var_dump($this->cache->sort('test', array('sort' => 'desc', 'store' => 'out'))); // (int)5
```

##### $this->cache->sSize(string $key)

Returns the cardinality of the set identified by key.

```php
$this->cache->sAdd('key1' , 'test1');
$this->cache->sAdd('key1' , 'test2');
$this->cache->sAdd('key1' , 'test3'); /* 'key1' => {'test1', 'test2', 'test3'}*/
$this->cache->sSize('key1'); /* 3 */
$this->cache->sSize('keyX'); /* 0 */
```

##### $this->cache->sInter(array $key)

Returns the members of a set resulting from the intersection of all the sets held at the specified keys.

```php
$this->cache->sAdd('key1', 'val1');
$this->cache->sAdd('key1', 'val2');
$this->cache->sAdd('key1', 'val3');
$this->cache->sAdd('key1', 'val4');

$this->cache->sAdd('key2', 'val3');
$this->cache->sAdd('key2', 'val4');

$this->cache->sAdd('key3', 'val3');
$this->cache->sAdd('key3', 'val4');

var_dump($redis->sInter('key1', 'key2', 'key3'));
/*
 * Output
	array (size=2)
	  0 => string 'val4' (length=4)
	  1 => string 'val3' (length=4)
 */
```

##### $this->cache->sGetMembers(string $key)

Returns the contents of a set.

```php
$this->cache->delete('key');
$this->cache->sAdd('key', 'val1');
$this->cache->sAdd('key', 'val2');
$this->cache->sAdd('key', 'val1');
$this->cache->sAdd('key', 'val3');

var_dump($this->cache->sGetMembers('key'));
/*
Gives
	array (size=3)
	  0 => string 'val3' (length=4)
	  1 => string 'val2' (length=4)
	  2 => string 'val1' (length=4)
 */
```

#### The methods below ara available for all drivers.

---

### $this->cache->set(string $key, string $data, int $expiration);

```php
$this->cache->set('test','cache test', 20); // default 60 seconds
```

### $this->cache->get(string $key);

You can get the saved values using this function.

```php
$this->cache->get($key);
```

### $this->cache->getMetaData(string $key);

You can reach the meta information of data with this function.

```php
$this->cache->getMetaData($key);
```

### $this->cache->delete(string $key);

Deletes the data of the specified key.

```php
$this->cache->delete($key);
```

### $this->cache->flushAll();

Cleans the memory completely.

```php
$this->cache->flushAll();
```

### Complete Example Config

Using <kbd>memcached</kbd> cache, we make a sample with the default settings:

```php
$data = array('test' => 'cache test');
$ttl  = 20; // default 60 seconds

$this->cache->set($data, $ttl);
$this->cache->get('test');
$this->cache->delete('test');
$this->cache->flushAll();
```

### Function Reference

-----

#### $this->cache->keyExists($key);

Check the is key exists providing by your key.

#### $this->cache->get($key);

Get cache data providing by your key.

#### $this->cache->set($key, $data, $expiration_time);

Saves a cache data usign your key.

#### $this->cache->getAllKeys();

Gets the all keys, however, only suitable with file, memcached and redis.

#### $this->cache->getAllData();

Gets the all data, however, only suitable with file, memcached and redis.

#### $this->cache->delete($key);

Deletes the selected key.

#### $this->cache->info();

Get software information installed on your server.

#### $this->cache->getMetaData($key);

Gets the meta information of data of the chosen key.

#### $this->cache->flushAll($key);

Remove all keys from all databases.