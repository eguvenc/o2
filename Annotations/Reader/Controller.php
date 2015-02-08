<?php

namespace Obullo\Annotations\Reader;

use ReflectionClass;
use Obullo\Application\Filter;
use Obullo\Container\Container;

/**
 * Annotations Reader for Controller
 *
 * Read controller doc blocks and execute filters.
 *
 * @category  DocBlocks
 * @package   Controller
 * @author    Obullo Framework <obulloframework@gmail.com>
 * @copyright 2009-2014 Obullo
 * @license   http://opensource.org/licenses/MIT MIT license
 * @link      http://obullo.com/package/blocks
 */
Class Controller
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
     * @param object $c      container
     * @param string $class  controller object
     * @param string $method controller method
     */
    public function __construct(Container $c, $class, $method = 'index')
    {
        $this->c = $c;
        $reflection = new ReflectionClass($class);

        if ( ! $reflection->hasMethod($method)) {  // Show404 if method not exists
            $this->c['response']->show404();
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
     * @return void|filter \Blocks\Annotations\Filter
     */
    public function parse()
    {
        $docs = str_replace('*', '', $this->blocks);
        $docs = explode("@", $docs);

        $filter = false;
        if (strpos($this->blocks, 'filter->') > 0) {  // If we have @filter blocks

            foreach ($docs as $line) {
                $methods = explode('->', $line);  // explode every methods
                array_shift($methods);            // remove class name "filter"
                foreach ($methods as $method) {
                    $this->callMethod($method);
                }
            }
        }
        return $filter;
    }

    /**
     * Call filter methods
     * 
     * @param string $method filter method name ( before, after, method or when )
     * 
     * @return void
     */
    public function callMethod($method)
    {
        $strstr = strstr($method, '(');
        $params = str_replace(array('(',')',';'), '', $strstr);
        $untrimmed = str_replace($strstr, '', $method);
        $method = trim($untrimmed);
        $parray = $params = str_replace(array('"', '[', ']'), '', trim($params));
        
        if (strpos($params, ',') > 0) {  // array support
            $parray = explode(',', $params);
        }
        $this->c['annotation.filter']->$method($parray);  // Execute filter methods
    }

}

// END Controller Class

/* End of file Controller.php */
/* Location: .Obullo/Annotations/Reader/Controller.php */