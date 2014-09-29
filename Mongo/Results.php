<?php

namespace Obullo\Mongo;

/**
 * Mongo Db Class Crud Dabase Results.
 * 
 * A library that interfaces with Mongo_Db Package
 * through Crud functions.
 * 
 */
Class Results
{
    protected $rows;    // row array data
    protected $current_row; // current data
    protected $count_rows;  // number of rows
    
    /**
     * Constructor
     * 
     * @param array $rows result array
     */
    public function __construct($rows = null)
    {
        $this->rows        = $rows;
        $this->current_row = 0;
        $this->count_rows  = count($rows);
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
        $result = $this->_getRow();
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
        $result = $this->_getRow();
        if (count($result) == 0) {
            return $result;
        }
        if (isset($result[$this->current_row - 1])) {
            --$this->current_row;
        }
        return $result[$this->current_row];

    }

    /**
     * Fetch next row as object
     * 
     * @return object
     */
    public function nextRow()
    {
        $result = $this->_getRow();
        if (count($result) == 0) {
            return $result;
        }
        if (isset($result[$this->current_row + 1])) {
            ++$this->current_row;
        }
        return $result[$this->current_row];
    }

    /**
     * Fetch last row as object
     * 
     * @return object
     */
    public function lastRow()
    {
        $result = $this->_getRow();
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
        return (int)$this->count_rows;
    }

    /**
     * Private function
     * 
     * @return mixed
     */
    private function _getRow()
    {
        return $this->rows;
    }

}

// END Results.php class
/* End of file Results.php */

/* Location: .Obullo/Mongo/Results.php */