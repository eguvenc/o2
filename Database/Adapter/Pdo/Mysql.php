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
     * Pdo connection object
     * 
     * @var null
     */
    public $conn;
    
    /**
     * Pdo Provider
     * 
     * @var object
     */
    public $provider;

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
        $this->conn = (isset($this->params['connection'])) ? $this->provider->get($this->params) : $this->provider->factory($this->params);
        $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);  // Aways show the pdo exceptions errors. // PDO::ERRMODE_SILENT 
    }

    /**
     * Platform specific pdo quote function.
     * 
     * @param mixed $str string
     * 
     * @return string
     */
    public function escape($str)
    {
        if (is_array($str)) {
            foreach ($str as $key => $val) {
                if (is_string($val)) {
                    $str[$key] = $this->escape($val);
                }
            }
            return $str;
        }
        return $this->quote($str, PDO::PARAM_STR);
    }
}

// END Mysql Class
/* End of file Mysql.php

/* Location: .Obullo/Database/Adapter/Pdo/Mysql.php */