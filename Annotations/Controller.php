<?php

namespace Obullo\Annotations;

use ReflectionClass;
use Obullo\Container\ContainerInterface;
use Obullo\Http\Response\ResponseInterface;

/**
 * Annotations Reader for Controller
 *
 * Read controller doc blocks and execute filters.
 *
 * @category  DocBlocks
 * @package   Controller
 * @author    Obullo Framework <obulloframework@gmail.com>
 * @copyright 2009-2015 Obullo
 * @license   http://opensource.org/licenses/MIT MIT license
 * @link      http://obullo.com/package/blocks
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
     * Doc blocks string
     * 
     * @var string
     */
    protected $blocks;

    /**
     * Constructor
     *
     * @param object $c        \Obullo\Container\ContainerInterface
     * @param object $response \Obullo\Http\Response\ResponseInterface
     * @param object $class    controller object
     * @param string $method   controller method
     */
    public function __construct(ContainerInterface $c, ResponseInterface $response, $class, $method = 'index')
    {
        $this->c = $c;
        $reflection = new ReflectionClass($class);

        $this->c['annotation.middleware'] = function () use ($c) {
            return new Middleware($c, $c['app'], $c['event']);
        };
        if (! $reflection->hasMethod($method)) {  // Show404 if method doest not exist
            $response->show404();
        }
        $this->blocks = '';
        if ($reflection->hasMethod('load')) {
            $this->blocks = $reflection->getMethod('load')->getDocComment();
        }
        $this->blocks.= $reflection->getMethod($method)->getDocComment();
    }

    /**
     * Parse docs blocks and execute filters
     * 
     * @return void
     */
    public function parse()
    {
        $docs = str_replace('*', '', $this->blocks);
        $docs = explode("@", $docs);

        if (strpos($this->blocks, 'middleware->') > 0 || strpos($this->blocks, 'event->')) {
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