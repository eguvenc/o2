<?php

namespace Obullo\Captcha;

/**
 * Captcha Adapter Interface
 * 
 * @category  Captcha
 * @package   AdapterInterface
 * @author    Obullo Framework <obulloframework@gmail.com>
 * @copyright 2009-2014 Obullo
 * @license   http://opensource.org/licenses/MIT MIT license
 * @link      http://obullo.com/package/captcha
 */
interface AdapterInterface
{
    /**
     * Initialize
     * 
     * @return void
     */
    public function init();

    /**
     * Create captcha and save into captcha
     *
     * @return void
     */
    public function create();

    /**
     * Check captcha code
     * 
     * @param string $code captcha code
     * 
     * @return boolean
     */
    public function check($code);
}


// END AdapterInterface Class
/* End of file AdapterInterface.php

/* Location: .Obullo/Captcha/AdapterInterface.php */