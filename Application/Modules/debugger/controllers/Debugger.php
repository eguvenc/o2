<?php

namespace Debugger;

use Obullo\Http\Debugger\Manager;

class Debugger extends \Controller
{
    /**
     * Loader
     * 
     * @return void
     */
    public function load()
    {
        $this->debugger = new Manager($this->c);
    }

    /**
     * Write iframe
     *  
     * @return void
     */
    public function index()
    {
        echo $this->debugger->printConsole();
    }

    /**
     * Close debugger window
     * 
     * @return void
     */
    public function off()
    {
        echo $this->debugger->off();
    }

    /**
     * Clear all log data
     * 
     * @return voide
     */
    public function clear()
    {
        $this->debugger->clear();
        echo $this->debugger->printConsole();
    }

}