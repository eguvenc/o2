<?php

namespace Obullo\Memory;

use Exception;

/**
 * Memory Class
 *
 * Control locale machine memory blocks with shared memory ( Shmop )
 * Shmop no requries any extension it comes with your default php
 * installation.
 * 
 * @category  Memory
 * @package   Memory
 * @author    Obullo Framework <obulloframework@gmail.com>
 * @copyright 2009-2014 Obullo
 * @license   http://opensource.org/licenses/MIT MIT license
 * @link      http://obullo.com/package/memory
 * @link      http://stackoverflow.com/questions/8631875/what-does-the-shmop-php-extension-do
 * 
 */
Class Memory
{
    /**
     * Holds the system id for the shared memory block
     *
     * @var int
     */
    protected $id;

    /**
     * Holds the default permission (octal) that will be used in created memory blocks
     *
     * @var int
     */
    protected $perm = 0644;

    /**
     * Constructor
     *
     * @param object $c container
     */
    public function __construct($c)
    {
        $this->logger = $c->load('service/logger');
        $this->logger->debug('Memory Class Initialized');
    }

    /**
     * Create unsigned integer identifier from string
     * 
     * @param string $key identifier
     * 
     * @see http://www.php.net/manual/en/function.crc32.php
     * 
     * @return void
     */
    public function unsigned($key)
    {
        return (int)sprintf("%u", crc32((string)$key));
    }

    /**
     * Checks if a shared memory block with the provided id exists or not
     *
     * In order to check for shared memory existance, we have to open it with
     * reading access. If it doesn't exist, warnings will be cast, therefore we
     * suppress those with the @ operator.
     *
     * @param string $key ID of the shared memory block you want to check
     * 
     * @return boolean True if the block exists, false if it doesn't
     */
    public function exists($key)
    {
        $this->id = @shmop_open($this->unsigned($key), "a", 0, 0);   // We will close the opened connection in destruct.
        return (is_int($this->id)) ? true : false;
    }

    /**
     * Set data with expiration
     * 
     * @param string  $key        identifier
     * @param mixed   $data       mixed  memory data
     * @param integer $expiration ttl
     *
     * @return void
     */
    public function set($key, $data = null, $expiration = 0)
    {
        $this->write($key, $data, $expiration);
    }

    /**
     * Get stored key value
     * 
     * @param string $key identifier
     * 
     * @return mixed boolean or string
     */
    public function get($key)
    {
        $data = $this->read($key);
        if ($data == false) {
            return false;
        }
        return $data;
    }

    /**
     * Reads from a shared memory block
     *
     * @param string $key identifier
     * 
     * @return string The data read from the shared memory block
     */
    public function read($key)
    {
        if ($this->exists($key)) {
            $id    = shmop_open($this->unsigned($key), "a", 0, 0);
            $size  = shmop_size($id);
            $data  = shmop_read($id, 0, $size);
            return $data;
        }
        return false;
    }

    /**
     * Writes on a shared memory block
     *
     * First we check for the block existance, and if it doesn't, we'll create it. Now, if the
     * block already exists, we need to delete it and create it again with a new byte allocation that
     * matches the size of the data that we want to write there. We mark for deletion,  close the semaphore
     * and create it again.
     *
     * @param string  $key        Identifier
     * @param string  $data       The data that you wan't to write into the shared memory block
     * @param integer $expiration ttl
     * 
     * @return void
     */
    public function write($key, $data, $expiration = 0)
    {
        if ($this->exists($key)) {  // If key already exists overwrite to it
            shmop_delete($this->id);
            shmop_close($this->id);
        }
        $this->_write($key, $data, $expiration);
    }

    /**
     * Write
     * 
     * @param string $key  identifier
     * @param string $data data
     * 
     * @return void
     */
    private function _write($key, $data)
    {
        $size = strlen($data);  // We need to get Byte size not character length
        $id = shmop_open($this->unsigned($key), "c", $this->perm, $size); // Create shared memory block
        if ( ! $id) {
            throw new Exception(
                sprintf(
                    'Corrupted memory block don\'t use this key "%s" or restart your server.',
                    $key
                )
            );
            return;
        }
        $shmopBytesWritten = shmop_write($id, $data, 0); // Lets write string into shared memory
        if ($shmopBytesWritten != shmop_size($id)) {     // Get shared memory block's size
            $this->logger->notice('Memory class couldn\'t write the entire length of data.');
        }
        shmop_close($id);
    }

    /**
     * Mark a shared memory block for deletion
     *
     * @param string $key identifier
     * 
     * @return bool
     */
    public function delete($key)
    {
        $id = @shmop_open($this->unsigned($key), "a", $this->perm, 0); 
        if ($id) {
            shmop_delete($id);
            shmop_close($id);
            return true;
        }
        return false;
    }

    /**
     * Gets the current shared memory block permission
     *
     * @return string
     */
    public function getPermission()
    {
        return $this->perm;
    }

    /**
     * Sets the default permission (octal) that will be used in created memory blocks
     *
     * @param string $perm permission, in octal form
     *
     * @return void
     */
    public function setPermission($perm)
    {
        $this->perm = $perm;
    }

    /**
     * Closes the shared memory block and stops manipulation
     *
     * @return void
     */
    public function __destruct()
    {
        if ($this->id != 0) {
            shmop_close($this->id);  // close 
        }
    }

}

// END Memory class

/* End of file Memory.php */
/* Location: .Obullo/Memory/Memory.php */