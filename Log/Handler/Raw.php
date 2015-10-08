<?php

namespace Obullo\Log\Handler;

use Obullo\Config\ConfigInterface;
use Obullo\Application\Application;

/**
 * Raw Handler 
 * 
 * @author    Obullo Framework <obulloframework@gmail.com>
 * @copyright 2009-2015 Obullo
 * @license   http://opensource.org/licenses/MIT MIT license
 */
class Raw extends AbstractHandler implements HandlerInterface
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
            $lines .= $this->lineFormat($record);
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