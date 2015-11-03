<?php

namespace Obullo\Log;

use Psr\Http\Message\ServerRequestInterface as Request;

class Benchmark
{
    /**
     * Start app benchmark
     * 
     * @param Request $request object
     * 
     * @return object
     */
    public static function start(Request $request)
    {
        return $request->withAttribute('REQUEST_TIME_START', microtime(true));
    }

    /**
     * Finalize benchmark
     * 
     * @param Request $request request
     * @param array   $extra   extra
     * 
     * @return void
     */
    public static function end(Request $request, $extra = array())
    {
        $c = $request->getContainer();

        $config = $c['config']->load('service/logger');

        if ($config['params']['app']['benchmark']['log']) {     // Do we need to generate benchmark data ?

            $end = microtime(true) - $request->getAttribute('REQUEST_TIME_START');
            $usage = 'memory_get_usage() function not found on your php configuration.';
            
            if (function_exists('memory_get_usage') && ($usage = memory_get_usage()) != '') {
                $usage = round($usage/1024/1024, 2). ' MB';
            }
            if ($c['config']['http']['debugger']['enabled']) {  // Exclude debugger cost from benchmark results.
                $end = $end - 0.0003;
            }
            $extra['time']   = number_format($end, 4);
            $extra['memory'] = $usage;

            $c['logger']->debug('Final output sent to browser', $extra, -9999);
        }
    }
}