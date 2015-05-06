<?php

namespace Obullo\Database\Adapter\Pdo;

use PDO;
use PDOStatement;

/**
 * Pdo Database Result
 * 
 * @category  Database
 * @package   Adapter
 * @author    Obullo Framework <obulloframework@gmail.com>
 * @copyright 2009-2014 Obullo
 * @license   http://opensource.org/licenses/MIT MIT license
 * @link      http://obullo.com/package/database
 */
class Result
{
    /**
     * Pdo Statement
     * 
     * @var object
     */
    public $stmt;

    /**
     * Create pdo statement object
     * 
     * @param PDOStatement $stmt pdo statement object
     */
    public function __construct(PDOStatement $stmt)
    {
        $this->stmt = $stmt;
    }

    /**
     * Returns number of rows.
     *
     * @return integer
     */
    public function count()
    {
        return $this->stmt->rowCount();
    }

    /**
     * Get row as object & if fail return false
     * 
     * @return array | object otherwise false
     */
    public function row()
    {
        return $this->stmt->fetch(PDO::FETCH_OBJ);
    }

    /**
     * Get row as array & if fail return 
     * 
     * @param boolean $failArray return array if fail
     * 
     * @return array | object otherwise false
     */
    public function rowArray($failArray = false)
    {
        $result = $this->stmt->fetch(PDO::FETCH_ASSOC);

        return ( ! $result && $failArray) ? array() : $result;
    }

    /**
     * Get results as array & if fail return ARRAY
     * 
     * @param boolean $failArray return array if fail
     * 
     * @return array | object otherwise false
     */
    public function result($failArray = false)
    {
        $result = $this->stmt->fetchAll(PDO::FETCH_OBJ);

        return ( ! $result && $failArray) ? array() : $result;
    }

    /**
     * Get results as array & if fail return ARRAY
     * 
     * @param boolean $failArray return array if fail
     * 
     * @return array | object otherwise false
     */
    public function resultArray($failArray = false)
    {
        $result = $this->stmt->fetchAll(PDO::FETCH_ASSOC);
        
        return ( ! $result && $failArray) ? array() : $result;
    }

}

// END Result Class
/* End of file Result.php

/* Location: .Obullo/Database/Adapter/Pdo/Result.php */