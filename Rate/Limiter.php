<?php

namespace Obullo\Rate;

use RuntimeException;

/**
 * Rate Limiter
 * 
 * @category  Security
 * @package   Limiter
 * @author    Obullo Framework <obulloframework@gmail.com>
 * @copyright 2009-2014 Obullo
 * @license   http://opensource.org/licenses/MIT MIT license
 * @link      http://obullo.com/docs
 */
Class Limiter
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
     * Load rate limiter configuration
     * 
     * @param string $identifier name
     * @param array  $params     configuration
     * 
     * @return void
     */
    public function set($identifier = 'ip', $params = array())
    {
        if (count($params) == 0) {
            $params = $this->c['config']->load('rate');  // Load from file
        }
        if (isset($this->{$identifier})) {
            return $this->{$identifier};
        }
        $this->{$identifier} = new Rate($this->c, $identifier, $params);
    }
}


// END Limiter Class

/* End of file Limiter.php */
/* Location: .Obullo/Rate/Limiter.php */