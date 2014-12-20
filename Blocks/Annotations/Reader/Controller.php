<?php

namespace Obullo\Blocks\Annotations\Reader;

use Obullo\Blocks\Annotations\Filter,
    ReflectionClass;

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
     * @param object $c     container
     * @param string $class called controller object
     */
    public function __construct($c, $class)
    {
        $this->c = $c;

        $rc = new ReflectionClass($class);
        $this->blocks = $rc->getMethod('index')->getDocComment();
    }

    /**
     * Parse docs blocks then execute filters
     * 
     * @return void|filter \Blocks\Annotations\Filter
     */
    public function parse()
    {
        $docs = str_replace('*', '', $this->blocks);
        $docs = explode("@", $docs);

        $filter = false;
        if (strpos($this->blocks, 'filter->') > 0) {  // If we have @filter blocks
            $filter = new Filter($this->c);
            foreach ($docs as $line) {
                $methods = explode('->', $line);  // explode every methods
                array_shift($methods);            // remove class name "filter"
                foreach ($methods as $method) {
                    $this->callMethod($filter, $method);
                }
            }
        }
        return $filter;
    }

    /**
     * Call filter methods
     * 
     * @param object $class  \Blocks\Annotations\Filter
     * @param string $method filter method name ( before, after, method or when )
     * 
     * @return void
     */
    public function callMethod($class, $method)
    {
        $strstr = strstr($method, '(');
        $params = str_replace(array('(',')',';'), '', $strstr);
        $untrimmed = str_replace($strstr, '', $method);
        $method = trim($untrimmed);
        $parray = $params = str_replace(array('"', '[', ']'), '', trim($params));
        
        if (strpos($params, ',') > 0) {  // array support
            $parray = explode(',', $params);
        }
        $class->$method($parray);  // Execute filter methods
    }

}

// END Controller Class

/* End of file Controller.php */
/* Location: .Obullo/Blocks/Annotations/Reader/Controller.php */