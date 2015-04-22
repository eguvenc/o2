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
     * Print captcha element js
     * 
     * @return string
     */
    public function printJs();

    /**
     * Print captcha html element
     * 
     * @return string
     */
    public function printHtml();

    /**
     * Get captcha results
     * 
     * @param string $code captcha code
     * 
     * @return boolean
     */
    public function result($code);
}


// END AdapterInterface Class
/* End of file AdapterInterface.php

/* Location: .Obullo/Captcha/AdapterInterface.php */