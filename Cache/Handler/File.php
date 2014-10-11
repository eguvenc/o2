<?php

namespace Obullo\Cache\Handler;

use Obullo\Cache\ArrayContainer,
    RunTimeException;

/**
 * File Caching Class
 *
 * @category  Cache
 * @package   File
 * @author    Obullo Framework <obulloframework@gmail.com>
 * @copyright 2009-2014 Obullo
 * @license   http://www.gnu.org/licenses/gpl-3.0.html GPL Licence
 * @link      http://obullo.com/package/cache
 */
Class File implements HandlerInterface
{
    const SERIALIZER_NONE = 'SERIALIZER_NONE';

    /**
     * Uploaded file path
     * 
     * @var string
     */
    public $filePath;

    /**
     * Connection settings
     * 
     * @var array
     */
    public $params = array();

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
        $serializer = null;
        $this->params = $c->load('config')['cache']['file'];
        $this->container = new ArrayContainer;
        $this->filePath = APP . str_replace('/', DS, trim($this->params['cachePath'], '/')) . DS;

        if ( ! is_writable($this->filePath)) {
            throw new RunTimeException(
                sprintf(
                    ' %s is not writable.', get_class()
                )
            );
        }
    }

    /**
     * Set options fake function
     * 
     * @param array $params config
     *
     * @return void
     */
    public function setOption($params = array()) 
    {
        $params = null;
    }

    /**
     * Get cache data.
     * 
     * @param string $key storeage key
     * 
     * @return object
     */
    public function get($key)
    {
        if ( ! file_exists($this->filePath . $key)) {
            return false;
        }
        if ($value = $this->container->get($key)) {
            return $value['data'];
        }
        $data = file_get_contents($this->filePath . $key);
        $data = unserialize($data);

        $this->container->set($key, $data); // Set to array container

        if (time() > $data['time'] + $data['ttl']) {
            unlink($this->filePath . $key);
            return false;
        }
        return $data['data'];
    }

    /**
     * Verify if the specified key exists.
     * 
     * @param string $key storage key
     * 
     * @return boolean true or false
     */
    public function keyExists($key)
    {
        if ($this->get($key) == false) {
            return false;
        }
        return true;
    }

    /**
     * Replace cache data.
     * 
     * @param string  $key  key
     * @param string  $data string data
     * @param integer $ttl  expiration
     * 
     * @return boolean
     */
    public function replace($key = '', $data = 60, $ttl = 60)
    {
        if ( ! is_array($key)) {
            $this->delete($key);
            $contents = array(
                'time' => time(),
                'ttl'  => $ttl,
                'data' => $data
            );
            $fileName = $this->filePath . $key;
            if ($this->writeData($fileName, $contents)) {
                return true;
            }
            return false;
        }
        return $this->setArray($key, $data);
    }

    /**
     * Set Array
     * 
     * @param array $data data
     * @param int   $ttl  expiration
     *
     * @return void
     */
    public function setArray($data, $ttl)
    {
        if (is_array($data)) {
            foreach ($data as $k => $v) {
                $contents = array(
                    'time' => time(),
                    'ttl'  => $ttl,
                    'data' => $v
                );
                $fileName = $this->filePath . $k;
                $write    = $this->writeData($fileName, $contents);
            }
            if ( ! $write) {
                return false;
            }
            return true;
        }
        return false;
    }

    /**
     * Write data
     *
     * @param string $fileName file name
     * @param array  $contents contents
     * 
     * @return boolean true or false
     */
    public function writeData($fileName, $contents)
    {
        if ( ! $fp = fopen($fileName, 'wb')) {
            return false;
        }
        $serializeData = serialize($contents);
        flock($fp, LOCK_EX);
        fwrite($fp, $serializeData);
        flock($fp, LOCK_UN);
        fclose($fp);
        return true;
    }

    /**
     * Save data
     * 
     * @param string $key  cache key.
     * @param array  $data cache data.
     * @param int    $ttl  expiration time.
     * 
     * @return boolean
     */
    public function set($key = '', $data = 60, $ttl = 60)
    {
        if ( ! is_array($key)) {
            $contents = array(
                'time' => time(),
                'ttl'  => $ttl,
                'data' => $data
            );
            $fileName = $this->filePath . $key;
            if ($this->writeData($fileName, $contents)) {
                return true;
            }
            return false;
        }
        return $this->setArray($key, $data);
    }

    /**
     * Delete
     * 
     * @param string $key cache key.
     * 
     * @return boolean
     */
    public function delete($key)
    {
        return unlink($this->filePath . $key);
    }

    /**
     * Get all keys
     * 
     * @return array
     */
    public function getAllKeys()
    {
        $dh  = opendir($this->filePath);
        while (false !== ($fileName = readdir($dh))) {
            if (substr($fileName, 0, 1) !== '.') {
                $files[] = $fileName;
            }
        }
        return $files;
    }

    /**
     * Get all data
     * 
     * @return array
     */
    public function getAllData()
    {
        $dh  = opendir($this->filePath);
        while (false !== ($fileName = readdir($dh))) {
            if (substr($fileName, 0, 1) !== '.') {
                $temp = file_get_contents($this->filePath . $fileName);
                $temp = unserialize($temp);
                if (time() > $temp['time'] + $temp['ttl']) {
                    unlink($this->filePath . $fileName);
                    return false;
                }
                $data[$fileName] = $temp['data'];
            }
        }
        return (empty($data)) ? null : $data;
    }

    /**
     * Clean all data
     * 
     * @return boolean
     */
    public function flushAll()
    {
        return delete_files($this->filePath);
    }

    /**
     * Cache Info
     * 
     * @param string $type type
     * 
     * @return array
     */
    public function info($type = null)
    {
        $type = null;
        return get_dir_file_info($this->filePath);
    }

    /**
     * Get Meta Data
     * 
     * @param string $key cache key.
     * 
     * @return array otherwise boolean
     */
    public function getMetaData($key)
    {
        if ( ! file_exists($this->filePath . $key)) {
            return false;
        }
        $data = file_get_contents($this->filePath . $key);
        $data = unserialize($data);

        if (is_array($data)) {
            $mtime = filemtime($this->filePath . $key);
            if ( ! isset($data['ttl'])) {
                return false;
            }
            return array(
                'expire' => $mtime + $data['ttl'],
                'mtime' => $mtime
            );
        }
        return false;
    }

    /**
     * Connect to file.
     * 
     * @return void
     */
    public function connect()
    {
        return;
    }

    /**
     * Close the connection
     * 
     * @return void
     */
    public function close()
    {
        return;
    }
}

// END File Class

/* End of file File.php */
/* Location: .Obullo/Cache/Handler/File.php */