<?php

namespace Obullo\Flash;

/**
 * Flash Session Class
 * 
 * @category  Session
 * @package   Flash
 * @author    Obullo Framework <obulloframework@gmail.com>
 * @copyright 2009-2014 Obullo
 * @license   http://opensource.org/licenses/MIT MIT license
 * @link      http://obullo.com/package/flash
 */
Class Session
{
    /**
     * Session
     * 
     * @var object
     */
    public $session;

    /**
     * Logger
     * 
     * @var object
     */
    public $logger;

    /**
     * Flashdata key
     * 
     * @var string
     */
    public $flashdataKey = 'flash';

    /**
     * Constructor
     *
     * @param object $c container
     */
    public function __construct($c) 
    {
        $this->session = $c->load('session');
        $this->logger = $c->load('service/logger');
        $this->flash = $c->load('config')->load('shared/flash');

        $this->flashdataSweep();  // Delete old flashdata (from last request)
        $this->flashdataMark();   // Marks all new flashdata as old (data will be deleted before next request)
        
        $this->logger->debug('Session Flash Class Initialized');
    }

    /**
     * Retrieves message template
     * 
     * @param string $message flash string
     * @param string $key     flash config key
     * 
     * @return string message with template
     */
    public function template($message, $key = 'error')
    {
        $constant = constant('NOTICE_'.strtoupper($key));
        return str_replace(
            array('{class}','{icon}','{message}'), 
            array($this->flash[$constant]['class'], $this->flash[$constant]['icon'], $message),
            $this->flash[NOTICE_MESSAGE]
        );
    }

    /**
     * Get all outputs of the flash session
     * 
     * @return array
     */
    public function outputArray() 
    {
        $messages = array();
        foreach (array('success', 'error', 'info', 'warning') as $key) {
            $message = $this->get('notice:'.$key);
            if ( ! empty($message)) {
                $messages[] = $this->template($message, $key);
            }
        }
        return $messages;
    }

    /**
     * Get all outputs of the flash session as *string
     *
     * @param string $newline break tag
     * 
     * @return string
     */
    public function output($newline = '<br />')
    {
        $array = $this->outputArray();
        return implode($newline, $array);
    }

    /**
     * Success flash message
     * 
     * @param string $message notice
     *
     * @return void
     */
    public function success($message)
    {
        $this->set(array('notice:success' => $message, 'status:success' => NOTICE_SUCCESS));
    }

    /**
     * Error flash message
     * 
     * @param string $message notice
     *
     * @return void
     */
    public function error($message)
    {
        $this->set(array('notice:error' => $message, 'status:error' => NOTICE_ERROR));
    }

    /**
     * Info flash message
     * 
     * @param string $message notice
     *
     * @return void
     */
    public function info($message)
    {
        $this->set(array('notice:info' => $message, 'status:info' => NOTICE_INFO));
    }

    /**
     * Warning flash message
     * 
     * @param string $message notice
     *
     * @return void
     */
    public function warning($message)
    {
        $this->set(array('notice:warning' => $message, 'status:warning' => NOTICE_INFO));
    }

    /**
     * Add or change flashdata, only available
     * until the next request
     *
     * @param mixed  $newData key or array
     * @param string $newval  value
     * 
     * @return void
     */
    public function set($newData = array(), $newval = '')
    {
        if (is_string($newData)) {
            $newData = array($newData => $newval);
        }
        if (is_array($newData) AND sizeof($newData) > 0) {
            foreach ($newData as $key => $val) {
                $flashdataKey = $this->flashdataKey . ':new:' . $key;
                $this->session->set($flashdataKey, $val);
            }
        }
    }

    /**
     * Fetch a specific flashdata item from the session array
     *
     * @param string $key    you want to fetch
     * @param string $prefix html open tag
     * @param string $suffix html close tag
     * 
     * @return   string
     */
    public function get($key, $prefix = '', $suffix = '')
    {
        $flashdataKey = $this->flashdataKey . ':old:' . $key;
        $value = $this->session->get($flashdataKey);
        if ($value == '') {
            $prefix = '';
            $suffix = '';
        }
        return $prefix . $value . $suffix;
    }

    /**
     * Keeps existing flashdata available to next request.
     *
     * @param string $key session key
     * 
     * @return void
     */
    public function keep($key)
    {
        $old_flashdataKey = $this->flashdataKey . ':old:' . $key;
        $value = $this->session->get($old_flashdataKey);
        $new_flashdataKey = $this->flashdataKey . ':new:' . $key;
        $this->session->set($new_flashdataKey, $value);
    }

    /**
     * Identifies flashdata as 'old' for removal
     * when flashdataSweep() runs.
     * 
     * @return void
     */
    public function flashdataMark()
    {
        $session = $_SESSION;
        foreach ($session as $name => $value) {
            $parts = explode(':new:', $name);
            if (is_array($parts) AND count($parts) === 2) {
                $newName = $this->flashdataKey . ':old:' . $parts[1];
                $this->session->set($newName, $value);
                $this->session->remove($name);
            }
        }
    }

    /**
     * Removes all flashdata marked as 'old'
     *
     * @return void
     */
    public function flashdataSweep()
    {
        $session = $_SESSION;
        foreach ($session as $key => $value) {
            $value = null;
            if (strpos($key, ':old:')) {
                $this->session->remove($key);
            }
        }
    }
}

// END Session.php File
/* End of file Session.php

/* Location: .Obullo/Flash/Session.php */