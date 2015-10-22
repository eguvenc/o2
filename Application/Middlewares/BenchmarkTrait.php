<?php

namespace Obullo\Application\Middlewares;

use Psr\Http\Message\ServerRequestInterface;

trait BenchmarkTrait
{
    /**
     * Start application benchmark
     * 
     * @param ServerRequestInterface $request 
     * 
     * @return object RequestInterface
     */
    public function benchmarkStart(ServerRequestInterface $request)
    {
        return $request->withAttribute('REQUEST_TIME_START', microtime(true));
    }

    /**
     * Finalize benchmark
     *
     * @param ServerRequestInterface $request psr7 request object
     * @param boolean                $logging enabled
     * @param array                  $extra   extra benchmark data
     * 
     * @return void
     */
    public function benchmarkEnd(ServerRequestInterface $request, $logging = true, $extra = array())
    {
        $logger = $this->config->load('logger');

        if ($logger['app']['benchmark']['log']) {     // Do we need to generate benchmark data ?

            $time = $request->getAttribute('REQUEST_TIME_START');
            $end = microtime(true) - $time;
            $usage = 'memory_get_usage() function not found on your php configuration.';
            
            if (function_exists('memory_get_usage') && ($usage = memory_get_usage()) != '') {
                $usage = round($usage/1024/1024, 2). ' MB';
            }
            $extra['time']   = number_format($end, 4);
            $extra['memory'] = $usage;

            if ($logging) {
                $this->logger->debug('Final output sent to browser', $extra, -9999);
            }
        }
        return $request;
    }

}