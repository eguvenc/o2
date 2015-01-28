<?php

namespace Obullo\Annotations;

use Obullo\Container\Container;

/**
 * Filter Class
 * 
 * @category  Annotations
 * @package   Filter
 * @author    Obullo Framework <obulloframework@gmail.com>
 * @copyright 2009-2014 Obullo
 * @license   http://opensource.org/licenses/MIT MIT license
 * @link      http://obullo.com/package/annotations
 */
Class Filter
{
    /**
     * Container
     * 
     * @var object
     */
    protected $c;

    /**
     * Before filters data
     * 
     * @var array
     */
    protected $before = array();

    /**
     * After filters data
     * 
     * @var array
     */
    protected $after = array();

    /**
     * On Controller load() filters data
     * 
     * @var array
     */
    protected $load = array();

    /**
     * Track of filter names
     * 
     * @var array
     */
    protected $track = array();

    /**
     * Key counter
     * 
     * @var integer
     */
    protected $count;

    /**
     * Http method name
     * 
     * @var string
     */
    protected $httpMethod = 'get';

    /**
     * Constructor
     * 
     * @param object $c container
     */
    public function __construct(Container $c)
    {
        $this->c = $c;
        $this->count = 0;
        $method = isset($_SERVER['REQUEST_METHOD']) ? $_SERVER['REQUEST_METHOD'] : 'get';
        $this->httpMethod = strtolower($method);
    }

    /**
     * Initialize to before filters
     * 
     * @param string $filter name
     * 
     * @return object
     */
    public function before($filter = '')
    {
        $this->before[$this->count] = array('name' => $filter);
        $this->track[] = 'before';
        ++$this->count;
        return $this;
    }

    /**
     * Initialize to after filters
     * 
     * @param string $filter name
     * 
     * @return object
     */
    public function after($filter = '')
    {
        $this->after[$this->count] = array('name' => $filter);
        $this->track[] = 'after';
        ++$this->count;
        return $this;
    }

    /**
     * Initialize to on load filters
     * 
     * @param string $filter name
     * 
     * @return object
     */
    public function load($filter = '')
    {
        $this->load[$this->count] = array('name' => $filter);
        $this->track[] = 'load';
        ++$this->count;
        return $this;
    }

    /**
     * Initialize to after filters
     * 
     * @param string|array $params http method(s): ( post, get, put, delete )
     * 
     * @return object
     */
    public function when($params = '')
    {
        if (is_string($params)) {
            $params = array($params);
        }
        $count = $this->count - 1;
        $last = end($this->track);
        $this->{$last}[$count]['when'] = $params;  // push when parameters
        return $this;
    }

    /**
     * Initialize to allowed methods filters
     * 
     * @param string|array $params parameters
     * 
     * @return void
     */
    public function method($params = null)
    {
        if (is_string($params)) {
            $params = array($params);
        }
        $this->c['event']->fire('on.method', array((object)$params, $this->httpMethod));
        return;
    }

    /**
     * Render filter data
     *
     * @param string $direction before or after
     * 
     * @return void
     */
    public function initFilters($direction = 'before')
    {   
        if (count($this->{$direction}) == 0) {
            return;
        }
        foreach ($this->{$direction} as $val) {
            if (isset($val['when']) AND in_array($this->httpMethod, $val['when'])) {  // stop filter
                $this->c['router']->runFilter($val['name'], $direction);
            }
            if ( ! isset($val['when'])) {
                $this->c['router']->runFilter($val['name'], $direction);
            }
        }
    }
    
}

// END Filter.php File
/* End of file Filter.php

/* Location: .Obullo/Annotations/Filter.php */