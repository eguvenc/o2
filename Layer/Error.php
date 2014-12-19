<?php

namespace Obullo\Layer;

/**
 * Error Class
 * 
 * @category  Layer
 * @package   Error
 * @author    Obullo Framework <obulloframework@gmail.com>
 * @copyright 2009-2014 Obullo
 * @license   http://opensource.org/licenses/MIT
 * @link      http://obullo.com/package/layer
 */
Class Error
{
    const ERROR_HEADER = '<div style="
    white-space: pre-wrap;
    white-space: -moz-pre-wrap;
    white-space: -pre-wrap;
    white-space: -o-pre-wrap;
    word-wrap: break-word; 
    background: #fff;
    border: 1px solid #ddd;
    border-radius: 4px;
    -moz-border-radius: 4px;
    -webkit-border-radius:4px;
    padding:5px 10px;
    color:#069586;
    font-size:12px;">';
    const ERROR_FOOTER = '</div>';

    /**
     * Container class
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
     * Get lvc error
     *
     * @param string $response lvc response
     * 
     * @return mixed
     */
    public function get404Error($response)
    {
        if ($this->c->load('request')->isXmlHttp()) {  // Is ajax request ?
            return array(
                'success' => 0,
                'message' => $this->c->load('translator')['e_404'],
                'errors' => array()
            );
        }
        return (static::ERROR_HEADER . $response . static::ERROR_FOOTER);
    }

    /**
     * Get private request format error
     * 
     * @param string $response lvc response
     * 
     * @return string
     */
    public function getFormatError($response)
    {
        $error = sprintf(
            '%s<span style="font-weight:bold;">Json response must be array and contain at least one of the following keys.</span><pre style="border:none;">
            $r = array(
                \'success\' => integer     // optional
                \'message\' => string,     // optional
                \'errors\'  => array(),    // optional
                \'results\' => array(),    // optional
                \'e\' => $e->getMessage(), // optional
            )

            echo json_encode($r); // required

            <b>Actual Response:</b> %s
            </pre>%s',
            static::ERROR_HEADER,
            (is_array($response) ? print_r($response, true) : $response),
            static::ERROR_FOOTER
        );

        if ($this->c->load('request')->isXmlHttp()) {
            return array(
                'success' => 0,
                'message' => $error,
                'errors' => array()
            );
        }
        return $error;
    }
    
}

// END Error class

/* End of file Error.php */
/* Location: .Obullo/Layer/Error.php */