<?php

namespace Obullo\Log\Formatter;

/**
 * Line Formatter Helper
 * 
 * @category  Log
 * @package   Handler
 * @author    Obullo Framework <obulloframework@gmail.com>
 * @copyright 2009-2014 Obullo
 * @license   http://opensource.org/licenses/MIT MIT license
 * @link      http://obullo.com/package/log
 */
class LineFormatter
{
    /**
     * Format the line defined in app/config/env.$env/config.php
     *
     * [%datetime%] %channel%.%level%: --> %message% %context% %extra%\n
     * 
     * @param array  $record record data
     * @param object $config object
     * 
     * @return array
     */
    public static function format(array $record, $config)
    {
        if (! is_array($record)) {
            return;
        }
        return str_replace(
            array(
            '%datetime%',
            '%channel%',
            '%level%',
            '%message%',
            '%context%',
            '%extra%',
            ), 
            array(
            $record['datetime'],
            $record['channel'],
            $record['level'],
            $record['message'],
            (empty($record['context'])) ? '' : $record['context'],
            (empty($record['extra'])) ? '' : $record['extra'],
            $record['extra'],
            ),
            str_replace('\n', "\n", $config['format']['line'])
        );
    }

}

// END LineFormatter class

/* End of file LineFormatter.php */
/* Location: .Obullo/Log/Formatter/LineFormatter.php */