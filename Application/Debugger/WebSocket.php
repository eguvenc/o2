<?php

namespace Obullo\Application\Debugger;

use RuntimeException;
use Obullo\Container\Container;

/**
 * Debugger ajax request handshake
 * 
 * @category  Container
 * @package   Container
 * @author    Obullo Framework <obulloframework@gmail.com>
 * @copyright 2009-2014 Obullo
 * @license   http://opensource.org/licenses/MIT MIT license
 * @link      http://obullo.com/package/container
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
        if (false == preg_match('#(ws:\/\/(?<host>(.*)))(:(?<port>\d+))(?<url>.*?)$#i', $this->c['config']['debugger']['socket'], $matches)) {
            throw new RuntimeException("Debugger socket connection error, example web socket configuration: ws://127.0.0.1:9000");
        }
        $this->host = $matches['host'];
        $this->port = $matches['port'];

        $this->socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
        $this->connect = socket_connect($this->socket, $this->host, $this->port);
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
        setcookie(FRAMEWORK.'_debugger_active_tab', "obulloDebugger-ajax-log", 0, '/');  // Select ajax tab

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
     * Build super globals
     * 
     * @return array
     */
    public function buildSuperGlobals()
    {
        $ENVIRONMENTS = array();
        $ENVIRONMENTS['POST'] = $_POST;
        $ENVIRONMENTS['GET'] = $_GET;
        $ENVIRONMENTS['COOKIE'] = isset($_COOKIE) ? $_COOKIE : [];
        $ENVIRONMENTS['SESSION'] = isset($_SESSION) ? $_SESSION : [];
        $ENVIRONMENTS['SERVER'] = isset($_SERVER) ? $_SERVER : [];

        return $ENVIRONMENTS;
    }

    /**
     * Build environments
     * 
     * @return string
     */
    public function buildEnvTab()
    {
        $ENVIRONMENTS = $this->buildSuperGlobals();

        $ENVIRONMENTS['HTTP_REQUEST'] = $this->c['request']->headers();
        $ENVIRONMENTS['HTTP_RESPONSE'] = headers_list();

        $output = '';
        foreach ($ENVIRONMENTS as $key => $value) {
            $label = (strpos($key, 'HTTP_') === 0) ? $key : '$_'.$key;
            $output.= '<a href="javascript:void(0);" onclick="fireMiniTab(this)" data_target="'.strtolower($key).'" class="fireMiniTab">'.$label.'</a>'."\n";
            $output.= '<div id="'.strtolower($key).'">'."\n";
            $output.= "<table>\n";
            $output.= "<tbody>\n";
            if (empty($value)) {
                $output.= "<tr>\n";
                $output.= "<th>&nbsp;</th>\n";
                $output.= "</tr>\n";
            }
            foreach ($value as $k => $v) {
                $output.= "<tr>\n";
                $output.= "<th>$k</th>\n";
                $output.= "<td>\n";
                if (is_array($v)) {
                    $output.= "<pre><span>".var_export($v, true)."</span></pre>\n";
                } else {
                    $output.= "<pre><span>\"$v\"</span></pre>\n";
                }
                $output.= "</td>\n";
                $output.= "</tr>\n";
            }
            $output.= "</tbody>\n";
            $output.= "</table>\n";
            $output.= "</div>\n";
        }
        return $output;
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
        $envHtml = $this->buildEnvTab();

        $upgrade  = "Request: $type\r\n" .
        "Upgrade: websocket\r\n" .
        "Connection: Upgrade\r\n" .
        "Environment-data: ".base64_encode($envHtml)."\r\n" .
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

/* Location: .Obullo/Application/Debugger/WebSocket.php */