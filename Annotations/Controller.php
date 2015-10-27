<?php

namespace Obullo\Annotations;

use ReflectionClass;
use Obullo\Container\ContainerInterface as Container;

/**
 * Annotations Reader for Controller ( Executes filters & events )
 * 
 * @author    Obullo Framework <obulloframework@gmail.com>
 * @copyright 2009-2015 Obullo
 * @license   http://opensource.org/licenses/MIT MIT license
 */
class Controller
{
    /**
     * Container
     * 
     * @var object
     */
    protected $c;

    /**
     * Doc method
     * 
     * @var string
     */
    protected $method = 'index';

    /**
     * ReflectionClass instance
     * 
     * @var object
     */
    protected $reflector;

    /**
     * Constructor
     * 
     * @param ContainerInterface $c         container
     * @param ReflectionClass    $reflector reflector
     */
    public function __construct(Container $c, ReflectionClass $reflector)
    {
        $this->c = $c;
        $this->reflector = $reflector;
        $this->c['annotation.middleware'] = function () use ($c) {
            return new Middleware($c['event'], $c['request'], $c['dependency'], $c['middleware']);
        };
    }

    /**
     * Set controller method
     * 
     * @param string $method name
     *
     * @return void
     */
    public function setMethod($method)
    {
        $this->method = $method;
    }

    /**
     * Get method
     * 
     * @return string
     */
    public function getMethod()
    {
        return $this->method;
    }

    /**
     * Parse docs blocks and execute filters
     * 
     * @return void
     */
    public function parse()
    {
        $blocks = '';
        if ($reflector->hasMethod('__construct')) {
            $blocks = $reflector->getMethod('__construct')->getDocComment();
        }
        $blocks.= $reflector->getMethod($this->getMethod())->getDocComment();

        $docs = str_replace('*', '', $blocks);
        $docs = explode("@", $docs);

        if (strpos($this->blocks, 'middleware->') > 0 || strpos($blocks, 'event->')) {
            foreach ($docs as $line) {
                $methods = explode('->', $line);  // explode every methods
                array_shift($methods);            // remove class name "filter"
                foreach ($methods as $methodString) {
                    $this->callMethod($methodString);
                }
            }
        }
    }

    /**
     * Call filter methods
     * 
     * @param string $methodString middleware method name ( when, assign, method )
     * 
     * @return void
     */
    public function callMethod($methodString)
    {
        $strstr = strstr($methodString, '(');
        $params = str_replace(array('(',')',';'), '', $strstr);
        $untrimmed = str_replace($strstr, '', $methodString);
        $method = trim($untrimmed);
        $parray = $params = str_replace(array(' ', '"', "'", '[', ']'), '', trim($params));
        
        if (strpos($params, ',') > 0) {  // array support
            $parray = explode(',', $params);
        }
        $this->c['annotation.middleware']->$method($parray);  // Execute middleware methods
    }

}