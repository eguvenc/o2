<?php

namespace Obullo\Database;

use Obullo\Container\Container;

/**
 * Database Connection Manager
 *
 * @category  Database
 * @package   Adapter
 * @author    Obullo Framework <obulloframework@gmail.com>
 * @copyright 2009-2014 Obullo
 * @license   http://opensource.org/licenses/MIT MIT license
 * @link      http://obullo.com/package/database
 */
Class Connection
{
    /**
     * Container
     * 
     * @var object
     */
    protected $c;

    /**
     * Database Config Parameters
     * 
     * @var array
     */
    protected $params;

    /**
     * Provider connection commands
     * 
     * @var array
     */
    protected $commands;

    /**
     * Constructor
     * 
     * @param object $c      container
     * @param array  $params configuration array
     */
    public function __construct($c, $params)
    {
        $this->c = $c;
        $this->c['config']->load('database');
        $this->params = $params;
        $this->commands = $c['config']['provider:db.commands'];  // Get command parameters
    }

    /**
     * Connect to database
     * 
     * @return void
     */
    public function connect()
    { 
        $defaultDb = $this->c['config']['database']['default']['database'];
        $defaultProvider = $this->c['config']['database']['default']['provider'];
        $provider = empty($this->params['provider']) ? $defaultProvider : $this->params['provider'];

        /**
        * Provider default instance fix.
        * New keyword support.
        * 
        * If default database already available we need return to old instance.
        * But if new keyword used in loader $this->c->load('new service/provider/db'); this time we cannot
        * return to old instance.
        */ 
        if ($this->c->exists('db')  //  Is service available, is it loaded before the declaration of provider ?
            AND isset($this->params['db'])      // Do we have a db parameter ?
            AND isset($this->params['provider'])
            AND empty($this->commands['new']) 
            AND $this->params['db'] == $defaultDb 
            AND $this->params['provider'] == $defaultProvider
        ) {
            return $this->c->load('return service/db'); // return to shared database instance
        }
        
        $handlers = $this->c['config']['database']['handlers'];
        $Class = $handlers[$provider];
        return new $Class($this->c, $this->params);
    }

}

// END Connection class

/* End of file Connection.php */
/* Location: .Obullo/Database/Connection.php */