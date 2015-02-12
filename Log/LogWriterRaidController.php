<?php

namespace Obullo\Log;

/**
 * Multiple writers raid control.
 *
 * Control log writers log mirroring feature.
 * 
 * @category  Log
 * @package   WriterRaidDefinition
 * @author    Obullo Framework <obulloframework@gmail.com>
 * @copyright 2009-2014 Obullo
 * @license   http://opensource.org/licenses/MIT MIT license
 * @link      http://obullo.com/package/log
 */
Class LogWriterRaidController
{
    /**
     * if log data type is a *writer, mirror record data to other *slave writers  if type == handler
     * we don't need do mirroring.
     * 
     * @param array $data queued log data
     * 
     * @return array data
     */
    public static function handle($data)
    {
        $masterWriter = $data['primary'];  // Master log writer

        foreach ($data as $key => $array) {

            if (is_array($array) AND $array['type'] == 'writer') { // Slave log writers

                // Mirrors
                // If $array['type'] == "writer" use primary handler record to other handlers 
                // otherwise use own record of the handler.
        
                $data[$key]['record'] = $data[$masterWriter]['record'];  // Mirror records to other writers
            }
        }
        unset($data['logger'], $data['primary']);
        return $data;  // writers
    }

}

// END LogWriterRaidHandler
/* End of file LogWriterRaidHandler.php */
