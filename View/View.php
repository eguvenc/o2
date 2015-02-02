<?php

namespace Obullo\View;

use Closure,
    Controller,
    Obullo\Layer\Layer,
    Obullo\Container\Container;

/**
 * View Class
 * 
 * @category  View
 * @package   View
 * @author    Obullo Framework <obulloframework@gmail.com>
 * @copyright 2009-2014 Obullo
 * @license   http://opensource.org/licenses/MIT MIT license
 * @link      http://obullo.com/package/view
 */
Class View
{
    public $c;           // Container
    protected $logger;   // Logger instance
    protected $response; // Response instance
    /**
     * Protected variables
     * 
     * @var array
     */
    protected $_boolStack   = array();    // Boolean type view variables
    protected $_arrayStack  = array();    // Array type view variables
    protected $_stringStack = array();    // String type view variables
    protected $_objectStack = array();    // Object type view variables
    protected $_staticVars  = array();    // Static variables ( @BASE, @WEBHOST , @ASSETS )
    protected $_layoutArray;              // Layouts array
    protected $_nestedController = null;  // Nested Controller

    /**
     * Constructor
     * 
     * @param array $c container
     */
    public function __construct(Container $c)
    {
        $this->c = $c;
        $this->response = $this->c['response'];
        $this->_staticVars = array(
            '@BASEURL' => rtrim($c['config']['url']['baseurl'], '/'),
            '@WEBHOST' => rtrim($c['config']['url']['webhost'], '/'),
            '@ASSETS'  => rtrim($c['config']['url']['assets'], '/')
        );
        $this->logger = $this->c->load('logger');
        $this->logger->debug('View Class Initialized');
    }

    /**
     * Fetch view
     * 
     * @param string  $obulloViewFilePath full path
     * @param string  $obulloViewFilename filename
     * @param string  $obulloViewData     mixed data
     * @param boolean $obulloViewInclude  fetch as string or include
     * 
     * @return void
     */
    public function fetch($obulloViewFilePath, $obulloViewFilename, $obulloViewData = null, $obulloViewInclude = true)
    {
        $obulloViewInclude = ($obulloViewData === false) ? false : $obulloViewInclude;
        $fileExtension = substr($obulloViewFilename, strrpos($obulloViewFilename, '.')); // Detect extension ( e.g. '.tpl' )
        $ext = (strpos($fileExtension, '.') === 0) ? '' : '.php';

        $this->assignVariables($obulloViewData);

        extract($this->_stringStack, EXTR_SKIP);
        extract($this->_arrayStack, EXTR_SKIP);
        extract($this->_objectStack, EXTR_SKIP);
        extract($this->_boolStack, EXTR_SKIP);

        ob_start();   // Please open short tags in your php.ini file. ( it must be short_tag = On ).
        include $obulloViewFilePath . $obulloViewFilename . $ext;
        $output = ob_get_clean();
        $output = str_replace(array_keys($this->_staticVars), array_values($this->_staticVars), $output);
        
        if ($obulloViewData === false OR $obulloViewInclude === false) {
            return $output;
        }
        $this->response->appendOutput($output);
        return;
    }

    /**
     * Assign view variables
     * 
     * @param array $obulloViewData view data
     * 
     * @return void
     */
    protected function assignVariables($obulloViewData)
    {
        if (is_array($obulloViewData)) {
            foreach ($obulloViewData as $key => $value) {
                $this->assign($key, $value);
            }
        }
    }

    /**
     * Set variables
     * 
     * @param string $key    view key data
     * @param mixed  $val    mixed
     * @param string $filter set filter or on / off all filters
     * 
     * @return void
     */
    public function assign($key, $val, $filter = null)
    {
        if (is_int($val)) {
            $this->_stringStack[$key] = $val;
            return;
        }
        if (is_string($val)) {
            //  do filter
            
            $this->parseString($key, $val);
            return;
        }
        $this->_arrayStack[$key] = array();  // Create empty array
        if (is_array($val)) {
            if (count($val) == 0) {
                $this->_arrayStack[$key] = array();
            } else {
                foreach ($val as $array_key => $value) {
                    
                    //  do filter
                    //  
                    $this->_arrayStack[$key][$array_key] = $value;
                }
            }
        }
        if (is_object($val)) {
            //  do filter
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
     * Parse string type variables
     * 
     * @param string $key key
     * @param string $val value
     * 
     * @return void
     */
    protected function parseString($key, $val)
    {
        $this->_stringStack[$key] = $val;
        if (strpos($key, '@') === 0) {
            $this->setVar($key, $val);
        }
    }

    // /**
    //  * Use a view layout
    //  * 
    //  * @param string $name layout name
    //  * 
    //  * @return void
    //  */
    // protected function layout($name = 'default')
    // {
    //     if (isset($this->_layoutArray[$name]) AND is_callable($this->_layoutArray[$name])) {
    //         $this->bind($this->_layoutArray[$name]);
    //     }
    //     return $this;
    // }

    // /**
    //  * Run Closure
    //  *
    //  * @param mixed $val closure or string
    //  * 
    //  * @return mixed
    //  */
    // protected function bind($val)
    // {
    //     $closure = Closure::bind($val, $this, get_class());
    //     return $closure();
    // }

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
         * Fetch layout variables
         */
        if (Controller::$instance != null AND method_exists(Controller::$instance, 'useLayer')) {  // Layouts must be run at the top level otherwise layout view file
                                                                                                   // could not load view variables.
            Controller::$instance->useLayer();
        }
        /**
         * IMPORTANT:
         * 
         * Router may not available in some levels, forexample if we define a closure route 
         * which contains the view class, it will not work if router not available in the controller.
         * So first we need check Controller is available if not we use container->router.
         */
        if (Controller::$instance == null) {
            $router = $this->c['router'];
        } else {
            $router = &Controller::$instance->router;
        }
        /**
         * Fetch view ( also it can be nested )
         */
        $return = $this->fetch(
            CONTROLLERS .$router->fetchModule(DS). $router->fetchDirectory() . DS .'view'. DS,
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
        return $this->fetch(APP .'templates'. DS, $filename, $data, $include);
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
        if (class_exists('Controller', false) AND Controller::$instance != null) {
            return Controller::$instance->{$key};
        }
    }

    /**
     * Assign static variables
     * 
     * @param string $name    key
     * @param string $replace value
     * 
     * @return void
     */
    protected function setVar($name, $replace)
    {
        $name = strtoupper($name);
        $name = str_replace('@', '', $name);
        $this->_staticVars['@'.$name] = $replace;
    }

}

// END View Class
/* End of file View.php

/* Location: .Obullo/View/View.php */