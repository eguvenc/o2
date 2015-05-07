<?php

namespace Obullo\Flash;

use Obullo\Container\Container;

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
class Session
{
    /**
     * Message key
     */
    const MESSAGE = 'message';
    /**
     * Status constants
     */
    const NOTICE_ERROR = 'error';
    const NOTICE_SUCCESS = 'success';
    const NOTICE_WARNING = 'warning';
    const NOTICE_INFO = 'info';

    /**
     * Container
     * 
     * @var object
     */
    protected $c;

    /**
     * Flash config
     * 
     * @var array
     */
    protected $flash;

    /**
     * Session
     * 
     * @var object
     */
    protected $session;

    /**
     * Notice keys
     * 
     * @var array
     */
    protected $notice = array();

    /**
     * Flashdata key
     * 
     * @var string
     */
    protected $flashdataKey = 'flash';

    /**
     * Constructor
     *
     * @param object $c container
     */
    public function __construct(Container $c) 
    {
        $this->c = $c;
        $this->session = $c['session'];
        $this->flash = $c['config']->load('flash');

        $this->flashdataSweep();  // Delete old flashdata (from last request)
        $this->flashdataMark();   // Marks all new flashdata as old (data will be deleted before next request)
        
        $this->c['logger']->debug('Session Flash Class Initialized');
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
        return str_replace(
            array('{class}','{icon}','{message}'), 
            array($this->flash[$key]['class'], $this->flash[$key]['icon'], $message),
            $this->flash[static::MESSAGE]
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
     * @return object
     */
    public function success($message)
    {
        $this->set(array('notice:success' => $message, 'notice:status' => static::NOTICE_SUCCESS));
        return $this;
    }

    /**
     * Error flash message
     * 
     * @param string $message notice
     *
     * @return object
     */
    public function error($message)
    {
        $this->set(array('notice:error' => $message, 'notice:status' => static::NOTICE_ERROR));
        return $this;
    }

    /**
     * Info flash message
     * 
     * @param string $message notice
     *
     * @return object
     */
    public function info($message)
    {
        $this->set(array('notice:info' => $message, 'notice:status' => static::NOTICE_INFO));
        return $this;
    }

    /**
     * Warning flash message
     * 
     * @param string $message notice
     *
     * @return object
     */
    public function warning($message)
    {
        $this->set(array('notice:warning' => $message, 'notice:status' => static::NOTICE_WARNING));
        return $this;
    }

    /**
     * Add or change flashdata, only available
     * until the next request
     *
     * @param mixed  $newData key or array
     * @param string $newval  value
     * 
     * @return object
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
        return $this;
    }

    /**
     * Fetch a specific flashdata item from the session array
     *
     * @param string $key    you want to fetch
     * @param string $prefix html open tag
     * @param string $suffix html close tag
     * 
     * @return string
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
     * @return object
     */
    public function keep($key)
    {
        $old_flashdataKey = $this->flashdataKey . ':old:' . $key;
        $value = $this->session->get($old_flashdataKey);
        $new_flashdataKey = $this->flashdataKey . ':new:' . $key;
        $this->session->set($new_flashdataKey, $value);
        return $this;
    }

    /**
     * Returns to requested object
     *
     * @param string $name object
     * 
     * @return void
     */
    public function with($name)
    {
        return $this->c[$name];
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