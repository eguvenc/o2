<?php

namespace Obullo\Cache;

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
     * Database provider
     * 
     * @var object
     */
    protected $provider;

    /**
     * Container loader commands
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
    public function __construct(Container $c, $params)
    {
        $this->c = $c;
        $this->c['config']->load('cache');  // Load cache configuration file

        $this->provider = empty($params['provider']) ? $c['config']['cache']['default']['provider'] : $params['provider'];
        $this->params = $params;
        $this->commands = $c['config']['provider:cache.commands'];
    }

    /**
     * Connect to database
     * 
     * @return void
     */
    public function connect()
    {
        /**
        * Provider default instance fix.
        * New keyword support.
        * 
        * If default cache already available we need return to old instance.
        * But if new keyword used in loader $this->c->load('new service/provider/cache'); this time we cannot
        * return to old instance.
        */ 
        if ($this->c->exists('cache')  //  Is service available ?
            AND isset($this->params['provider'])
            AND isset($this->params['serializer']) // Is this provider request ?
            AND empty($this->commands['new']) 
            AND $this->provider == $this->c['config']['cache']['default']['provider']
            AND $this->params['serializer'] == $this->c['config']['cache']['default']['serializer']
        ) {
            return $this->c->load('return service/cache'); // return to shared cache service
        }
        $handlers = $this->c['config']['cache']['handlers'];
        
        $Class = $handlers[$this->provider];
        return new $Class($this->c, $this->params['serializer']);
    }

}

// END Connection class

/* End of file Connection.php */
/* Location: .Obullo/Cache/Connection.php */