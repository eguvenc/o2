<?php

namespace Obullo\Jelly\Widget;

/**
 * Jelly
 * 
 * @category  Jelly
 * @package   Widget
 * @author    Obullo Framework <obulloframework@gmail.com>
 * @author    Ali Ihsan Caglayan <ihsancaglayan@gmail.com>
 * @copyright 2009-2014 Obullo
 * @license   http://www.gnu.org/licenses/gpl-3.0.html GPL Licence
 * @link      http://obullo.com/docs
 */
Class Image
{
    /**
     * Constructor
     * 
     * @param array $c container
     */
    public function __construct($c)
    {
        $this->c = $c;
    }

    /**
     * Set error message
     * 
     * @param string $message error message
     * 
     * @return void
     */
    public function setError($message)
    {
        $this->error = $message;
    }

    /**
     * Get error message
     * 
     * @return string
     */
    public function getError()
    {
        return $this->error;
    }

    /**
     * Validate
     * 
     * @param string $data validate data
     * 
     * @return boolean
     */
    public function validate($data)
    {
        return true;
    }
}

// END Image Class
/* End of file Image.php */

/* Location: .Obullo/Jelly/Widget/Image.php */