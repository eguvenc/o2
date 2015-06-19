<?php

namespace Obullo\Log\Handler;

use Obullo\Container\ContainerInterface;

/**
 * Raw Handler Class
 * 
 * @category  Log
 * @package   Handler
 * @author    Obullo Framework <obulloframework@gmail.com>
 * @copyright 2009-2014 Obullo
 * @license   http://opensource.org/licenses/MIT MIT license
 * @link      http://obullo.com/package/log
 */
class Raw extends AbstractHandler implements HandlerInterface
{
    /**
     * Config Constructor
     *
     * @param object $c container
     */
    public function __construct(ContainerInterface $c)
    {
        parent::__construct($c);
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

// END Raw class

/* End of file Raw.php */
/* Location: .Obullo/Log/Handler/Raw.php */