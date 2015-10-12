<?php

namespace Obullo\View;

use Closure;
use Controller;
use Obullo\Layer\Layer;
use Obullo\Log\LoggerInterface;
use Obullo\Config\ConfigInterface;
use Obullo\Container\ContainerInterface;

use Psr\Http\Message\ResponseInterface;

/**
 * View Class
 * 
 * @author    Obullo Framework <obulloframework@gmail.com>
 * @copyright 2009-2015 Obullo
 * @license   http://opensource.org/licenses/MIT MIT license
 */
class View
{
    /**
     * Container
     * 
     * @var object
     */
    protected $c;

    /**
     * Logger
     * 
     * @var object
     */
    protected $logger;

    /**
     * Response
     * 
     * @var object
     */
    protected $response;

    /**
     * Protected variables
     * 
     * @var array
     */
    protected $_boolStack   = array();    // Boolean type view variables
    protected $_arrayStack  = array();    // Array type view variables
    protected $_stringStack = array();    // String type view variables
    protected $_objectStack = array();    // Object type view variables

    /**
     * Constructor
     * 
     * @param object $c        \Obullo\Container\ContainerInterface
     * @param object $response \Pst\Http\Message\ResponseInterface
     * @param object $config   \Obullo\Config\ConfigInterface
     * @param object $logger   \Obullo\Log\LoggerInterface
     */
    public function __construct(ContainerInterface $c, ResponseInterface $response, ConfigInterface $config, LoggerInterface $logger)
    {
        $this->c = $c;
        $this->logger = $logger;
        $this->response = $response;
        $this->logger->debug('View Class Initialized');
    }

    /**
     * Fetch view
     * 
     * @param string  $_OVpath     full path
     * @param string  $_OVfilename filename
     * @param string  $_OVData     mixed data
     * @param boolean $_OVInclude  fetch as string or include
     * 
     * @return void
     */
    public function fetch($_OVpath, $_OVfilename, $_OVData = null, $_OVInclude = true)
    {
        $_OVInclude = ($_OVData === false) ? false : $_OVInclude;
        $fileExtension = substr($_OVfilename, strrpos($_OVfilename, '.')); // Detect extension ( e.g. '.tpl' )
        $ext = (strpos($fileExtension, '.') === 0) ? '' : '.php';

        $this->assignVariables($_OVData);

        extract($this->_stringStack, EXTR_SKIP);
        extract($this->_arrayStack, EXTR_SKIP);
        extract($this->_objectStack, EXTR_SKIP);
        extract($this->_boolStack, EXTR_SKIP);

        ob_start();   // Please open short tags in your php.ini file. ( it must be short_tag = On ).
        include $_OVpath . $_OVfilename . $ext;
        $body = ob_get_clean();
        
        if ($_OVData === false || $_OVInclude === false) {
            return $body;
        }
        $this->response->getBody()->write($body);
        return;
    }

    /**
     * Assign view variables
     * 
     * @param array $_OVData view data
     * 
     * @return void
     */
    protected function assignVariables($_OVData)
    {
        if (is_array($_OVData)) {
            foreach ($_OVData as $key => $value) {
                $this->assign($key, $value);
            }
        }
    }

    /**
     * Set variables
     * 
     * @param mixed $key view key => data or combined array
     * @param mixed $val mixed
     * 
     * @return void
     */
    public function assign($key, $val = null)
    {
        if (is_array($key)) {
            foreach ($key as $_k => $_v) {
                $this->assignVar($_k, $_v);
            }
        } else {
            $this->assignVar($key, $val);
        }
    }

    /**
     * Set variables
     * 
     * @param string $key view key data
     * @param mixed  $val mixed
     * 
     * @return void
     */
    protected function assignVar($key, $val)
    {
        if (is_int($val)) {
            $this->_stringStack[$key] = $val;
            return;
        }
        if (is_string($val)) {
            $this->_stringStack[$key] = $val;
            return;
        }
        $this->_arrayStack[$key] = array();  // Create empty array
        if (is_array($val)) {
            if (count($val) == 0) {
                $this->_arrayStack[$key] = array();
            } else {
                foreach ($val as $array_key => $value) {
                    $this->_arrayStack[$key][$array_key] = $value;
                }
            }
        }
        if (is_object($val)) {
            $this->_objectStack[$key] = $val;
            $this->_arrayStack = array();
            return;
        }
        if (is_bool($val)) {
            $this->_boolStack[$key] = $val;
            $this->_arrayStack = array();
            return;
        }
        $this->_stringStack[$key] = $val;
        $this->_arrayStack = array();
        return;
    }

    /**
     * Load view file from /view folder
     * 
     * @param string  $filename filename
     * @param mixed   $data     array data
     * @param boolean $include  no include ( fetch as string )
     * 
     * @return string                      
     */
    public function load($filename, $data = null, $include = true)
    {
        /**
         * IMPORTANT:
         * 
         * Router may not available in some levels, forexample if we define a closure route 
         * which contains the view class, it will not work if router not available in the controller.
         * So first we need check Controller is available if not we use container->router.
         */
        if (! class_exists('Controller', false) || Controller::$instance == null) {
            $router = $this->c['router'];
        } else {
            $router = &Controller::$instance->router;  // Use nested controller router ( see the Layer package. )
        }
        /**
         * Fetch view ( also it can be nested )
         */
        $return = $this->fetch(
            MODULES .$router->fetchModule('/') . $router->fetchDirectory() .'/view/',
            $filename,
            $data,
            $include
        );
        return $return;
    }

    /**
     * Get view as string
     * 
     * @param string $filename filename
     * @param mixed  $data     array data
     * 
     * @return string
     */
    public function get($filename, $data = null)
    {
        return $this->load($filename, $data, false);
    }

    /**
     * Load view file app / templates folder
     * 
     * @param string  $filename filename
     * @param array   $data     variables
     * @param boolean $include  no include ( fetch as string )
     * 
     * @return string                      
     */
    public function template($filename, $data = null, $include = false)
    {
        return $this->fetch(TEMPLATES, $filename, $data, $include);
    }

    /**
     * Make available controller variables in view files
     * 
     * @param string $key Controller variable name
     * 
     * @return void
     */
    public function __get($key)
    {
        return $this->c[$key];
    }

}