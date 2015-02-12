<?php

namespace Obullo\Mailer;

/**
 * Mail Api Response
 * 
 * @category  Mail
 * @package   Transport
 * @author    Obullo Framework <obulloframework@gmail.com>
 * @copyright 2009-2014 Obullo
 * @license   http://opensource.org/licenses/MIT MIT license
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
    public function __construct(array $response)
    {
        $this->response = $response;
    }

    /**
     * Returns to raw output
     * 
     * @return string
     */
    public function getRaw()
    {
        return isset($this->response['body']) ? $this->response['body'] : false;
    }

    /**
     * Returns to xml output
     * 
     * @return mixed
     */
    public function getXml()
    {
        return isset($this->response['xml']) ? $this->response['xml'] : false;
    }

    /**
     * Returns to json decoded array output
     * 
     * @return mixed
     */
    public function getArray()
    {
        return isset($this->response['array']) ? $this->response['array'] : false;
    }

    /**
     * Returns to curl info in array format
     * 
     * @return mixed
     */
    public function getInfo()
    {
        return isset($this->response['info']) ? $this->response['info'] : false;
    }

}

// END Response class
/* End of file Response.php */

/* Location: .Obullo/Mailer/Response.php */