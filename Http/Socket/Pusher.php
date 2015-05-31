<?php

namespace Obullo\Http\Socket;

use Exception;
use RuntimeException;

/**
 * Pusher Exception
 *
 * Modeled after pusher github library https://github.com/pusher/pusher-http-php (✿◠‿◠)
 * 
 * @category  Debug
 * @package   Debugger
 * @author    Obullo Framework <obulloframework@gmail.com>
 * @copyright 2009-2014 Obullo
 * @license   http://opensource.org/licenses/MIT MIT license
 * @link      http://obullo.com/package/debugger
 */
class PusherException extends Exception
{
}
/**
 * Pusher Instance
 *
 * Modeled after pusher github library https://github.com/pusher/pusher-http-php
 * 
 * @category  Debug
 * @package   Debugger
 * @author    Obullo Framework <obulloframework@gmail.com>
 * @copyright 2009-2014 Obullo
 * @license   http://opensource.org/licenses/MIT MIT license
 * @link      http://obullo.com/package/debugger
 */
class PusherInstance
{
    protected static $instance = null;
    protected static $app_id  = '';
    protected static $secret  = '';
    protected static $api_key = '';

    /**
     * Ignored func
     * 
     * @return void
     */
    private function __construct()
    {

    }
    /**
     * Ignored func
     * 
     * @return void
     */
    private function __clone()
    {

    }

    /**
     * Get pusher instance
     * 
     * @return object
     */
    public static function getPusher()
    {
        if (self::$instance !== null) {
            return self::$instance;
        }
        self::$instance = new Pusher(
            self::$api_key, 
            self::$secret, 
            self::$app_id
        );
        return self::$instance;
    }
}

/**
 * Pusher Main Class
 *
 * Modeled after pusher github library https://github.com/pusher/pusher-http-php (✿◠‿◠)
 * 
 * @category  Debug
 * @package   Debugger
 * @author    Obullo Framework <obulloframework@gmail.com>
 * @copyright 2009-2014 Obullo
 * @license   http://opensource.org/licenses/MIT MIT license
 * @link      http://obullo.com/package/debugger
 */
class Pusher
{
    public static $VERSION = '2.2.2';
    protected $settings = array(
        'scheme' => 'http',
        'host' => 'api.pusherapp.com',
        'port' => 80,
        'timeout' => 30,
        'debug' => false
    );
    protected $logger = null;

    /**
     * Constructor
     * 
     * Initializes a new Pusher instance with key, secret , app ID and channel. 
     * You can optionally turn on debugging for all requests by setting debug to true.
     *  
     * @param string $auth_key auth key
     * @param string $secret   secret
     * @param string $app_id   app id
     * @param mixed  $options  options to configure the Pusher instance.
     * @param string $host     host
     * @param int    $port     port
     * @param int    $timeout  the http timeout
     */
    public function __construct(
        $auth_key,
        $secret,
        $app_id,
        $options = array(),
        $host = null,
        $port = null,
        $timeout = null
    ) {
        $this->checkCompatibility();

        if (is_bool($options) === true ) {  // Start backward compatibility with old constructor 
            $options = array(
                'debug' => $options
            );
        }
        if (! is_null($host)) {
            $match = null;
            preg_match("/(http[s]?)\:\/\/(.*)/", $host, $match);
            if (count($match) === 3) {
                $this->settings[ 'scheme' ] = $match[ 1 ];
                $host = $match[ 2 ];
            }
            $this->settings[ 'host' ] = $host;
            $this->log(
                'Legacy $host parameter provided: ' 
                . $this->settings[ 'scheme' ] + ' host: ' + $this->settings[ 'host' ]
            );
        }

        if (! is_null($port)) {
            $options['port'] = $port;
        }
        if (! is_null($timeout)) {
            $options['timeout'] = $timeout;
        }
        if (isset($options['encrypted'])  // End backward compatibility with old constructor
            && $options['encrypted'] === true 
            && ! isset($options['scheme']) 
            && ! isset($options['port'])
        ) {
            $options['scheme'] = 'https';
            $options['port'] = 443;
        }
        $this->settings['auth_key']     = $auth_key;
        $this->settings['secret']       = $secret;
        $this->settings['app_id']       = $app_id;
        $this->settings['base_path']    = '/apps/' . $this->settings['app_id'];

        foreach ($options as $key => $value) {
            if (isset($this->settings[$key])) { // only set if valid setting/option
                $this->settings[ $key ] = $value;
            }
        }
        // ensure host doesn't have a scheme prefix
        $this->settings[ 'host' ] = preg_replace('/http[s]?\:\/\//', '', $this->settings[ 'host' ], 1);
    }

    /**
     * Fetch the settings.
     * @return array
     */
    public function getSettings()
    {
        return $this->settings;
    }

    /**
     * Set a logger to be informed of internal log messages.
     * 
     * @param object $logger logger
     *
     * @return void
     */
    public function setLogger($logger)
    {
        $this->logger = $logger;
    }

    /**
     * Log message
     * 
     * @param string $msg message
     * 
     * @return void
     */
    protected function log($msg)
    {
        if (is_null($this->logger) == false) {
            $this->logger->log('Pusher: ' . $msg);
        }
    }

    /**
    * Check if the current PHP setup is sufficient to run this class
    *
    * @return void
    */
    protected function checkCompatibility()
    {
        if (! extension_loaded('curl') || ! extension_loaded('json')) {
            throw new PusherException(
                'There is missing dependant extensions - please ensure both cURL and JSON modules are installed'
            );
        }
        if (! in_array('sha256', hash_algos())) {
            throw new PusherException(
                'SHA256 appears to be unsupported - make sure you have support for it, or upgrade your version of PHP.'
            );
        }
    }

    /**
     * Validate number of channels and channel name format.
     *
     * @param array $channels channels
     *
     * @return void
     */ 
    protected function validateChannels($channels)
    {
        if (count($channels) > 100 ) {
            throw new PusherException(
                'An event can be triggered on a maximum of 100 channels in a single call.'
            );
        }
        foreach ($channels as $channel) {
            $this->validateChannel($channel);
        }
    }

    /**
     * Ensure a channel name is valid based on our spec
     *
     * @param string $channel channel
     *
     * @return void
     */
    protected function validateChannel($channel)
    {
        if (! preg_match('/\A[-a-zA-Z0-9_=@,.;]+\z/', $channel)) {
            throw new PusherException(
                'Invalid channel name ' . $channel
            );
        }
    }

    /**
     * Ensure a socket_id is valid based on our spec
     *
     * @param int $socket_id socket_id
     *
     * @return void
     */
    protected function validateSocketId($socket_id)
    {
        if ($socket_id !== null && ! preg_match('/\A\d+\.\d+\z/', $socket_id)) {
            throw new PusherException('Invalid socket ID ' . $socket_id);
        }
    }

    /**
     * Utility function used to create the curl object with common settings
     * 
     * @param string $s_url          url
     * @param string $request_method method
     * @param array  $query_params   data
     * 
     * @return curl ch
     */
    protected function createUrl($s_url, $request_method = 'GET', $query_params = array())
    {
        // Create the signed signature

        $signed_query = Pusher::buildAuthQueryString(
            $this->settings['auth_key'],
            $this->settings['secret'],
            $request_method,
            $s_url,
            $query_params
        );

        $full_url = $this->settings['scheme'] . '://' .
                                $this->settings['host'] . ':' . 
                                $this->settings['port'] . $s_url . '?' . $signed_query;

        $this->log('curl_init( ' . $full_url . ' )');
        $ch = curl_init();  // Set cURL opts and execute request
        
        if ($ch === false ) {
            throw new PusherException('Could not initialise cURL!');
        }
        curl_setopt($ch, CURLOPT_URL, $full_url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array("Content-Type: application/json"));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_TIMEOUT, $this->settings['timeout']);
        return $ch;
    }

    /**
     * Utility function to execute curl and create capture response information.
     * 
     * @param curl $ch object
     *
     * @return array curl response
     */
    protected function execCurl($ch)
    {    
        $response = array();
        $response['body'] = curl_exec($ch);
        $response['status'] = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        $this->log('execCurl response: ' . print_r($response, true));

        if ($response['body'] === false) {
            $this->log('execCurl error: ' . curl_error($ch));
        }
        curl_close($ch);
        return $response;
    }
    
    /**
     * Build the required HMAC'd auth string
     *
     * @param string $auth_key       auth key
     * @param string $auth_secret    secret key
     * @param string $request_method method
     * @param string $request_path   path
     * @param array  $query_params   params
     * @param string $auth_version   version
     * @param string $auth_timestamp time
     * 
     * @return string
     */
    public static function buildAuthQueryString(
        $auth_key,
        $auth_secret,
        $request_method,
        $request_path,
        $query_params = array(),
        $auth_version = '1.0',
        $auth_timestamp = null
    ) {
            $params = array();
            $params['auth_key'] = $auth_key;
            $params['auth_timestamp'] = (is_null($auth_timestamp)?time() : $auth_timestamp);
            $params['auth_version'] = $auth_version;
            
            $params = array_merge($params, $query_params);
            ksort($params);
            $string_to_sign = "$request_method\n" . $request_path . "\n" . Pusher::arrayImplode('=', '&', $params);
            $auth_signature = hash_hmac('sha256', $string_to_sign, $auth_secret, false);
            $params['auth_signature'] = $auth_signature;
            ksort($params);
            $auth_query_string = Pusher::arrayImplode('=', '&', $params);
        return $auth_query_string;
    }
    
    /**
     * Implode an array with the key and value pair giving
     * a glue, a separator between pairs and the array
     * to implode.
     * 
     * @param string $glue      The glue between key and value
     * @param string $separator Separator between pairs
     * @param array  $array     The array to implode
     * 
     * @return string The imploded array
     */
    public static function arrayImplode($glue, $separator, $array)
    {
        if (! is_array($array)) {
            return $array;
        }
        $string = array();
        foreach ( $array as $key => $val ) {
            if (is_array($val)) {
                $val = implode(',', $val);
                $string[] = "{$key}{$glue}{$val}";
            }
        }        
        return implode($separator, $string);
    }

    /**
     * Trigger an event by providing event name and payload.
     * 
     * Optionally provide a socket ID to exclude a client (most likely the sender).
     *
     * @param array  $channels        An array of channel names to publish the event on.
     * @param string $event           event name
     * @param mixed  $data            event data
     * @param int    $socket_id       socket id
     * @param bool   $debug           debug
     * @param bool   $already_encoded already encoded
     * 
     * @return bool|string
     */
    public function trigger($channels, $event, $data, $socket_id = null, $debug = false, $already_encoded = false)
    {
        if (is_string($channels) === true) {
            $this->log('->trigger received string channel "' . $channels . '". Converting to array.');
            $channels = array($channels);
        }
        $this->validateChannels($channels);
        $this->validateSocketId($socket_id);

        $query_params = array();
        $s_url = $this->settings['base_path'] . '/events';      
        $data_encoded = $already_encoded ? $data : json_encode($data);

        $post_params = array();
        $post_params[ 'name' ] = $event;
        $post_params[ 'data' ] = $data_encoded;
        $post_params[ 'channels' ] = $channels;

        if ($socket_id !== null ) {
            $post_params[ 'socket_id' ] = $socket_id;
        }
        $post_value = json_encode($post_params);
        $query_params['body_md5'] = md5($post_value);
        $ch = $this->createUrl($s_url, 'POST', $query_params);

        $this->log('trigger POST: ' . $post_value);

        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $post_value);

        $response = $this->execCurl($ch);

        if ($response['status'] == 200 && $debug == false) {
            return true;
        } elseif ($debug == true || $this->settings['debug'] == true) {
            return $response;
        } else {
            return false;
        }
    }

    /**
     * Fetch channel information for a specific channel.
     *
     * @param string $channel The name of the channel
     * @param array  $params  Additional parameters for the query e.g. $params = array( 'info' => 'connection_count' )
     * 
     * @return object
     */
    public function getChannelInfo($channel, $params = array() )
    {
        $this->validateChannel($channel);
        $response = $this->get('/channels/'. $channel, $params);
        
        if ($response['status' ] == 200) {
            $response = json_decode($response['body']);
        } else {
            $response = false;
        }
        return $response;
    }
    
    /**
     * Fetch a list containing all channels
     * 
     * @param array $params Additional parameters for the query e.g. $params = array( 'info' => 'connection_count' )
     *
     * @return array
     */
    public function getChannels($params = array())
    {
        $response = $this->get('/channels', $params);
        if ($response[ 'status' ] == 200) {
            $response = json_decode($response[ 'body' ]);
            $response->channels = get_object_vars($response->channels);
        } else {
            $response = false;
        }
        return $response;
    }

    /**
     * GET arbitrary REST API resource using a synchronous http client.
     * All request signing is handled automatically.
     *  
     * @param string $path   Path excluding /apps/APP_ID
     * @param array  $params API params     (see http://pusher.com/docs/rest_api)
     *
     * @return See Pusher API docs
     */
    public function get($path, $params = array())
    {
        $s_url = $this->settings['base_path'] . $path;
        $ch = $this->createUrl($s_url, 'GET', $params);
        $response = $this->execCurl($ch);

        if ($response['status'] == 200) {
            $response['result'] = json_decode($response['body'], true);
        } else {
            $response = false;
        }
        return $response;
    }

    /**
     * Creates a socket signature
     * 
     * @param string $channel     socket id
     * @param int    $socket_id   socket id
     * @param string $custom_data custom data
     * 
     * @return string
     */
    public function socketAuth($channel, $socket_id, $custom_data = false)
    {
        $this->validateChannel($channel);
        $this->validateSocketId($socket_id);

        if ($custom_data == true) {
            $signature = hash_hmac('sha256', $socket_id . ':' . $channel . ':' . $custom_data, $this->settings['secret'], false);
        } else {
            $signature = hash_hmac('sha256', $socket_id . ':' . $channel, $this->settings['secret'], false);
        }
        $signature = array ( 'auth' => $this->settings['auth_key'] . ':' . $signature);

        if ($custom_data) {  // Add the custom data if it has been supplied
            $signature['channel_data'] = $custom_data;
        }
        return json_encode($signature);
    }

    /**
     * Creates a presence signature (an extension of socket signing)
     *
     * @param string $channel   channel
     * @param int    $socket_id socket id
     * @param string $user_id   user id
     * @param mixed  $user_info user info
     * 
     * @return string
     */
    public function presenceAuth($channel, $socket_id, $user_id, $user_info = false )
    {
        $user_data = array('user_id' => $user_id);
        if ($user_info == true) {
            $user_data['user_info'] = $user_info;
        }
        return $this->socketAuth($channel, $socket_id, json_encode($user_data));
    }

}

// END Pusher class

/* End of file Pusher.php */
/* Location: .Obullo/Http/Socket/Client/Pusher.php */