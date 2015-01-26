<?php

namespace Obullo\Filter;

use Obullo\Container\Container;

/**
 * Filter Class
 * 
 * @category  Filters
 * @package   Filter
 * @author    Obullo Framework <obulloframework@gmail.com>
 * @copyright 2009-2014 Obullo
 * @license   http://opensource.org/licenses/MIT MIT license
 * @link      http://obullo.com/package/filter
 */
Class Filter
{
    /**
     * Constructor
     *
     * @param object $c container
     */
    public function __construct(Container $c) 
    {
        $this->c = $c;
    }

    /**
     * Sanitize 
     * 
     * @param  [type] $data   [description]
     * @param  [type] $filter [description]
     * @return [type]         [description]
     */
    public function sanitize($data, $filter)
    {
        // new Email
    }

}

// END Filter.php File
/* End of file Filter.php

/* Location: .Obullo/Filter/Filter.php */