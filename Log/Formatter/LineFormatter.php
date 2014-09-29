<?php

namespace Obullo\Log\Formatter;

/**
 * Line Formatter Class
 * 
 * @category  Log
 * @package   Formatter
 * @author    Obullo Framework <obulloframework@gmail.com>
 * @copyright 2009-2014 Obullo
 * @license   http://www.gnu.org/licenses/gpl-3.0.html GPL Licence
 * @link      http://obullo.com/package/log
 */
Class LineFormatter
{
    /**
     * Container
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
     * Format the line which is defined in your app/config/$env/config.php
     * 
     * 'line' => '[%datetime%] %channel%.%level%: --> %message% %context% %extra%\n',
     * 
     * @param array $record record data
     * 
     * @return array
     */
    public function format($record)
    {
        if ( ! is_array($record)) {
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
            ), array(
            $record['datetime'],
            $record['channel'],
            $record['level'],
            $record['message'],
            (empty($record['context'])) ? '' : $record['context'],
            (empty($record['extra'])) ? '' : $record['extra'],
            $record['extra'],
            ),
            str_replace('\n', "\n", $this->c['config']['log']['line'])
        );
    }

}

// END LineFormatter class

/* End of file LineFormatter.php */
/* Location: .Obullo/Log/Formatter/LineFormatter.php */