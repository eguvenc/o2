<?php

namespace Obullo\Session\Handler;

/**
 * Session Save Handler Interface
 * 
 * @category  Logger
 * @package   Log
 * @author    Obullo Framework <obulloframework@gmail.com>
 * @copyright 2009-2014 Obullo
 * @license   http://www.gnu.org/licenses/gpl-3.0.html GPL Licence
 * @link      http://obullo.com/package/session
 * @see       http://php.net/session_set_save_handler
 */
interface HandlerInterface
{
    /**
     * Constructor
     *
     * @param array $c      container
     * @param array $params configuration
     *
     * @return void
     */
    public function __construct($c, $params);

    /**
    * Php5 session handler interface open function
    * 
    * @param string $savePath    save path 
    * @param string $sessionName session name
    * 
    * @return bool
    */
    public function open($savePath, $sessionName);

    /**
     * Close the connection. Called by PHP when the script ends.
     * 
     * @return void
     */
    public function close();

    /**
     * Read data from the session.
     * 
     * @param string $id session id
     * 
     * @return mixed
     */
    public function read($id);

    /**
     * Write data to the session.
     * 
     * @param string $id   current session id
     * @param mixed  $data mixed data
     * 
     * @return bool
     */
    public function write($id, $data);

    /**
     * Delete data from the session.
     * 
     * @param string $id current session id
     * 
     * @return bool
     */
    public function destroy($id);

    /**
     * Run garbage collection
     * 
     * @param integer $maxLifetime expration time
     * 
     * @return bool
     */
    public function gc($maxLifetime);

    /**
     * Set expiration of valid session
     * 
     * @param int $ttl lifetime
     * 
     * @return void
     */
    public function setLifetime($ttl);

    /**
     * Get expiration of valid session
     * 
     * @return int
     */
    public function getLifetime();
}

// END HandlerInterface class

/* End of file HandlerInterface.php */
/* Location: .Obullo/Session/Handler/HandlerInterface.php */