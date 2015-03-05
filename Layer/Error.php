<?php

namespace Obullo\Layer;

use Obullo\Container\Container;

/**
 * Error Class
 * 
 * @category  Layer
 * @package   Error
 * @author    Obullo Framework <obulloframework@gmail.com>
 * @copyright 2009-2014 Obullo
 * @license   http://opensource.org/licenses/MIT MIT license
 * @link      http://obullo.com/package/layer
 */
Class Error
{
    const ERROR_HEADER = '<div style="
    white-space: pre-wrap;
    white-space: -moz-pre-wrap;
    white-space: -pre-wrap;
    white-space: -o-pre-wrap;
    font-size:12px;
    font-family:Arial,Verdana,sans-serif;
    font-weight:normal;
    word-wrap: break-word; 
    background: #FFFAED;
    border: 1px solid #ddd;
    border-radius: 4px;
    -moz-border-radius: 4px;
    -webkit-border-radius:4px;
    padding:5px 10px;
    color:#E53528;
    font-size:12px;">';
    const ERROR_FOOTER = '</div>';

    /**
     * Container class
     * 
     * @var object
     */
    protected $c;

    /**
     * Constructor
     *
     * @param object $c container
     */
    public function __construct(Container $c)
    {
        $this->c = $c;
    }

    /**
     * Format layer errors
     *
     * @param string $response lvc response
     * 
     * @return mixed
     */
    public function getError($response)
    {
        if ($this->c['request']->isAjax()) {  // Is ajax request ?
            return array(
                'success' => 0,
                'message' => $response,
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
            '%s<span style="font-weight:bold;">Database layer response array must be contain at least one of the following keys.</span><pre style="border:none;">
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

        if ($this->c['request']->isAjax()) {
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