<?php

namespace Obullo\Mail\Transport;

/**
 * Transport Api Client Response
 * 
 * @category  Mail
 * @package   Transport
 * @author    Obullo Framework <obulloframework@gmail.com>
 * @copyright 2009-2014 Obullo
 * @license   http://www.gnu.org/licenses/gpl-3.0.html GPL Licence
 * @link      http://obullo.com/package/mail
 */
Class Response
{
    /**
     * Response array
     * 
     * @var array
     */
    protected $response;

    /**
     * Constructor
     *
     * @param array $response array
     */
    public function __construct($response)
    {
        $this->response = $response;
    }

    /**
     * Returns to raw output ( json )
     * 
     * @return string
     */
    public function getRaw()
    {
        return $this->response['body'];
    }

    /**
     * Returns to raw output ( json )
     * 
     * @return string
     */
    public function getXml()
    {
        return $this->response['xml'];
    }

    /**
     * Returns to json decoded output
     * 
     * @return array
     */
    public function getArray()
    {
        return $this->response['array'];
    }

    /**
     * Returns to curl info in array format
     * 
     * @return array
     */
    public function getInfo()
    {
        return $this->response['info'];
    }

}

// END Response class
/* End of file Response.php */

/* Location: .Obullo/Mail/Transport/Response.php */