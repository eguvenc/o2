<?php

namespace Obullo\Captcha;

/**
 * O2 Captcha - Captcha Service
 *
 * @category  Captcha
 * @package   CaptchaService
 * @author    Obullo Framework <obulloframework@gmail.com>
 * @copyright 2009-2014 Obullo
 * @license   http://opensource.org/licenses/MIT MIT license
 * @link      http://obullo.com/package/captcha
 */
Class CaptchaService
{
    /**
     * Container
     * 
     * @var object
     */
    protected $c;

    /**
     * Service configuration parameters
     * 
     * @var array
     */
    protected $config = array();

    /**
     * Constructor
     * 
     * @param object $c      container
     * @param object $params parameters
     */
    public function __construct($c, array $params = array())
    {
        $this->c = $c;
        $this->config = $params;
    }

    /**
     * Service class loader
     * 
     * @param string $class name
     * 
     * @return object | null
     */
    public function __get($class)
    {
        $key = strtolower($class); // Services: $this->captcha->image

        if (isset($this->{$key})) {  // Lazy loading ( returns to old instance if class already exists ).
            return $this->{$key};
        }
        $Class = '\Obullo\Captcha\Adapter\\'.ucfirst($key);
        return $this->{$key} = new $Class($this->c, $this);
    }

}

// END CaptchaService.php File
/* End of file CaptchaService.php

/* Location: .Obullo/Captcha/CaptchaService.php */