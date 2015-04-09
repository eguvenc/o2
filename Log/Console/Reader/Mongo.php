<?php

namespace Obullo\Log\Console\Reader;

use Obullo\Container\Container;
use Obullo\Log\Console\Printer\Colorful;

/**
 * Mongo Reader
 * 
 * @category  Log
 * @package   Console
 * @author    Obullo Framework <obulloframework@gmail.com>
 * @copyright 2009-2014 Obullo
 * @license   http://opensource.org/licenses/MIT MIT license
 * @link      http://obullo.com/package/log
 */
class Mongo
{
    /**
     * Follow logs
     * 
     * @param string $c          container
     * @param string $dir        sections ( http, ajax, cli )
     * @param string $collection default logs
     * 
     * @return void
     */
    public function follow(Container $c, $dir = 'http', $collection = 'logs')
    {
        $c['config']->load('logger');

        echo "\n\33[1;37mFollowing Mongo Handler ".ucfirst($collection)." Collection ...\33[0m\n";

        // use default provider

        $mongo = $c['service provider mongo']->get(
            [
                'connection' => 'default'
            ]
        ); 
        $mongoCollection = $mongo->{$collection};
        $resultArray = $mongoCollection->find();
        
        $i = 0;
        $printer = new Colorful;
        while (true) {
            if ($mongoCollection->count() > $i) {
                foreach ($resultArray as $val) {
                    $line = str_replace(
                        array(
                            '%datetime%',
                            '%channel%',
                            '%level%',
                            '%message%',
                            '%context%',
                            '%extra%',
                        ), array(
                            date('Y-m-d H:i:s', $val['datetime']->sec),
                            $val['channel'],
                            $val['level'],
                            $val['message'],
                            (is_array($val['context'])) ? json_encode($val['context'], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) : $val['context'],
                            $val['extra']
                        ),
                        str_replace('\n', "\n", $c['config']['logger']['format']['line'])
                    );
                    $printer->printLine($i, $line);
                    $i++;
                }
            }
        }
    }

}

// END Mongo class

/* End of file Mongo.php */
/* Location: .Obullo/Log/Console/Reader/Mongo.php */