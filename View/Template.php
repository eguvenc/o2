<?php

namespace Obullo\View;

use Closure;
use Controller;
use Obullo\Log\LoggerInterface as Logger;
use Obullo\Container\ContainerInterface as Container;
use Obullo\View\ViewInterface as View;

use Obullo\Http\Stream;

/**
 * Temlate Class
 * 
 * @author    Obullo Framework <obulloframework@gmail.com>
 * @copyright 2009-2015 Obullo
 * @license   http://opensource.org/licenses/MIT MIT license
 */
class Template implements TemplateInterface
{
    /**
     * Container
     * 
     * @var object
     */
    protected $c;

    /**
     * View
     * 
     * @var object
     */
    protected $view;

    /**
     * Logger
     * 
     * @var object
     */
    protected $logger;

    /**
     * Constructor
     * 
     * @param object $c      \Obullo\Container\ContainerInterface
     * @param object $view   \Obullo\View\View
     * @param object $logger \Obullo\Log\LoggerInterface
     */
    public function __construct(Container $c, View $view, Logger $logger)
    {
        $this->c = $c;
        $this->view = $view;
        $this->logger = $logger;
        $this->logger->debug('Template Class Initialized');
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
        $this->view->assign($key, $val);        
    }

    /**
     * Include template file from /resources/templates folder
     * 
     * @param string $filename name
     * @param array  $data     data
     * 
     * @return string
     */
    public function load($filename, $data = null)
    {
        return $this->view->getBody(TEMPLATES, $filename, $data, true);
    }

    /**
     * Get template files as string
     * 
     * @param string $filename filename
     * @param mixed  $data     array data
     * 
     * @return object Stream
     */
    public function get($filename, $data = null)
    {
        return $this->view->getBody(TEMPLATES, $filename, $data, false);
    }

    /**
     * Make template files as Stream body
     * 
     * @param string $filename filename
     * @param mixed  $data     array data
     * 
     * @return object Stream
     */
    public function make($filename, $data = null)
    {
        $output = $this->view->getBody(TEMPLATES, $filename, $data, false);
        
        $body = new Stream(fopen('php://temp', 'r+'));
        $body->write($output);
        return $body;
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