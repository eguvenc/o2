<?php

namespace Obullo\Log\Filter;

use Obullo\Log\Logger;

/**
 * Priority Filter Class ( Log threshold filter )
 * 
 * @category  Log
 * @package   Filter
 * @author    Obullo Framework <obulloframework@gmail.com>
 * @copyright 2009-2014 Obullo
 * @license   http://www.gnu.org/licenses/gpl-3.0.html GPL Licence
 * @link      http://obullo.com/package/log
 */
Class Priority implements FilterInterface
{
    /**
     * Container
     * 
     * @var object
     */
    public $c;

    /**
     * Filter Params
     * 
     * @var array
     */
    public $params;

    /**
     * Constructor
     * 
     * @param object $c      container
     * @param array  $params array
     */
    public function __construct($c, array $params = array())
    {
        $this->c = $c;
        $this->priorities = $params;
    }

    /**
     * Filter in array
     * 
     * @param array $record unformatted record data
     * 
     * @return array
     */
    public function filter(array $record)
    {
        $priority = Logger::$priorities[$record['level']];
        if (in_array($priority, $this->priorities)) {
            return $record;
        }
        return array();  // To remove the record we return to empty array.
    }

    /**
     * Filter "not" in array
     * 
     * @param array $record unformatted record data
     * 
     * @return array
     */
    public function notIn($record)
    {
        $priority = Logger::$priorities[$record['level']];
        if ( ! in_array($priority, $this->priorities)) {
            return $record;
        }
        return array();  // To remove the record we return to empty array.
    }

}

// END Priority class

/* End of file Priority.php */
/* Location: .Obullo/Log/Filter/Priority.php */