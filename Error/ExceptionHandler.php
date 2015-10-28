<?php

namespace Obullo\Error;

use Exception;
use Obullo\Log\LoggerInterface as Logger;

/**
 * Exception handler
 * 
 * @copyright 2009-2015 Obullo
 * @license   http://opensource.org/licenses/MIT MIT license
 */
class ExceptionHandler
{
    /**
     * Debug on /off variable
     * 
     * @var boolean
     */
    protected $debug;

    /**
     * Constructor
     *
     * @param boolean $debug on / off
     */
    public function __construct($debug = true)
    {
        $this->debug = $debug;
    }

    /**
     * Registers the exception handler.
     *
     * @param boolean $debug on / off
     *
     * @return ExceptionHandler The registered exception handler
     */
    public static function register($debug = true)
    {
        $handler = new static($debug);
        set_exception_handler(array($handler, 'handle'));
        return $handler;
    }

    /**
     * Sends a view response
     *
     * @param object $e An Exception instance
     *
     * @return void
     */
    public function handle(Exception $e)
    {
        global $c;
        $message = $e->getMessage();
        $file    = $e->getFile();
        $line    = $e->getLine();
        $logger  = $c['logger'];
        
        if ($logger instanceof Logger) {         // Log for local environment
            $logger->channel($c['config']['logger']['default']['channel']);
            $logger->error(
                $message,
                [
                    'file' => DebugOutput::getSecurePath($file),
                    'line' => $line
                ]
            );
        }
        $c['exception']->show($e);
    }
}