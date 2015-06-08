<?php

namespace Obullo\Application\Middlewares;

trait BenchmarkTrait
{
    /**
     * Build benchmark header
     * 
     * @return void
     */
    public function benchmarkStart()
    {
        $_SERVER['REQUEST_TIME_START'] = microtime(true);
        /*
         * ------------------------------------------------------
         *  Console log header
         * ------------------------------------------------------
         */
    }

    /**
     * Log benchmark data after the response
     * 
     * @param string $message final message
     * @param array  $extra   extra benchmark data
     * 
     * @return void
     */
    public function benchmarkEnd($message = 'Final output sent to browser', $extra = array())
    {
        /*
         * ------------------------------------------------------
         *  Console log footer
         * ------------------------------------------------------
         */
        $end = microtime(true) - $_SERVER['REQUEST_TIME_START'];  // End Timer

        if ($this->c['config']->load('logger')['app']['benchmark']) {     // Do we need to generate benchmark data ?
            $usage = 'memory_get_usage() function not found on your php configuration.';
            if (function_exists('memory_get_usage') && ($usage = memory_get_usage()) != '') {
                $usage = round($usage/1024/1024, 2). ' MB';
            }
            $extra['time'] = number_format($end, 4);
            $extra['memory'] = $usage;
        }
        $this->c['logger']->debug($message, $extra, -99);
    }
}

// END BenchmarkTrait File
/* End of file BenchmarkTrait.php

/* Location: .Obullo/Application/Middlewares/BenchmarkTrait.php */