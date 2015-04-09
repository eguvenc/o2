<?php

namespace Obullo\Task;

use Controller;
use RuntimeException;

class DebuggerController extends Controller
{
    protected $socket;
    protected $connection;
    protected $maxByte = 1048576;  // 1 Mb / 1024 Kb
    protected $clients = array();

    /**
     * Loader
     * 
     * @return void
     */
    public function load()
    {
        self::registerErrorHandler();     // We disable errors otherwise we get socket write errors in ajax response
        self::registerExceptionHandler();
    }

    /**
     * Write iframe
     *  
     * @return void
     */
    public function index()
    {
        ob_implicit_flush();   /* Turn on implicit output flushing so we see what we're getting as it comes in. */

        if (false == preg_match('#(ws:\/\/(?<host>(.*)))(:(?<port>\d+))(?<url>.*?)$#i', $this->c['config']['debugger']['socket'], $matches)) {
            throw new RuntimeException("Debugger socket connection error, example web socket configuration: ws://127.0.0.1:9000");
        }
        $this->connection = $matches;
        $this->socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);   // Create TCP/IP sream socket
        socket_set_option($this->socket, SOL_SOCKET, SO_REUSEADDR, 1);  // Reuseable port
        socket_bind($this->socket, 0, $this->connection['port']);              // Bind socket to specified host
        socket_listen($this->socket);  // Listen to port

        $this->clients = array($this->socket);
        $this->process();
    }

    /**
     * Start background process
     * 
     * @return void
     */
    public function process()
    {
        $host = $this->connection['host'];
        $port = $this->connection['port'];
        $url  = $this->connection['url'];

        while (true) {
            $connections = $this->clients;   // Manage multiple connections
            socket_select($connections, $null, $null, 0);

            if (in_array($this->socket, $connections)) {   // Check for new socket

                if (($newSocket = socket_accept($this->socket)) === false) {
                    echo "socket_accept() failed: " . socket_strerror(socket_last_error($newSocket)) . "\n";
                    break;
                }
                $this->clients[] = $newSocket; // Add socket to client array
                
                $header = socket_read($newSocket, $this->maxByte); // Read data sent by the socket
                $headers = $this->handshake($header, $newSocket, $host, $port, $url); // Perform websocket handshake
                
                if (is_array($headers) AND isset($headers['Request'])) {
                    $this->sendRequest($headers, $newSocket);
                }
                $foundSocket = array_search($this->socket, $connections);
                unset($connections[$foundSocket]);
            }
            $this->readStreamResources($connections);  // Read socket data

        }
        socket_close($this->socket);
    }

    /**
     * Send request to stream
     * 
     * @param array    $headers   request headers
     * @param resource $newSocket socket resource
     * 
     * @return void
     */
    protected function sendRequest($headers, $newSocket)
    {
        $data = ['type' => 'system', 'socket' => intval($newSocket)];

        if (isset($headers['Environment-data'])) {
            $data['env'] = $headers['Environment-data'];
        }
        if ($headers['Request'] == 'Http') {
            $data['message'] = 'HTTP_REQUEST';
            $this->send($data);
        } elseif ($headers['Request'] == 'Ajax') {
            $data['message'] = 'AJAX_REQUEST';
            $this->send($data);
        } elseif ($headers['Request'] == 'Cli') {
            $data['message'] = 'CLI_REQUEST';
            $this->send($data);
        }
    }

    /**
     * Read stream data
     * 
     * @param array $sockets resource array
     * 
     * @return void
     */
    protected function readStreamResources($sockets)
    {
        foreach ($sockets as $changedSocket) {     // Loop through all connected sockets
                
            while (socket_recv($changedSocket, $buf, $this->maxByte, 0) >= 1) {    // Check for any incomming data

                $receivedText = static::unmask($buf);  // Unmask data
                $data = json_decode($receivedText);

                if (is_object($data) AND isset($data->type)) {

                    $message = ['type' => $data->type, 'message' => $data->message];
                    
                    if (isset($data->env)) {
                        $message['env'] = $data->env;
                    }
                    $this->send(json_encode($message));
                }
                break 2; // Exist this loop
            }
            socket_close($changedSocket);  // Don't close the sockets otherwise we socket connection errors.
        }
    }

    /**
     * Send data to all clients
     * 
     * @param string $data data
     * 
     * @return void
     */
    public function send($data)
    {
        $responseText = static::mask(json_encode($data));
        $this->broadcast($responseText);    // Send data
    }

    /**
     * Broadcast message to all connections
     * 
     * @param string $msg message
     * 
     * @return void
     */
    public function broadcast($msg)
    {
        foreach ($this->clients as $socket) {
            @socket_write($socket, $msg, strlen($msg));
        }
    }

    /**
     * Register debugger system as an error handler PHP errors
     * 
     * @return mixed Returns result of set_error_handler
     */
    public static function registerErrorHandler()
    {
        return set_error_handler(
            function ($level, $message, $file, $line) {
                return $level = $message = $file = $line = 0;
                return;
            }
        );
    }

    /**
     * Register logging system as an exception handler to log PHP exceptions
     * 
     * @return boolean
     */
    public static function registerExceptionHandler()
    {
        set_exception_handler(
            function ($exception) {
                return $exception = null;
            }
        );
    }

    /**
     * Encode message for transfer to client.
     * 
     * @param string $text message
     * 
     * @return string
     */
    protected static function mask($text)
    {
        $b1 = 0x80 | (0x1 & 0x0f);
        $length = strlen($text);
        
        if($length <= 125)
            $header = pack('CC', $b1, $length);
        elseif($length > 125 && $length < 65536)
            $header = pack('CCn', $b1, 126, $length);
        elseif($length >= 65536)
            $header = pack('CCNN', $b1, 127, $length);
        return $header.$text;
    }

    /**
     * Unmask incoming framed message
     * 
     * @param string $text message
     * 
     * @return string
     */
    public static function unmask($text)
    {
        $length = ord($text[1]) & 127;
        if ($length == 126) {
            $masks = substr($text, 4, 4);
            $data = substr($text, 8);
        } elseif ($length == 127) {
            $masks = substr($text, 10, 4);
            $data = substr($text, 14);
        } else {
            $masks = substr($text, 2, 4);
            $data = substr($text, 6);
        }
        $text = "";
        for ($i = 0; $i < strlen($data); ++$i) {
            $text .= $data[$i] ^ $masks[$i%4];
        }
        return $text;
    }

    /**
     * Handshake with client
     * 
     * @param array    $header request headers
     * @param resource $socket socket connection
     * @param string   $host   address
     * @param integer  $port   port number
     * @param string   $url    any possible url address
     * 
     * @return boolean
     */
    protected static function handshake($header, $socket, $host, $port, $url)
    {
        $headers = array();
        $lines = preg_split("/\r\n/", $header);
        foreach ($lines as $line) {
            $line = chop($line);
            if (preg_match('/\A(\S+): (.*)\z/', $line, $matches)) {
                $headers[$matches[1]] = $matches[2];
            }
        }
        if (isset($headers['Sec-WebSocket-Key'])) {

            $secKey = $headers['Sec-WebSocket-Key'];
            $secAccept = base64_encode(pack('H*', sha1($secKey . '258EAFA5-E914-47DA-95CA-C5AB0DC85B11')));

            $upgrade  = "HTTP/1.1 101 Web Socket Protocol Handshake\r\n" .
            "Upgrade: websocket\r\n" .
            "Connection: Upgrade\r\n" .
            "WebSocket-Origin: $host\r\n" .
            "WebSocket-Location: ws://$host:$port$url\r\n".
            "Sec-WebSocket-Accept:$secAccept\r\n\r\n";
            socket_write($socket, $upgrade, strlen($upgrade));
            return true;
        }
        return $headers;
    }

}

// END DebuggerController class
/* End of file DebuggerController.php */

/* Location: .Obullo/Task/DebuggerController.php */