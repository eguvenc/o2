<?php

namespace Obullo\Application\Addons;

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
        $this->c['logger']->debug('$_REQUEST_URI: ' . $this->c['uri']->getRequestUri(), array(), 10);
        $this->c['logger']->debug('$_COOKIE: ', $_COOKIE, 9);
        $this->c['logger']->debug('$_POST: ', $_POST, 9);
        $this->c['logger']->debug('$_GET: ', $_GET, 9);
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

        if ($this->c['config']->load('logger')['extra']['benchmark']) {     // Do we need to generate benchmark data ?
            $usage = 'memory_get_usage() function not found on your php configuration.';
            if (function_exists('memory_get_usage') AND ($usage = memory_get_usage()) != '') {
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

/* Location: .Obullo/Application/Addons/BenchmarkTrait.php */