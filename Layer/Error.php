<?php

namespace Obullo\Layer;

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
class Error
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
     * Format layer errors
     *
     * @param string $response lvc response
     * 
     * @return mixed
     */
    public function getError($response)
    {
        return (static::ERROR_HEADER . $response . static::ERROR_FOOTER);
    }
    
}