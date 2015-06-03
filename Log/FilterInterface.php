<?php

namespace Obullo\Log;

use Obullo\Container\ContainerInterface;

/**
 * Logger Filter Interface
 * 
 * @category  Log
 * @package   Filter
 * @author    Obullo Framework <obulloframework@gmail.com>
 * @copyright 2009-2014 Obullo
 * @license   http://opensource.org/licenses/MIT MIT license
 * @link      http://obullo.com/package/log
 */
interface FilterInterface
{
    /**
     * Constructor
     * 
     * @param object $c container
     */
    public function __construct(ContainerInterface $c);

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
/* Location: .Obullo/Log/FilterInterface.php */