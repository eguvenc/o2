<?php

namespace Obullo\Log;

use Obullo\Container\ContainerInterface as Container;
use Psr\Http\Message\ServerRequestInterface as Request;

class Benchmark
{
    /**
     * Start application benchmark
     * 
     * @param ContainerInterface $c container
     * 
     * @return object RequestInterface
     */
    public static function start(Container $c)
    {
        $c['REQUEST_TIME_START'] = microtime(true);
    }

    /**
     * Finalize benchmark
     *
     * @param container $c       object
     * @param boolean   $logging enabled
     * @param array     $extra   extra benchmark data
     * 
     * @return void
     */
    public static function end(Container $c, $logging = true, $extra = array())
    {
        $logger = $c['config']->load('service/logger');

        $time = $c['REQUEST_TIME_START'];

        if ($logger['params']['app']['benchmark']['log']) {     // Do we need to generate benchmark data ?

            $end = microtime(true) - $time;
            $usage = 'memory_get_usage() function not found on your php configuration.';
            
            if (function_exists('memory_get_usage') && ($usage = memory_get_usage()) != '') {
                $usage = round($usage/1024/1024, 2). ' MB';
            }
            $extra['time']   = number_format($end, 4);
            $extra['memory'] = $usage;

            if ($logging) {
                $c['logger']->debug('Final output sent to browser', $extra, -9999);
            }
        }
    }
}