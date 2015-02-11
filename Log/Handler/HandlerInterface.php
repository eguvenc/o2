<?php

namespace Obullo\Log\Handler;

/**
 * Log Handler Interface
 * 
 * @category  Log
 * @package   Handler
 * @author    Obullo Framework <obulloframework@gmail.com>
 * @copyright 2009-2014 Obullo
 * @license   http://opensource.org/licenses/MIT MIT license
 * @link      http://obullo.com/package/log
 */
interface HandlerInterface
{
    /**
     * Write
     *
     * @param array $data data
     * 
     * @return void
     */
    public function write(array $data);

    /**
     * Close
     * 
     * @return void
     */
    public function close();
}

// END HandlerInterface class

/* End of file HandlerInterface.php */
/* Location: .Obullo/Log/Handler/HandlerInterface.php */