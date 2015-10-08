<?php

namespace Obullo\Log\Handler;

/**
 * Handler Interface
 * 
 * @author    Obullo Framework <obulloframework@gmail.com>
 * @copyright 2009-2015 Obullo
 * @license   http://opensource.org/licenses/MIT MIT license
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