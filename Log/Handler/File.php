<?php

namespace Obullo\Log\Handler;

use Obullo\Container\Container;
use Obullo\Log\Formatter\LineFormatterTrait;

/**
 * File Handler Class
 * 
 * @category  Log
 * @package   Handler
 * @author    Obullo Framework <obulloframework@gmail.com>
 * @copyright 2009-2014 Obullo
 * @license   http://opensource.org/licenses/MIT MIT license
 * @link      http://obullo.com/package/log
 */
class File extends AbstractHandler implements HandlerInterface
{
    use LineFormatterTrait;

    /**
     * Container
     * 
     * @var object
     */
    protected $c;

    /**
     * Config Constructor
     *
     * @param object $c container
     */
    public function __construct(Container $c)
    {
        $this->c = $c;

        parent::__construct($c);
    }

    /**
     * Write output
     *
     * @param string $data single record data
     * 
     * @return mixed
     */
    public function write(array $data)
    {
        $lines = '';
        foreach ($data['record'] as $record) {
            $record = $this->arrayFormat($data['time'], $record);
            $lines .= $this->lineFormat($record);
        }
        $this->path = static::replacePath($this->c['config']['logger']['file']['path']['http']); // Default http requests
        if ($data['request'] == 'ajax') {
            $this->path = static::replacePath($this->c['config']['logger']['file']['path']['ajax']); // Replace with ajax request path
        }
        if ($data['request'] == 'cli') {
            $this->path = static::replacePath($this->c['config']['logger']['file']['path']['cli']); // Replace with cli request path
        }
        if ( ! $fop = fopen($this->path, 'ab')) {
            return false;
        }
        flock($fop, LOCK_EX);
        fwrite($fop, $lines);
        flock($fop, LOCK_UN);
        fclose($fop);
    }

    /**
     * If log path has "data/logs" folder, we replace it with "DIRECTORY_SEPERATOR. data".
     * 
     * @param string $path log path
     * 
     * @return string current path
     */
    protected static function replacePath($path)
    {
        return ROOT .str_replace('/', DS, trim($path, '/'));
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

// END File class

/* End of file File.php */
/* Location: .Obullo/Log/Handler/File.php */