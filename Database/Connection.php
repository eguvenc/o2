<?php

namespace Obullo\Database;

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
     * @param object $c        container
     * @param array  $params   configuration array
     * @param array  $commands possible command parameters ( new, return, as .. )
     */
    public function __construct($c, $params, $commands = array())
    {
        $this->c = $c;
        $this->c['config']->load('database');
        $this->params = $params;
        $this->commands = $commands;
    }

    /**
     * Connect to database
     * 
     * @return void
     */
    public function connect()
    {
        $handlers = $this->c['config']['database']['handlers'];
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
        if ($this->c->exists('db')  //  Is service available ?
            AND $this->commands['class'] == 'service/provider/db' // Is this provider request ?
            AND empty($this->commands['new']) 
            AND $this->params['db'] == $defaultDb 
            AND $this->params['provider'] == $defaultProvider
        ) {
            return $this->c->load('return service/db'); // return to current database instance
        }
        $Class = $handlers[$provider];
        return new $Class($this->c, $this->params);
    }

}

// END Connection class

/* End of file Connection.php */
/* Location: .Obullo/Database/Connection.php */