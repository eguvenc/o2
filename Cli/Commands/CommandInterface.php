<?php

namespace Obullo\Cli\Commands;

/**
 * Command Interface
 * 
 * @category  Cli
 * @package   Commands
 * @author    Obullo Framework <obulloframework@gmail.com>
 * @copyright 2009-2014 Obullo
 * @license   http://www.gnu.org/licenses/gpl-3.0.html GPL Licence
 * @link      http://obullo.com/package/Cli
 */
interface CommandInterface
{
    /**
     * Execute command
     * 
     * @return bool
     */
    public function run();
}

// END CommandInterface class

/* End of file CommandInterface.php */
/* Location: .Obullo/Cli/Commands/CommandInterface.php */