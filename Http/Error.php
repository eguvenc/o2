<?php

namespace Obullo\Http;

use Obullo\Container\ContainerInterface;

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
     * Application
     * 
     * @var object
     */
    protected $app;

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
     * Constructor
     * 
     * @param object $c        Container
     * @param object $response Http\Response
     * 
     * @return void
     */
    public function __construct(ContainerInterface $c, Response $response)
    {
        $this->app = $c['app'];
        $this->logger = $c['logger'];
        $this->response = $response;
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
        if ($this->c->has('app.uri') && empty($page)) {
            $page = $this->app->uri->getUriString();
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
    * @param string $message message
    * @param int    $status  status
    * @param int    $heading heading text
    *
    * @return void
    */
    public function showError($message, $status = 500, $heading = 'An Error Was Encountered')
    {
        $message = $this->sanitizeMessage($message);
        $this->logger->error($heading.' --> '.$message);

        header('Content-type: text/html; charset='.$this->c['config']['locale']['charset']); // Some times we use utf8 chars in errors.
        echo $this->showHttpError($heading, $message, 'general', $status);
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
        return filter_var($message, FILTER_SANITIZE_SPECIAL_CHARS);
    }

    /**
    * General Http Errors
    *
    * @param string $heading  the heading
    * @param string $message  the message
    * @param string $template the template name
    * @param int    $status   header status code
    * 
    * @return   string
    */
    protected function showHttpError($heading, $message, $template = 'general', $status = 500)
    {
        $message = implode('<br />', ( ! is_array($message)) ? array($message) : $message);
        $message = filter_var($message, FILTER_SANITIZE_SPECIAL_CHARS);

        if (defined('STDIN')) { // Cli
            return '['.$heading.']: The url ' .$message. ' you requested was not found.'."\n";
        } else {
            http_response_code($status);
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