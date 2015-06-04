<?php

namespace Obullo\Database\Pdo\Drivers;

use PDO;
use Obullo\Database\Pdo\Adapter;
use Obullo\Service\ServiceProviderInterface;

/**
 * Pdo Mysql Database Driver
 * 
 * @category  Database
 * @package   Mysql
 * @author    Obullo Framework <obulloframework@gmail.com>
 * @copyright 2009-2014 Obullo
 * @license   http://opensource.org/licenses/MIT MIT license
 * @link      http://obullo.com/package/database
 */
class Mysql extends Adapter
{
    /**
     * Column identifier symbol
     * 
     * @var string
     */
    public $escapeIdentifier = '`';

    /**
     * Connect to PDO
     * 
     * @return void
     */
    public function createConnection()
    {
        $this->conn = new PDO($this->params['dsn'], $this->params['username'], $this->params['password'], $this->params['options']);
        $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);  // Aways show the pdo exceptions errors. // PDO::ERRMODE_SILENT 
    }
}

// END Mysql Class
/* End of file Mysql.php

/* Location: .Obullo/Database/Pdo/Drivers/Mysql.php */