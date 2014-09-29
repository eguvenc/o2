<?php

namespace Obullo\Jelly\Widget;

use DateTime;

/**
 * Birthdate
 * 
 * @category  Jelly
 * @package   Widget
 * @author    Obullo Framework <obulloframework@gmail.com>
 * @author    Ali Ihsan Caglayan <ihsancaglayan@gmail.com>
 * @copyright 2009-2014 Obullo
 * @license   http://www.gnu.org/licenses/gpl-3.0.html GPL Licence
 * @link      http://obullo.com/docs
 */
Class Birthdate
{
    /**
     * Date format
     * 
     * @var string
     */
    public $format = 'Y-m-d';

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
     * @param string $data validate value
     * 
     * @return boolean
     */
    public function validate($data)
    {
        $d = DateTime::createFromFormat($this->format, $data);
        if ($d->format($this->format) == $data) {
            return true;
        }
        $this->setError($this->c->load('translator')['Wrong birth date']);
        return false;
    }
}

// END Birthdate Class
/* End of file Birthdate.php */

/* Location: .Obullo/Jelly/Widget/Birthdate.php */