<?php

namespace Obullo\View;

use Closure,
    Controller,
    Obullo\Layer\Layer;

/**
 * View Class
 * 
 * @category  View
 * @package   View
 * @author    Obullo Framework <obulloframework@gmail.com>
 * @copyright 2009-2014 Obullo
 * @license   http://www.gnu.org/licenses/gpl-3.0.html GPL Licence
 * @link      http://obullo.com/package/view
 */
Class View
{
    /**
     * Dynamic variable types
     * 
     * @var array
     */
    public $bool = array(); // Boolean type view variables
    public $array = array(); // Array type view variables
    public $string = array(); // String type view variables
    public $object = array(); // Object type view variables

    /**
     * Static variables ( @base, @host , @assets )
     * 
     * @var array
     */
    public $variables = array();

    /**
     * Logger instance
     * 
     * @var object
     */
    public $logger;

    /**
     * Router instance
     * 
     * @var object
     */
    public $router = null;
    
    /**
     * Response instance
     * 
     * @var object
     */
    public $response;

    /**
     * Nested Controller
     * 
     * @var object
     */
    public $nestedController = null;

    /**
     * Constructor
     * 
     * @param array $c      container
     * @param array $params configuration array
     */
    public function __construct($c, $params = array())
    {
        $this->variables = array(
            '@base@' => $c['config']['url']['base'],
            '@host@' => $c['config']['url']['host'],
            '@assets@' => $c['config']['url']['assets']
        );
        $this->c = $c;
        $this->schemes = $params;
        $this->logger = $this->c->load('service/logger');
        $this->response = $this->c->load('response');

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
        $fileExtension = substr($obulloViewFilename, strrpos($obulloViewFilename, '.')); 	// Detect the file extension ( e.g. '.tpl' )
        $ext = (strpos($fileExtension, '.') === 0) ? '' : EXT;

        if (class_exists('Controller', false) AND is_object(Controller::$instance)) {
            foreach (array_keys(get_object_vars(Controller::$instance)) as $key) {	 // This allows to using "$this" variable in all views files.
                $this->{$key} = Controller::$instance->{$key}; 	// e.g. $this->config->getItem('myitem')
            }
        }
        if (is_callable($obulloViewData)) {
            $this->bind($obulloViewData);
        }
        extract($this->string, EXTR_SKIP);
        extract($this->array, EXTR_SKIP);
        extract($this->object, EXTR_SKIP);
        extract($this->bool, EXTR_SKIP);

        ob_start();   // Please open short tags in your php.ini file. ( it must be short_tag = On ).
        include $obulloViewFilePath . $obulloViewFilename . $ext;
        $output = ob_get_clean();

        $this->logger->debug('View file loaded: ' . $obulloViewFilePath . $obulloViewFilename . $ext);

        $output = str_replace(array_keys($this->variables), array_values($this->variables), $output);
        
        if ($obulloViewData === false OR $obulloViewInclude === false) {
            return $output;
        }
        $this->response->appendOutput($output);
        return;
    }

    /**
     * Set variables
     * 
     * @param string $key view key data
     * @param mixed  $val mixed
     * 
     * @return void
     */
    public function assign($key, $val)
    {
        if (is_int($val)) {
            $this->string[$key] = $val;
            return;
        }
        if (is_string($val)) {
            if (strpos($val, '@layer') === 0 ) {
                $matches = explode('.', $val);
                $uri     = $matches[1];
                $param = (isset($matches[2])) ? $matches[2] : '';
                $val   = $this->c->load('layer')->get($uri, $param);
            }
            $this->string[$key] = $val;
            if (strpos($key, '@') === 0) {
                $this->setVar($key, $val);
            }
            return;
        }
        $this->array[$key] = array();

        if (is_array($val)) {
            if (count($val) == 0) {
                $this->array[$key] = array();
            } else {
                foreach ($val as $array_key => $value) {
                    $this->array[$key][$array_key] = $value;
                }
            }
        }
        if (is_object($val)) {
            $this->object[$key] = $val;
            $this->array = array();
            return;
        }
        if (is_bool($val)) {
            $this->bool[$key] = $val;
            $this->array = array();
            return;
        }
        $this->string[$key] = $val;
        $this->array = array();
        return;
    }

    /**
     * Use a view scheme
     * 
     * @param string $name scheme name
     * 
     * @return void
     */
    public function getScheme($name = 'default')
    {
        if (isset($this->schemes[$name]) AND is_callable($this->schemes[$name])) {
            $this->bind($this->schemes[$name]);
        }
        return $this;
    }

    /**
     * Run Closure
     *
     * @param mixed $val closure or string
     * 
     * @return mixed
     */
    public function bind($val)
    {
        $closure = Closure::bind($val, $this, get_class());
        return $closure();
    }

    /**
     * Set nested type controller object
     * 
     * @param Controller $controller nested object reference
     * 
     * @return object $this
     */
    public function nested(Controller $controller)
    {
        $this->nestedController = $controller;
        return $this;
    }

    /**
     * Load view file from /view folder
     * 
     * @param string  $filename        filename
     * @param mixed   $dataOrNoInclude closure data, array data or boolean ( fetch as string )
     * @param boolean $include         no include ( fetch as string )
     * 
     * @return string                      
     */
    public function load($filename, $dataOrNoInclude = null, $include = true)
    {
        /**
         * IMPORTANT:
         * 
         * Router may not available in some levels, we need to always use container object.
         * Forexample if we define a closure route which contains the view class, 
         * it will not work if router not available in the controller.
         * So first we need check router is available if not we user container->router otherwise Controller->router.
         */
        $router = (Controller::$instance == null) ? $this->c->load('router') : Controller::$instance->router;
        /**
         * Is there any nested layer ?
         */
        if (is_object($this->nestedController)) {
            $router = $this->nestedController->router;
        }
        /**
         * Fetch view ( als oit can be nested )
         */
        $return = $this->fetch(
            PUBLIC_DIR .$router->fetchTopDirectory(DS). $router->fetchDirectory() . DS .'view'. DS,
            $filename,
            $dataOrNoInclude,
            $include
        );
        $this->nestedController = null; // Reset nested controller object.
        return $return;
    }

    /**
     * Load view file app / templates folder
     * 
     * @param string  $filename        filename
     * @param mixed   $dataOrNoInclude closure data, array data or boolean ( fetch as string )
     * @param boolean $include         no include ( fetch as string )
     * 
     * @return string                      
     */
    public function template($filename, $dataOrNoInclude = null, $include = false)
    {
        return $this->fetch(APP .'templates'. DS, $filename, $dataOrNoInclude, $include);
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
        $name = str_replace('@', '', $name);
        $this->variables['@'.$name.'@'] = $replace;
    }

}

// END View Class
/* End of file View.php

/* Location: .Obullo/View/View.php */