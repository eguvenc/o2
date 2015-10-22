<?php

namespace Obullo\Error;

/**
 * Exception Class
 * 
 * @category  Error
 * @package   Exception
 * @author    Obullo Framework <obulloframework@gmail.com>
 * @copyright 2009-2015 Obullo
 * @license   http://opensource.org/licenses/MIT MIT license
 * @link      http://obullo.com/package/error
 */
class Exception implements ExceptionInterface
{
    /**
     * Display the exception view
     * 
     * @param object  $e          exception object
     * @param boolean $fatalError whether to fatal error
     * 
     * @return string view
     */
    public function show(\Exception $e, $fatalError = false)
    {
        if ($e->getCode() == 2 && substr($e->getFile(), 22) == 'Application/Http.php') { // Disable include 404 include error
            return;
        }
        if (strpos($e->getMessage(), 'shmop_') === 0) {  // Disable shmop function errors.
            return;
        }
        if (strpos($e->getMessage(), 'socket_connect') === 0) {  // Disable socket errors.
            return;
        }
        if ($fatalError == false) { 
            unset($fatalError);  // Fatal error variable used in view file
        }
        if (defined('STDIN')) {  // Cli
            echo $this->getErrorView('ExceptionConsole', $e);
            return;
        }
        $isAjax = false;
        if (! empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
            $isAjax = true;
        }
        if ($isAjax) {
            echo $this->getErrorView('ExceptionAjax', $e);
            return;
        }
        echo '<!DOCTYPE html> 
        <html>
            <head>
                <meta http-equiv="Content-Type" content="text/html; charset=UTF-8"/>
                <meta name="robots" content="noindex,nofollow" />
                <style>

                </style>
            </head>
            <body><div>'.$this->getErrorView('ExceptionHtml', $e).'</div></body></html>';
    }

    /**
     * Load exception view
     * 
     * @param string $file content
     * @param string $e    exception object
     * 
     * @return string
     */
    protected function getErrorView($file, $e)
    {   
        ob_start();
        include OBULLO . 'Error/View/' .$file . '.php';
        return ob_get_clean();
    }

}