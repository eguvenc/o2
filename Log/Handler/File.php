<?php

namespace Obullo\Log\Handler;

use Obullo\Config\ConfigInterface;
use Obullo\Application\Application;

/**
 * File Handler Class
 * 
 * @category  Log
 * @package   Handler
 * @author    Obullo Framework <obulloframework@gmail.com>
 * @copyright 2009-2015 Obullo
 * @license   http://opensource.org/licenses/MIT MIT license
 * @link      http://obullo.com/package/log
 */
class File extends AbstractHandler implements HandlerInterface
{
    /**
     * File configuration
     * 
     * @var array
     */
    protected $pathArray;

    /**
     * Constructor
     * 
     * @param object $app    \Obullo\Application\Application
     * @param object $config \Obullo\Config\ConfigInterface
     */
    public function __construct(Application $app, ConfigInterface $config)
    {
        parent::__construct($app, $config);

        $this->pathArray = $config['logger']['file']['path'];
    }

    /**
     * Write output
     *
     * @param string $event single log event
     * 
     * @return mixed
     */
    public function write(array $event)
    {
        $lines = '';
        foreach ($event['record'] as $record) {
            $record = $this->arrayFormat($event, $record);
            $lines .= $this->lineFormat($record);
        }
        $type = $event['request'];
        if (isset($this->pathArray[$type])) {
            $this->path = self::resolvePath($this->pathArray[$type]);
        }
        if (! $fop = fopen($this->path, 'ab')) {
            return false;
        }
        flock($fop, LOCK_EX);
        fwrite($fop, $lines);
        flock($fop, LOCK_UN);
        fclose($fop);
    }

    /**
     * If log path has "data/logs" folder, we replace it with "DIRECTORY_SEPERATOR".
     * 
     * @param string $path log path
     * 
     * @return string current path
     */
    protected static function resolvePath($path)
    {
        $path = ltrim($path, '/');
        if (strpos($path, "resources/") === 0) {    // Add root 
            return ROOT .str_replace('/', DS, $path);
        }
        return $path;
    }

    /**
     * Close handler connection
     * 
     * @return void
     */
    public function close()
    {
        return;
    }
}