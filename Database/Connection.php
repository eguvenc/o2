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
     * Database provider
     * 
     * @var object
     */
    protected $provider;

    /**
     * Constructor
     * 
     * @param object $c      container
     * @param array  $params configuration array
     */
    public function __construct($c, $params)
    {
        $this->c = $c;
        $this->provider = isset($params['provider']) ? $params['provider'] : $c['config']['database']['default']['provider'];
        $this->params = $params;
    }

    /**
     * Connect to database
     * 
     * @return void
     */
    public function connect()
    {
        $class = '\\Obullo\Database\Pdo\\'.ucfirst($this->provider);
        return new $class($this->c, $this->params);
    }

}

// END Connection class

/* End of file Connection.php */
/* Location: .Obullo/Database/Connection.php */