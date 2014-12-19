<?php

namespace Obullo\Utils;

/**
 * Element helper
 * 
 * @category  Utilities
 * @package   Element
 * @author    Obullo Framework <obulloframework@gmail.com>
 * @copyright 2009-2014 Obullo
 * @license   http://opensource.org/licenses/MIT
 * @link      http://obullo.com/package/utils
 */
Class Element
{
    /**
     * Container
     *
     * @var object
     */
    public $c;

    /**
     * Constructor
     *
     * @param object $c container
     */
    public function __construct($c)
    {
        $this->c = $c;
    }

    /**
     * Check array element is associative
     * 
     * @param array $array array
     * 
     * @return boolean
     */
    public function isAssoc($array)
    {
        return array_keys($array) !== range(0, count($array) - 1);
    }

}

// END Element Class
/* End of file Element.php

/* Location: .Obullo/Utils/Element.php */