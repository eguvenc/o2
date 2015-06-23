<?php

namespace Obullo\Database;

use Controller;

/**
 * Model Class ( Default Model )
 * 
 * @category  Model
 * @package   Model
 * @author    Obullo Framework <obulloframework@gmail.com>
 * @copyright 2009-2014 Obullo
 * @license   http://opensource.org/licenses/MIT MIT license
 * @link      http://obullo.com/package/model
 */
class Model
{
    /**
     * Container
     * 
     * @var object
     */
    private $__container;
    
    /**
     * Controller loader
     * 
     * @param string $key class name
     * 
     * @return void
     */
    public function __get($key)
    {
        if ($this->__container == null) {
            global $c;
            $this->__container = &$c;
        }
        if ($key == 'c') {
            return $this->__container;
        }
        return $this->__container[$key];
    }
}

// END Model class
/* End of file Model.php */

/* Location: .Obullo/Database/Model.php */