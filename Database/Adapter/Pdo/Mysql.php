<?php

namespace Obullo\Database\Adapter\Pdo;

use PDO;
use Obullo\Container\Container;
use Obullo\Service\ServiceProviderInterface;

/**
 * Pdo Mysql Database Driver
 * 
 * @category  Database
 * @package   Adapter
 * @author    Obullo Framework <obulloframework@gmail.com>
 * @copyright 2009-2014 Obullo
 * @license   http://opensource.org/licenses/MIT MIT license
 * @link      http://obullo.com/package/database
 */
class Mysql extends Adapter implements HandlerInterface
{
    /**
     * Pdo Provider
     * 
     * @var object
     */
    public $provider;

    /**
     * Pdo connection object
     * 
     * @var null
     */
    public $connection;

    /**
     * Pdo provider parameters
     * 
     * @var array
     */
    public $params = array();

    /**
     * Connect to PDO
     * 
     * @return void
     */
    public function createConnection()
    {
        $this->connection = (isset($this->params['connection'])) ? $this->provider->get($this->params) : $this->provider->factory($this->params);
        $this->connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);  // Aways show the pdo exceptions errors. // PDO::ERRMODE_SILENT 
    }

    /**
     * Platform specific pdo quote function.
     * 
     * @param string $str  string
     * @param mixed  $type type
     * 
     * @return string
     */
    public function escape($str, $type = PDO::PARAM_STR)
    {
        return $this->quote($str, $type);
    }
}

// END Mysql Class
/* End of file Mysql.php

/* Location: .Obullo/Database/Adapter/Pdo/Mysql.php */