<?php

namespace Obullo\Debugger;

use RuntimeException;
use Obullo\Log\Handler\Raw;
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
        $this->socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
        $this->connect = @socket_connect($this->socket, $this->host, $this->port);

        if (isset($_SERVER['argv'][0]) && $_SERVER['argv'][0] == 'task') {  // Ignored for php task debugger init command
            return;
        }
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
        $handler = new Raw($this->c);  // Log raw handler
        $this->lines = $handler->write($data);
        $this->lines = $this->logFormat($this->lines);

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

    // foo.replace(/<br>/g,"\n")

    public function logFormat($str)
    {
        $levelPatterns = array(
            '#\[([0-9\-:\s]+)\]#',  // date
            '#([\w]+\.[\w]+):#',    // channnel.level
            '#-->(.*)#',            // --> message & context data
            '#<p>(.*(Uri Class Initialized\b).*)<\/p>#',
            '#<p>(.*(system.error\b).*)<\/p>#',
            '#<p>(.*(system.warning\b).*)<\/p>#',
            '#<p>(.*(system.notice\b).*)<\/p>#',
            '#<p>(.*(system.emergency\b).*)<\/p>#',
            '#<p>(.*(system.critical\b).*)<\/p>#',
        );
        $levelReplace = array(
            '<p><span class="date">$1</span>',
            '<span class="info">$1</span>',
            ' --> $1</p>',
            '<p class="title">$1</p>',
            '<p class="error">$1</p>',
            '<p class="error">$1</p>',
            '<p class="error">$1</p>',
            '<p class="error">$1</p>',
        );
        return preg_replace($levelPatterns, $levelReplace, $str);
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