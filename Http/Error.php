<?php

namespace Obullo\Http;

use Obullo\Log\LoggerInterface;
use Obullo\Config\ConfigInterface;
use Obullo\Application\Application;

use Psr\Http\Message\ResponseInterface;

/**
 * Http error handler
 * 
 * @author    Obullo Framework <obulloframework@gmail.com>
 * @copyright 2009-2015 Obullo
 * @license   http://opensource.org/licenses/MIT MIT license
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
     * Config
     * 
     * @var object
     */
    protected $config;

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
     * @param object $app      \Obullo\Application\Application
     * @param object $config   \Obullo\Config\ConfigInterface
     * @param object $logger   \Obullo\Log\LoggerInterface
     * @param object $response \Psr\Http\Message\ResponseInterface
     * 
     * @return void
     */
    public function __construct(Application $app, ConfigInterface $config, LoggerInterface $logger, ResponseInterface $response)
    {
        $this->app = $app;
        $this->logger = $logger;
        $this->config = $config;
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
        if (empty($page)) {
            $exp = explode("/", $this->app->uri->getUriString());
            $segments = array_slice($exp, 0, 4);
            $page = implode("/", $segments);
        }
        $page = $this->sanitizeMessage($page);
        if (strlen($page) > 60) {   // Security fix
            $page = '';
        }
        $this->logger->error('404 Page Not Found --> '.$page);
        echo $this->showHttpError('404 Page Not Found', $page, '404', 404);
        exit;
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

        header('Content-type: text/html; charset='.$this->config['locale']['charset']); // Some times we use utf8 chars in errors.
        echo $this->showHttpError($heading, $message, 'general', $status);
        exit;
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
    * @return string
    */
    protected function showHttpError($heading, $message, $template = 'general', $status = 500)
    {
        $message = implode('<br />', ( ! is_array($message)) ? array($message) : $message);
        $message = filter_var($message, FILTER_SANITIZE_SPECIAL_CHARS);

        if (defined('STDIN')) { // Cli
            return '['.$heading.']: The url '.$message.' you requested was not found.'."\n";
        } else {
            http_response_code($status);
        }
        ob_start();
        include TEMPLATES .'errors'. DS .$template.'.php';
        $buffer = ob_get_clean();
        return $buffer;
    }

}