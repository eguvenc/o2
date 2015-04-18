<?php

namespace Obullo\Layer;

use Obullo\Layer\Error;
use Obullo\Container\Container;

/**
 * Json Layer Class
 * 
 * @category  Layer
 * @package   Json
 * @author    Obullo Framework <obulloframework@gmail.com>
 * @copyright 2009-2014 Obullo
 * @license   http://opensource.org/licenses/MIT MIT license
 * @link      http://obullo.com/package/layers
 */
class Json
{
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
    public function __construct(Container $c)
    {
        $this->c = $c;
    }

    /**
     * Decode json data and check response format
     * 
     * @param string $raw data
     * 
     * @return string
     */
    public function decode($raw)
    {
        $r = json_decode($raw, true);  // Decode json to array
        if ($this->validateFormat($r) == false) {         // Check the Private Response Format 
            $error = new Error($this->c);
            return $error->getFormatError($r);
        }
        if (isset($r['success'])      // Show exceptional message to developers if environment not LIVE.
            AND $r['success'] == false 
            AND isset($r['e']) AND ! empty($r['e'])
            AND $this->c['app']->env() != 'production'   // Don't send exceptional errors in "production" environment.
        ) { 
            $r['message'] = $r['e'];  // Replace the message with exception
        }
        return $this->formatResponse($r);
    }

    /**
     * Validate "Json Layer" response format
     * 
     * @param array $r response
     * 
     * @return boolean
     */
    public function validateFormat($r)
    {
        if ( ! is_array($r)) {   // Check format is array ?
            return false;
        }
        $keyErrors = array_map(
            function ($val) {    // Check key standart of "Json Layers".
                return in_array(
                    $val,
                    array(
                    'success',
                    'message',
                    'errors',
                    'results',
                    'e'
                    )
                );
            }, array_keys($r)
        );
        if (in_array(false, $keyErrors, true)) {   // Throws an exception
            return false;
        }
        return true;
    }

    /**
     * Reformat result array
     * 
     * @param array $r result array
     * 
     * @return array
     */
    public function formatResponse($r)
    {
        if (isset($r['results']) AND is_array($r['results'])) {  // Automatically add count into results array.
            $r['count'] = count($r['results']);  // Add automatically count() function if we have an array.
        }
        return $r;
    }
}

// END Json class

/* End of file Json.php */
/* Location: .Obullo/Layer/Json.php */