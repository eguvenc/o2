<?php

namespace Obullo\Log\Formatter;

/**
 * Debugger Formatter Helper for Debugger Module
 * 
 * @category  Log
 * @package   Handler
 * @author    Obullo Framework <obulloframework@gmail.com>
 * @copyright 2009-2015 Obullo
 * @license   http://opensource.org/licenses/MIT MIT license
 * @link      http://obullo.com/package/log
 */
class DebugFormatter
{
    /**
     * Format the line defined in config/env.$env/config.php
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
        $format = str_replace('\n', "", $config['format']['line']);
        $format = preg_replace('#([^\s\w:\->%.]+)#', '', $format);

        $search = [
            '%datetime%',
            '%channel%',
            '%level%',
            '%message%',
            '%context%',
            '%extra%',
        ];
        $replace = [
            '<div class="p"><span class="date">'.$record['datetime'].'</span>',
            $record['channel'],
            $record['level'],
            $record['message'],
            (empty($record['context'])) ? '' : $record['context'],
            (empty($record['extra'])) ? '' : $record['extra'],
        ];
        $line = str_replace($search, $replace, $format)."</div>\n";

        $levelPatterns = array(
            '#<div class="p">(.*(Uri Class Initialized\b).*)<\/div>#',
            '#<div class="p">(.*(.*\.error\b).*)<\/div>#',
            '#<div class="p">(.*(.*\.warning\b).*)<\/div>#',
            '#<div class="p">(.*(.*\.notice\b).*)<\/div>#',
            '#<div class="p">(.*(.*\.emergency\b).*)<\/div>#',
            '#<div class="p">(.*(.*\.critical\b).*)<\/div>#',
            '#<div class="p">(.*(.*\.alert\b).*)<\/div>#',
            '#<div class="p">(.*(.*\.info\b).*)<\/div>#',
        );
        $levelReplace = array(
            '<div class="p title">$1</div>',
            '<div class="p error">$1</div>',
            '<div class="p error">$1</div>',
            '<div class="p error">$1</div>',
            '<div class="p error">$1</div>',
            '<div class="p error">$1</div>',
            '<div class="p alert">$1</div>',
            '<div class="p info">$1</div>',
        );
        return preg_replace($levelPatterns, $levelReplace, $line);
    }

}