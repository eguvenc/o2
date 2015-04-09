<?php

namespace Obullo\Http;

use Obullo\Container\Container;

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
class Error
{
    /**
     * Constructor
     * 
     * @param object $c        container
     * @param object $response response
     * 
     * @return void
     */
    public function __construct(Container $c, Response $response)
    {
        $this->c = $c;
        $this->response = $response;
        $this->logger = $c['logger'];
    }

    /**
    * 404 Page Not Found Handler
    *
    * @param string $page page name
    * 
    * @return string
    */
    public function show404($page = '')
    {
        if ($this->c->exists('app.uri') AND empty($page)) {
            $page = $this->c['app']->uri->getUriString();
        }
        $page = $this->sanitizeMessage($page);
        $message = '404 Page Not Found --> '.$page;
        $this->logger->error($message);

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

        header('Content-type: text/html; charset='.$this->c['config']['locale']['charset']); // Some times we use utf8 chars in errors.
        echo $this->showHttpError($heading, $message, 'general', $statusCode);
        exit();
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
        return $message;
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
        http_response_code($statusCode);

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