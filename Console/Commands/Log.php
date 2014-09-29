<?php

namespace Obullo\Console\Commands;

use Obullo\Console\LogFollower;

/**
 * Log Command
 *
 * Follow log data
 * 
 * @category  Console
 * @package   Commands
 * @author    Obullo Framework <obulloframework@gmail.com>
 * @copyright 2009-2014 Obullo
 * @license   http://www.gnu.org/licenses/gpl-3.0.html GPL Licence
 * @link      http://obullo.com/package/console
 */
Class Log implements CommandInterface
{
    /**
     * Container
     * 
     * @var object
     */
    public $c;

    /**
     * Logger
     * 
     * @var object
     */
    public $logger;

    /**
     * Request type = App, Ajax or Cli
     * 
     * @var string
     */
    public $route;

    /**
     * Constructor
     *
     * @param object $c      container
     * @param object $params parameters
     */
    public function __construct($c, array $params = array())
    {
        $this->c = $c;
        $this->route = isset($params[0]) ? $params[0] : 'app';
        $this->logger = $c->load('service/logger');
    }

    /**
     * Print Logo
     * 
     * @return string colorful logo
     */
    public function logo() 
    {
        echo "\33[1;36m".'
        ______  _            _  _
       |  __  || |__  _   _ | || | ____
       | |  | ||  _ || | | || || ||  _ |
       | |__| || |_||| |_| || || || |_||
       |______||____||_____||_||_||____|

        Welcome to Log Manager v2.0 (c) 2014
You are displaying the "app" request logs. To change direction use $php task log "ajax" or "cli".'."\n\033[0m";

    }

    /**
     * Execute command
     * 
     * @return boolean
     */
    public function run()
    {
        $this->logo();
        $followerClass = '\\Obullo\Console\LogFollower\\'.ucfirst($this->logger->getHandlerWriterName());
        $follower = new $followerClass;
        $follower->follow($this->c, $this->route);
    }

}

// END Log class

/* End of file Log.php */
/* Location: .Obullo/Console/Commands/Log.php */