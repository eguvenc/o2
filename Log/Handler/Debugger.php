<?php

namespace Obullo\Log\Handler;

use Obullo\Container\ContainerInterface;
use Obullo\Log\Formatter\DebuggerFormatter;

/**
 * Debugger Handler Class
 * 
 * @category  Log
 * @package   Handler
 * @author    Obullo Framework <obulloframework@gmail.com>
 * @copyright 2009-2014 Obullo
 * @license   http://opensource.org/licenses/MIT MIT license
 * @link      http://obullo.com/package/log
 */
class Debugger extends AbstractHandler implements HandlerInterface
{
    /**
     * Write output
     *
     * @param string $data single record data
     * 
     * @return mixed
     */
    public function write(array $data)
    {
        $lines = '';
        foreach ($data['record'] as $record) {
            $record = $this->arrayFormat($data, $record);
            $lines.= DebuggerFormatter::format($record, $this->config);
        }
        return $lines;
    }

    /**
     * Close handler connection
     * 
     * @return void
     */
    public function close()
    {
        return;
    }
}

// END Debugger class

/* End of file Debugger.php */
/* Location: .Obullo/Log/Handler/Debugger.php */