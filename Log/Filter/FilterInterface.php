<?php

namespace Obullo\Log\Filter;

/**
 * Logger Filter Interface
 * 
 * @category  Log
 * @package   Filter
 * @author    Obullo Framework <obulloframework@gmail.com>
 * @copyright 2009-2014 Obullo
 * @license   http://www.gnu.org/licenses/gpl-3.0.html GPL Licence
 * @link      http://obullo.com/package/log
 */
interface FilterInterface
{
    /**
     * Constructor
     * 
     * @param object $c      container
     * @param array  $params array
     */
    public function __construct($c, array $params = array());

    /**
     * Filter unformatted log records
     * 
     * @param array $record record data
     * 
     * @return array
     */
    public function filter(array $record);
}

// END FilterInterface class

/* End of file FilterInterface.php */
/* Location: .Obullo/Log/Filter/FilterInterface.php */