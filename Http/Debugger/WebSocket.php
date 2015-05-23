<?php

namespace Obullo\Http\Debugger;

use RuntimeException;
use Obullo\Container\Container;

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
    public function __construct(Container $c)
    {
        $this->c = $c;
        if (false == preg_match('#(ws:\/\/(?<host>(.*)))(:(?<port>\d+))(?<url>.*?)$#i', $this->c['config']['http-debugger']['socket'], $matches)) {
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
        $this->socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
        $this->connect = @socket_connect($this->socket, $this->host, $this->port);

        if (isset($_SERVER['argv'][0]) && $_SERVER['argv'][0] == 'task') {  // Ignored for php task debugger init command
            return;
        }
        if ($this->connect == false) {
            $message = "Debugger server is not running. Please run debugger from your console: <pre>php task debugger</pre>";
            if ($this->c['request']->isAjax()) {
                $message = strip_tags($message);
            }
            throw new RuntimeException($message);
        }  
    }

    /**
     * Emit http request data for debugger
     * 
     * @return void
     */
    public function emit()
    {
        if ($this->c['request']->isAjax()) {
            $this->ajaxHandshake();
        } else {
            $this->httpHandshake();
        }
    }

    /**
     * We listen debugger requests if we have detect an ajax request 
     * we send handshake to websocket.
     * 
     * @return void
     */
    public function ajaxHandshake()
    {
        if (isset($_COOKIE['o_debugger_active_tab']) AND $_COOKIE['o_debugger_active_tab'] != 'obulloDebugger-environment') {
            setcookie('o_debugger_active_tab', "obulloDebugger-ajax-log", 0, '/');  // Select ajax tab
        } elseif ( ! isset($_COOKIE['o_debugger_active_tab'])) {
            setcookie('o_debugger_active_tab', "obulloDebugger-ajax-log", 0, '/'); 
        }
        $this->handshake('Ajax');
    }

    /**
     * We listen debugger requests if we have detect an cli request 
     * we send handshake to websocket.
     * 
     * @return void
     */
    public function cliHandshake()
    {
        $this->handshake('Cli');
    }

    /**
     * Http handshake build environment variables
     * 
     * @return void
     */
    public function httpHandshake()
    {
        $this->handshake('Http');
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
        $envtab = new EnvTab($this->c);
        $base64Data = base64_encode($envtab->printHtml());

        $upgrade  = "Request: $type\r\n" .
        "Upgrade: websocket\r\n" .
        "Connection: Upgrade\r\n" .
        "Environment-data: ".$base64Data."\r\n" .
        "WebSocket-Origin: $this->host\r\n" .
        "WebSocket-Location: ws://$this->host:$this->port\r\n";

        if ($this->socket === false OR $this->connect == false) {
            return;
        }
        socket_write($this->socket, $upgrade, strlen($upgrade));
        socket_close($this->socket);
    }
}

// END WebSocket.php File
/* End of file WebSocket.php

/* Location: .Obullo/Http/Debugger/WebSocket.php */