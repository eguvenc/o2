<?php

namespace Obullo\Model;

use Controller;

/**
 * Easy Class
 * 
 * @category  Model
 * @package   Easy
 * @author    Obullo Framework <obulloframework@gmail.com>
 * @copyright 2009-2014 Obullo
 * @license   http://opensource.org/licenses/MIT MIT license
 * @link      http://obullo.com/package/model
 */
class Easy
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

// END Easy class
/* End of file Easy.php */

/* Location: .Obullo/Model/Easy.php */