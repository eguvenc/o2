<?php

namespace Obullo\Log\Handler;

use Obullo\Config\ConfigInterface;
use Obullo\Application\Application;
use Obullo\Log\Formatter\DebugFormatter;

/**
 * Http Debugger Handler
 * 
 * @author    Obullo Framework <obulloframework@gmail.com>
 * @copyright 2009-2015 Obullo
 * @license   http://opensource.org/licenses/MIT MIT license
 */
class Debugger extends AbstractHandler implements HandlerInterface
{
    /**
     * Constructor
     * 
     * @param object $app    \Obullo\Application\Application
     * @param object $config \Obullo\Config\ConfigInterface
     */
    public function __construct(Application $app, ConfigInterface $config)
    {
        parent::__construct($app, $config);
    }

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
            $lines.= DebugFormatter::format($record, $this->config);
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