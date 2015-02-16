<?php

namespace Obullo\Http;

/**
 * Show http errors
 * 
 * @category  Http
 * @package   Error
 * @author    Obullo Framework <obulloframework@gmail.com>
 * @copyright 2009-2014 Obullo
 * @license   http://opensource.org/licenses/MIT MIT license
 * @link      http://obullo.com/package/http
 */
Class Error
{
    /**
     * Constructor
     * 
     * @param object $c        container
     * @param object $response response
     * 
     * @return void
     */
    public function __construct($c, $response)
    {
        $this->c = $c;
        $this->response = $response;
        $this->logger = $c['logger'];
    }

    /**
    * 404 Page Not Found Handler
    *
    * @param string  $page    page name
    * @param boolean $http404 http 404 or layer 404
    * 
    * @return string
    */
    public function show404($page = '', $http404 = true)
    {
        if ($this->c->exists('uri') AND empty($page)) {
            $page = $this->c['request.uri']->getUriString();
        }
        $page = $this->sanitizeMessage($page);
        $message = '404 Page Not Found --> '.$page;
        $this->logger->error($message);
        if ($http404 == false) {
            $this->error = $message;
            return $message;
        }
        echo $this->showHttpError('404 Page Not Found', $page, '404', 404);
        exit();
    }

    /**
    * Manually Set General Http Errors
    *
    * @param string $message    message
    * @param int    $statusCode status
    * @param int    $heading    heading text
    *
    * @return void
    */
    public function showError($message, $statusCode = 500, $heading = 'An Error Was Encountered')
    {
        $message = $this->sanitizeMessage($message);
        $this->logger->error($heading.' --> '.$message, false);
        if ($statusCode === false) {
            $this->error = $message;
            return $message;
        }
        header('Content-type: text/html; charset='.$this->c['config']['locale']['charset']); // Some times we use utf8 chars in errors.
        echo $this->showHttpError($heading, $message, 'general', $statusCode);
        exit();
    }

    /**
     * Show user friendly notice messages
     * 
     * @param string $message message
     * 
     * @return string error
     */
    public function showWarning($message)
    {
        $message = $this->sanitizeMessage($message);
        ob_start();
        include TEMPLATES .'errors'. DS .'warning.php';
        $buffer = ob_get_clean();
        return $buffer;
    }

    /**
     * Show user friendly notice messages
     * 
     * @param string $message message
     * 
     * @return string error
     */
    public function showNotice($message)
    {
        $message = $this->sanitizeMessage($message);
        ob_start();
        include TEMPLATES .'errors'. DS .'notice.php';
        $buffer = ob_get_clean();
        return $buffer;
    }

    /**
     * Sanitize mesage
     * 
     * @param string $message message
     * 
     * @return string message
     */
    protected function sanitizeMessage($message)
    {
        return filter_var($message, FILTER_SANITIZE_SPECIAL_CHARS);
    }

    /**
    * General Http Errors
    *
    * @param string $heading    the heading
    * @param string $message    the message
    * @param string $template   the template name
    * @param int    $statusCode header status code
    * 
    * @return   string
    */
    protected function showHttpError($heading, $message, $template = 'general', $statusCode = 500)
    {
        $this->response->status($statusCode);
        
        $message = implode('<br />', ( ! is_array($message)) ? array($message) : $message);
        $message = filter_var($message, FILTER_SANITIZE_SPECIAL_CHARS);

        if (defined('STDIN')) { // Cli
            return '['.$heading.']: The url ' .$message. ' you requested was not found.'."\n";
        }
        ob_start();
        include TEMPLATES .'errors'. DS .$template.'.php';
        $buffer = ob_get_clean();
        return $buffer;
    }

}

// END Error.php File
/* End of file Error.php

/* Location: .Obullo/Http/Error.php */