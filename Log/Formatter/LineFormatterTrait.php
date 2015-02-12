<?php

namespace Obullo\Log\Formatter;

trait LineFormatterTrait
{
    /**
     * Format the line comes from app/config/$env/config.php
     * 
     * 'line' => '[%datetime%] %channel%.%level%: --> %message% %context% %extra%\n',
     * 
     * @param array $record record data
     * 
     * @return array
     */
    public function lineFormat($record)
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
            str_replace('\n', "\n", $this->c['config']['logger']['format']['line'])
        );
    }

}

// END LineFormatterTrait class

/* End of file LineFormatterTrait.php */
/* Location: .Obullo/Log/Formatter/LineFormatterTrait.php */