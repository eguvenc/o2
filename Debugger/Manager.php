<?php

namespace Obullo\Debugger;

use RuntimeException;
use Obullo\Log\Handler\Raw;
use Obullo\Container\ContainerInterface;

/**
 * Manager Class
 * 
 * @category  Debug
 * @package   Manager
 * @author    Obullo Framework <obulloframework@gmail.com>
 * @copyright 2009-2014 Obullo
 * @license   http://opensource.org/licenses/MIT MIT license
 * @link      http://obullo.com/package/debugger
 */
class Manager
{
    /**
     * Container class
     * 
     * @var object
     */
    protected $c;

    /**
     * Logger class
     * 
     * @var object
     */
    protected $logger;

    /**
     * Config
     * 
     * @var array
     */
    protected $config;

    /**
     * Constructor
     */
    public function __construct()
    {
        global $c;
        $this->c = $c;
        $this->logger = $c['logger'];
        $this->config = $c['config']->load('logger');
    }

    /**
     * Display logs
     * 
     * @return string echo the log output
     */
    public function printIndex()
    {
        /**
         * View variables
         * 
         * @var string
         */
        $websocketUrl = $this->c['config']['http']['debugger']['socket'];
        $debuggerOff  = (int)$this->c['config']['http']['debugger']['enabled'];
        $debuggerUrl  = $this->c['app']->uri->getBaseUrl(INDEX_PHP.'/debugger/body?o_debugger=1');

        $envtab = new EnvTab($this->c);
        $envHtml = $envtab->printHtml();

        ob_start();
        include_once 'View.php';
        $view = ob_get_clean();
        unset($envHtml);

        return $view;
    }

    /**
     * Ping socket connection
     * 
     * @return int 1 or 0
     */
    public function ping()
    {
        if (false == preg_match(
            '#(ws:\/\/(?<host>(.*)))(:(?<port>\d+))(?<url>.*?)$#i', 
            $this->c['config']['http']['debugger']['socket'], 
            $matches
        )) {
            throw new RuntimeException("Debugger socket connection error, example web socket configuration: ws://127.0.0.1:9000");
        }
        $socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
        $connect = socket_connect($socket, $matches['host'], $matches['port']);
        if ($connect == 1) {
            return 1;
        } else {
            return 0;
        }
    }

}

// END Manager class
/* End of file Manager.php */

/* Location: .Obullo/Debugger/Manager.php */