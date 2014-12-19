<?php

namespace Obullo\Mongo;

/**
 * Mongo Db Class Crud Dabase Results.
 * 
 * A library that interfaces with Mongo_Db Package
 * through Crud functions.
 * 
 * @category  Mongo
 * @package   Results
 * @author    Obullo Framework <obulloframework@gmail.com>
 * @copyright 2009-2014 Obullo
 * @license   http://opensource.org/licenses/MIT
 * @link      http://obullo.com/package/mongo
 */
Class Results
{
    /**
     * Row array data
     * 
     * @var array
     */
    protected $rows;

    /**
     * Current data
     * 
     * @var interger
     */
    protected $currentRow;

    /**
     * Number of rows
     * 
     * @var integer
     */
    protected $countRows;
    
    /**
     * Constructor
     * 
     * @param array $rows result array
     */
    public function __construct($rows = null)
    {
        $this->rows       = $rows;
        $this->currentRow = 0;
        $this->countRows  = count($rows);
    }

    /**
     * Fetch all results as object.
     * 
     * @return array
     */
    public function result()
    {
        $rows = array();
        foreach ($this->rows as $row) {
            $rows[] = (object)$row;
        }
        return $rows;
    }

    /**
     * Fetch all results as array
     * 
     * @return array
     */
    public function resultArray()
    {
        return $this->rows;
    }

    /**
     * Fetch row as object
     *
     * @return object
     */
    public function row()
    {
        return (isset($this->rows[0])) ? (object)$this->rows[0] : (object)$this->rows;
    }

    /**
     * Fetch results as array
     * 
     * @return array
     */
    public function rowArray()
    {
        return (isset($this->rows[0])) ? $this->rows[0] : (array)$this->rows;
    }

    /**
     * Fetch first row as object
     * 
     * @return object
     */
    public function firstRow()
    {
        $result = $this->getRow();
        if (count($result) == 0) {
            return $result;
        }
        return $result[0];
    }
    
    /**
     * Fetch previous row as object
     * 
     * @return object
     */
    public function previousRow()
    {
        $result = $this->getRow();
        if (count($result) == 0) {
            return $result;
        }
        if (isset($result[$this->currentRow - 1])) {
            --$this->currentRow;
        }
        return $result[$this->currentRow];

    }

    /**
     * Fetch next row as object
     * 
     * @return object
     */
    public function nextRow()
    {
        $result = $this->getRow();
        if (count($result) == 0) {
            return $result;
        }
        if (isset($result[$this->currentRow + 1])) {
            ++$this->currentRow;
        }
        return $result[$this->currentRow];
    }

    /**
     * Fetch last row as object
     * 
     * @return object
     */
    public function lastRow()
    {
        $result = $this->getRow();
        if (count($result) == 0) {
            return $result;
        }
        return $result[count($result) - 1];
    }
    
    /**
     * Get number of rows
     * 
     * @return integer
     */
    public function count()
    {
        return (int)$this->countRows;
    }

    /**
     * Protected function
     * 
     * @return mixed
     */
    protected function getRow()
    {
        return $this->rows;
    }

}

// END Results.php class
/* End of file Results.php */

/* Location: .Obullo/Mongo/Results.php */