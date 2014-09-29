<?php

namespace Obullo\Session;

/**
 * Session Raw Data Helper
 * 
 * This just an helper if we need to hack php session
 * encoded raw data.
 * 
 * @category  Session
 * @package   Decoder
 * @author    Obullo Framework <obulloframework@gmail.com>
 * @copyright 2009-2014 Obullo
 * @license   http://www.gnu.org/licenses/gpl-3.0.html GPL Licence
 * @link      http://obullo.com/package/session
 */
Class Decoder
{
    /**
     * Decodes just session meta data
     * 
     * @param string $rawSession data
     * 
     * @return array
     */
    public function decodeMeta($rawSession = '')
    {
        if ($output = explode('_o2_meta|s:', $rawSession)) {
            $json = substr(substr($output[1], 5), 0, -2);
            $data = json_decode($json, true);
            return $data;
        }
        return array();
    }

    /**
     * Encode array data to metadata format
     * 
     * @param array $data array
     * 
     * @return string json encoded raw
     */
    public function encodeMeta(array $data)
    {
        return json_encode($data);
    }

    /**
     * Writes meta data to $_SESSION
     * 
     * @param array $data array
     * 
     * @return mixed value of sessio
     */
    public function saveMeta(array $data)
    {
        return $_SESSION['_o2_meta'] = $this->encodeMeta($data);
    }

    /**
     * Decodes raw session data 
     * like php session_decode(); function
     *
     * Original Regex : (\w+)\|s:.*?:"(.*?)";|(\w+)\|i:(\d+)(?:[;])
     * Example String : session_id|s:26:"l7tejneomkl6cig5r3cgfakou3";ip_address|s:9:"127.0.0.1";user_agent|s:50:"Mozilla/5.0 (X11; Ubuntu; Linux x86_64; rv:26.0)
     * G";last_activity|i:1402400684;hello|s:4:"Test";
     * 
     * @param string $rawSession php session raw encoded data
     * 
     * @return array decoded array data
     */
    public function decode($rawSession)
    {
        preg_match_all('#(?<string>\w+)(?:\|s:).*?:"(?<string_val>.*?)(?:";)|(?<integer>\w+)(?:\|i:)(?<integer_val>.*?)(?:;)#', $rawSession, $matches);
        $sessionArray = array();
        if (isset($matches['string'])) {
            foreach ($matches['string'] as $key => $val) {
                if ( ! empty($matches['string_val'][$key])) {
                    $sessionArray[$val] = (string)$matches['string_val'][$key];
                }
                if (isset($matches['integer'][$key]) AND ! empty($matches['integer'][$key])) {
                    $sessionArray[$matches['integer'][$key]] = (int)$matches['integer_val'][$key];
                }
            }  
        }
        return $sessionArray;
    }

    /**
     * Encodes session array to php session raw format
     * like php session_encode(); function.
     * 
     * @param array $sessionArray data
     *  
     * @return string session raw
     */
    public function encode($sessionArray)
    {
        $sessionRaw = '';
        foreach ($sessionArray as $key => $value) {
            if (is_string($value)) {
                $sessionRaw.= $key.'|s:'.mb_strlen($value).':"'.$value.'";';
            }
            if (is_int($value)) {
                $sessionRaw.= $key.'|i:'.$value.';';
            }   
        }
        return $sessionRaw;
    }
}

// END Decoder.php File
/* End of file Decoder.php

/* Location: .Obullo/Session/Decoder.php */