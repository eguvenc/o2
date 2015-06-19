<?php

namespace Obullo\Debugger;

use RuntimeException;
use Obullo\Log\Handler\Debugger;
use Obullo\Container\ContainerInterface;

/**
 * Debugger Websocket 
 * 
 * Handler requests and do handshake
 * 
 * @category  Debug
 * @package   Debugger
 * @author    Obullo Framework <obulloframework@gmail.com>
 * @copyright 2009-2014 Obullo
 * @license   http://opensource.org/licenses/MIT MIT license
 * @link      http://obullo.com/package/debugger
 */
class WebSocket
{
    /**
     * Container
     * 
     * @var object
     */
    protected $c;

    /**
     * Host
     * 
     * @var string
     */
    protected $host;

    /**
     * Port
     * 
     * @var int
     */
    protected $port;

    /**
     * App log data ( lines )
     *  
     * @var string
     */
    protected $lines;

    /**
     * Web socket
     * 
     * @var object
     */
    protected $socket;

    /**
     * App output
     * 
     * @var string
     */
    protected $output;

    /**
     * Websocket connect
     * 
     * @var boolean
     */
    protected $connect;

    /**
     * Constructor
     * 
     * @param object $c container
     */
    public function __construct(ContainerInterface $c)
    {
        $this->c = $c;
        if (false == preg_match(
            '#(ws:\/\/(?<host>(.*)))(:(?<port>\d+))(?<url>.*?)$#i', 
            $this->c['config']['http']['debugger']['socket'], 
            $matches
        )) {
            throw new RuntimeException("Debugger socket connection error, example web socket configuration: ws://127.0.0.1:9000");
        }
        $this->host = $matches['host'];
        $this->port = $matches['port'];
    }

    /**
     * Connecto debugger server
     * 
     * @return void
     */
    public function connect()
    {
        if (isset($_SERVER['argv'][0]) && substr($_SERVER['argv'][0], -4) == 'task') {  // Ignore for php task commands
            return;                                                                     // we use substr() for Windows and linux support
        }
        $this->socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
        $this->connect = @socket_connect($this->socket, $this->host, $this->port);

        if ($this->connect == false) {
            $message = "Debugger enabled in your config file but server is not running. 
            Disable debugger or run server from your console: <pre>php task debugger</pre>";
            if ($this->c['request']->isAjax()) {
                $message = strip_tags($message);
            }
            throw new RuntimeException($message);
        }  
    }

    /**
     * Emit http request data for debugger
     *
     * @param string $output output
     * @param string $data   log data
     * 
     * @return void
     */
    public function emit($output = null, $data = array())
    {
        $this->output = $output;
        $handler = new Debugger($this->c);  // Log raw handler
        $this->lines = $handler->write($data);

        if ($this->c['request']->isAjax()) {
            if (isset($_COOKIE['o_debugger_active_tab']) && $_COOKIE['o_debugger_active_tab'] != 'obulloDebugger-environment') {
                setcookie('o_debugger_active_tab', "obulloDebugger-ajax-log", 0, '/');  // Select ajax tab
            } elseif (! isset($_COOKIE['o_debugger_active_tab'])) {
                setcookie('o_debugger_active_tab', "obulloDebugger-ajax-log", 0, '/'); 
            }
            $this->handshake('Ajax');
        } elseif ($this->c['request']->isCli()) { 
            $this->handshake('Cli');
        } else {
            $this->handshake('Http');
        }
    }

    /**
     * Retuns to application html output
     * 
     * @return string
     */
    public function getOutput()
    {
        return $this->output;
    }

    /**
     * Send interface request to websocket
     * 
     * @param string $type Ajax or Cli 
     * 
     * @return void
     */
    protected function handshake($type = 'Ajax') 
    {
        $envtab = new EnvTab($this->c, $this->getOutput());
        $base64EnvData = base64_encode($envtab->printHtml());
        $base64LogData = base64_encode($this->lines);

        $upgrade  = "Request: $type\r\n" .
        "Upgrade: websocket\r\n" .
        "Connection: Upgrade\r\n" .
        "Environment-data: ".$base64EnvData."\r\n" .
        "Log-data: ".$base64LogData."\r\n" .
        "WebSocket-Origin: $this->host\r\n" .
        "WebSocket-Location: ws://$this->host:$this->port\r\n";

        if ($this->socket === false || $this->connect == false) {
            return;
        }
        socket_write($this->socket, $upgrade, strlen($upgrade));
        socket_close($this->socket);
    }
}

// END WebSocket.php File
/* End of file WebSocket.php

/* Location: .Obullo/Debugger/WebSocket.php */