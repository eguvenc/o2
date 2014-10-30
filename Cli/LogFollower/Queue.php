<?php

namespace Obullo\Cli\LogFollower;

/**
 * Queue Follower
 * 
 * @category  Cli
 * @package   LogFollower
 * @author    Obullo Framework <obulloframework@gmail.com>
 * @copyright 2009-2014 Obullo
 * @license   http://www.gnu.org/licenses/gpl-3.0.html GPL Licence
 * @link      http://obullo.com/package/Cli
 */
Class Queue
{
    /**
     * Follow logs
     *
     * @param string $c     container
     * @param string $route sections ( app, ajax, cli )
     * 
     * @return void
     */
    public function follow($c, $route = 'app')
    {
        $queue = $c->load('service/queue');
        $logger = $c->load('service/logger');

        $writer = $logger->getWriterName();

        echo "\n\33[1;36mFollowing \33[1;37m\33[1;46mQueue Handler ".ucfirst($route)."\33[0m\33[1;36m logs ...\33[0m\n";

        echo "\33[1;36mChannel : ". LOGGER_CHANNEL ."\33[0m\n";
        echo "\33[1;36mRoute   : ". gethostname(). LOGGER_NAME . $writer."\33[0m\n";

        $queue->channel(LOGGER_CHANNEL);  // Sets queue exchange
    
        $i = 0;
        while (true) {
            $job = $queue->pop(gethostname(). LOGGER_NAME .$writer);
            if ( ! is_null($job)) {
                $body = json_decode($job->getRawBody(), true);

                if ($body['data']['type'] == $route) {
                    foreach ($body['data']['record'] as $val) {

                        if (is_string($val)) {
                            $printer = new Printer\Colorful;
                            $printer->printLine($i, $val);
                            $i++;
                        } else {
                            $line = str_replace(
                                array(
                                    '%datetime%',
                                    '%channel%',
                                    '%level%',
                                    '%message%',
                                    '%context%',
                                    '%extra%',
                                ), array(
                                    (is_array($val) AND isset($val['datetime']['sec'])) ? date('Y-m-d H:i:s', $val['datetime']['sec']) : date('Y-m-d H:i:s'),
                                    $val['channel'],
                                    $val['level'],
                                    $val['message'],
                                    (is_array($val['context'])) ? preg_replace("/[\n\r\n]/", '', var_export($val['context'], true)) : $val['context'],
                                    $val['extra']
                                ),
                                str_replace('\n', "\n", $c->load('config')['log']['line'])
                            );
                            $printer = new Printer\Colorful;
                            $printer->printLine($i, $line);
                            $i++;

                        } //  end if

                    }  // end foreach
                }

            } 

        } // end while

    } 

}

// END Queue class

/* End of file Queue.php */
/* Location: .Obullo/Cli/LogFollower/Queue.php */