<?php

namespace Debugger;

use Obullo\Debugger\Manager;

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
        echo $this->debugger->printHtml();
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
     * Server ping
     * 
     * @return int 1 or 0
     */
    public function ping()
    {
        echo $this->debugger->ping();
    }

    /**
     * Clear all log data
     * 
     * @return voide
     */
    public function clear()
    {
        $this->debugger->clear();
        $this->index();
    }

}