<?php

namespace Obullo\Console\LogFollower;

/**
 * MongoWriter Follower
 * 
 * @category  Cli
 * @package   LogFollower
 * @author    Obullo Framework <obulloframework@gmail.com>
 * @copyright 2009-2014 Obullo
 * @license   http://www.gnu.org/licenses/gpl-3.0.html GPL Licence
 * @link      http://obullo.com/package/console
 */
Class MongoWriter
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
        echo "\n\33[0;36mFollowing \"mongo\" writer $route log data ...\33[0m\n";
        $mongo           = $c->load('return service/provider/mongo');
        $mongoCollection = $mongo->selectCollection('db', 'logs');
        $resultArray     = $mongoCollection->find();
        $i = 0;
        $printer = new Printer\Colorful;
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
                        str_replace('\n', "\n", $c->load('config')['log']['line'])
                    );
                    $printer->printLine($i, $line);
                    $i++;
                }
            }
        }
    }

}

// END FileWriter class

/* End of file FileWriter.php */
/* Location: .Obullo/Console/LogFollower/FileWriter.php */