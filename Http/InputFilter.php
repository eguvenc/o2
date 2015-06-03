<?php

namespace Obullo\Http;

use Obullo\Container\ContainerInterface;

/**
 * Input Filter
 * 
 * @category  Http
 * @package   Client
 * @author    Obullo Framework <obulloframework@gmail.com>
 * @copyright 2009-2014 Obullo
 * @license   http://opensource.org/licenses/MIT MIT license
 * @link      http://obullo.com/package/http
 */
class InputFilter
{
    /**
     * Current input value
     * 
     * @var mixed
     */
    protected $value;

    /**
     * Current filter name
     * 
     * @var string
     */
    protected $filter;

    /**
     * Container
     * 
     * @param Contaner $c object
     */
    public function __construct(ContainerInterface $c)
    {
        $this->c = $c;
    }

    /**
     * Set filter name
     * 
     * @param string $name filter
     *
     * @return object this
     */
    public function setFilter($name)
    {
        $this->filter = $name;
        return $this;
    }

    /**
     * Get filter object
     * 
     * @return object
     */
    public function getFilter()
    {
        return $this->c[$this->filter];
    }

    /**
     * Set latest request input value
     * 
     * @param mixed $value value
     *
     * @return void
     */
    public function setValue($value)
    {
        $this->value = $value;
        return $this;
    }

    /**
     * Get input value
     * 
     * @return mixed
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * Call filter class & execute filter methods
     * 
     * @param string $method    name
     * @param array  $arguments argument array
     * 
     * @return mixed
     */
    public function __call($method, $arguments)
    {
        if (count($arguments) > 0) {
            array_unshift($arguments, $this->getValue());
        } else {
            $arguments = array($this->getValue());
        }
        return call_user_func_array(array($this->getFilter(), $method), $arguments);
    }
}

// END InputFilter.php File
/* End of file InputFilter.php

/* Location: .Obullo/Http/InputFilter.php */