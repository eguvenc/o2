<?php

namespace Obullo\Error;

use Controller;
use Obullo\Log\Logger;
use Obullo\Container\Container;

/**
 * Exception Class
 * 
 * @category  Error
 * @package   Exception
 * @author    Obullo Framework <obulloframework@gmail.com>
 * @copyright 2009-2014 Obullo
 * @license   http://opensource.org/licenses/MIT MIT license
 * @link      http://obullo.com/package/error
 */
class Exception
{
    /**
     * Container
     * 
     * @var object
     */
    public $c;

    /**
     * Constructor
     * 
     * @param object $c container
     */
    public function __construct(Container $c)
    {
        $this->c = $c;
    }

    /**
     * Display the exception view
     * 
     * @param object  $e          exception object
     * @param boolean $fatalError whether to fatal error
     * 
     * @return string view
     */
    public function toString($e, $fatalError = false)
    {
        if (strpos($e->getMessage(), 'shmop_') === 0) {  // Hide shmop function errors in debug mode.
            return;
        }
        if (strpos($e->getMessage(), 'socket_connect') === 0) {  // Hide shmop function errors in debug mode.
            return;
        }
        if ($fatalError == false) { 
            unset($fatalError);  // Fatal error variable used in view file
        }
        if (defined('STDIN')) {      // Cli
            echo $this->loadView('ExceptionConsole', $e);
            return;
        }
        if (is_object($this->c)) {
            $isAjax = false;
            if ( ! empty($_SERVER['HTTP_X_REQUESTED_WITH']) AND strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
                $isAjax = true;
            }
            if ($isAjax) {    // Ajax
                echo $this->loadView('ExceptionAjax', $e);
                return;
            }
            $lastQuery = '';             
            if (class_exists('Controller', false)
                AND Controller::$instance != null 
                AND isset(Controller::$instance->db) 
                AND is_object(Controller::$instance->db) 
                AND method_exists(Controller::$instance->db, 'lastQuery')
            ) {  
                $lastQuery = Controller::$instance->db->lastQuery();        // Show the last sql query
            }
        }
        // Html
        echo '<!DOCTYPE html> 
        <html>
            <head>
                <meta http-equiv="Content-Type" content="text/html; charset=UTF-8"/>
                <meta name="robots" content="noindex,nofollow" />
                <style>

                </style>
            </head>
            <body><div>'.$this->loadView('ExceptionHtml', $e).'</div></body></html>';
    }

    /**
     * Load exception view
     * 
     * @param string $file content
     * @param string $e    exception object
     * 
     * @return string
     */
    public function loadView($file, $e)
    {
        ob_start();
        include OBULLO . 'Error' . DS . 'View'. DS .$file . '.php';
        return ob_get_clean();
    }

}

// END Exception class

/* End of file Exception.php */
/* Location: .Obullo/Error/Exception.php */