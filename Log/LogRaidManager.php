<?php

namespace Obullo\Log;

/**
 * Multiple writers raid control.
 * 
 * @category  Log
 * @package   LogRaidManager
 * @author    Obullo Framework <obulloframework@gmail.com>
 * @copyright 2009-2014 Obullo
 * @license   http://opensource.org/licenses/MIT MIT license
 * @link      http://obullo.com/package/log
 */
class LogRaidManager
{
    /**
     * If log data type is a * writer, mirror 
     * record data to secondary writers if type == handler
     * we don't need do mirroring.
     * 
     * @param array $data queued log data
     * 
     * @return array data
     */
    public static function handle($data)
    {
        $primaryWriter = $data['primary'];  // Primary log writer

        foreach ($data as $key => $array) {

            if (is_array($array) && $array['type'] == 'writer') { // Secondary log writers

                // Mirrors
                // If $array['type'] == "writer" use primary handler's record to write other handlers 
                // otherwise use own record of the handler.
        
                $data[$key]['record'] = $data[$primaryWriter]['record'];  // Sync records to write other writers
            }
        }
        unset($data['logger'], $data['primary']);
        return $data;  // writers
    }

}

// END LogRaidManager class

/* End of file LogRaidManager.php */
/* Location: .Obullo/Log/LogRaidManager.php */
