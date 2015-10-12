<?php

namespace Obullo\Http;

trait BenchmarkTrait
{
    /**
     * Build benchmark header
     * 
     * @return void
     */
    public function benchmarkStart()
    {
        $this->c['server.REQUEST_TIME_START'] = microtime(true);
    }

    /**
     * Finalize benchmark operation
     *
     * @param string $message final message
     * @param array  $extra   extra benchmark data
     * 
     * @return void
     */
    public function benchmarkEnd($message = 'Final output sent to browser', $extra = array())
    {
        $logger = $this->c['config']->load('logger');

        if ($logger['app']['benchmark']['log'] && isset($this->c['server.REQUEST_TIME_START'])) {     // Do we need to generate benchmark data ?

            $end = microtime(true) - $this->c['server.REQUEST_TIME_START'];  // End Timer

            $usage = 'memory_get_usage() function not found on your php configuration.';
            if (function_exists('memory_get_usage') && ($usage = memory_get_usage()) != '') {
                $usage = round($usage/1024/1024, 2). ' MB';
            }
            $extra['time']   = number_format($end, 4);
            $extra['memory'] = $usage;

            $this->c['logger']->debug($message, $extra, -99999);
        }
    }

}