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
    protected $c;

    /**
     * Constructor
     */
    public function __construct()
    {
        global $c;
        $this->c = $c;
        if (method_exists($this, 'load')) {
            $this->load();
        }
    }

    /**
     * Controller loader
     * 
     * @param string $key class name
     * 
     * @return void
     */
    public function __get($key)
    {
        if (isset(Controller::$instance->{$key})) {
            return Controller::$instance->{$key};
        }
    }
}

// END Model class
/* End of file Model.php */

/* Location: .Obullo/Database/Model.php */