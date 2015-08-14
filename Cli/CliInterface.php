<?php

namespace Obullo\Cli;

/**
 * Cli Interface
 * 
 * @category  Cli
 * @package   Cli
 * @author    Obullo Framework <obulloframework@gmail.com>
 * @copyright 2009-2015 Obullo
 * @license   http://opensource.org/licenses/MIT MIT license
 * @link      http://obullo.com/package/cli
 */
interface CliInterface
{
    /**
     * Reset variables
     * 
     * @return void
     */
    public function clear();

    /**
     * Resolve command line parameters
     * 
     * @return array resolved parameters
     */
    public function parse();

    /**
     * Get one segment
     * 
     * @param mixed $segment integer number or string segment
     * @param mixed $default default value of segment
     * 
     * @return mixed valid segment or null
     */
    public function segment($segment, $default = null);

    /**
     * Get all segments
     * 
     * @return array all segments
     */
    public function segmentArray();

    /**
     * Get one argument
     * 
     * @param mixed $key     key of argument
     * @param mixed $default default value of argument
     * 
     * @return mixed valid argument or null
     */
    public function argument($key, $default = null);

    /**
     * Get all arguments
     * 
     * @return array all arguments
     */
    public function argumentArray();

    /**
     * Get task controller
     * 
     * @return string
     */
    public function getClass();

    /**
     * Get task method
     * 
     * @return string
     */
    public function getMethod();

    /**
     * Get executed original command with parameters
     *
     * @param boolean $nl whether to use newlines
     * 
     * @return string
     */
    public function getCmdString($nl = true);

    /**
     * Returns to all argument shortcuts
     * 
     * @return array
     */
    public function getShortcuts();
    
}