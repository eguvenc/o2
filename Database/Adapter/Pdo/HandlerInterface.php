<?php

namespace Obullo\Database\Adapter\Pdo;

use Obullo\Container\Container;
use Obullo\Service\ServiceProviderInterface;

/**
 * Database Handler Interface
 * 
 * @category  Database
 * @package   Handler
 * @author    Obullo Framework <obulloframework@gmail.com>
 * @copyright 2009-2014 Obullo
 * @license   http://opensource.org/licenses/MIT MIT license
 * @link      http://obullo.com/package/database
 */
interface HandlerInterface
{
    /**
     * Create pdo connection
     * 
     * @return void
     */
    public function createConnection();

    /**
     * Platform specific pdo quote function.
     * 
     * @param string $str  string
     * @param mixed  $type type
     * 
     * @return string
     */
    public function escape($str, $type = null);
}

// END HandlerInterface class

/* End of file HandlerInterface.php */
/* Location: .Obullo/Database/Adapter/Pdo/HandlerInterface.php */