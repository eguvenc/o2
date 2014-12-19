<?php

namespace Obullo\Tree;

use RunTimeException;

/**
 * Nested Set Model Tree Class
 *
 * Modeled after https://github.com/olimortimer/ci-nested-sets
 *
 * @category  Tree
 * @package   Db
 * @author    Obullo Framework <obulloframework@gmail.com>
 * @copyright 2009-2014 Obullo
 * @license   http://opensource.org/licenses/MIT MIT license
 * @link      http://obullo.com/package/tree
 *
 * https://github.com/olimortimer/ci-nested-sets/blob/master/Nested_set.php
 * http://framework.zend.com/wiki/display/ZFPROP/Zend_Db_NestedSet+-+Graham+Anderson
 * https://github.com/fpietka/Zend-Nested-Set/blob/master/library/Nestedset/Model.php
 * http://ftp.nchu.edu.tw/MySQL/tech-resources/articles/hierarchical-data.html
 *
 */
Class Db
{
    /**
     * Table Constants
     */
    const TABLE_NAME  = 'nested_category';
    const PRIMARY_KEY = 'category_id';
    const PARENT_ID   = 'parent_id';
    const TEXT        = 'name';
    const LEFT        = 'lft';
    const RIGHT       = 'rgt';

    /**
     * Protect sql query identifiers
     * default value is for mysql ( ` ).
     *
     * @var string
     */
    public $escapeChar = '`';

    /**
    * $db Pdo object
    *
    * @var object
    */
    public $db = null;

    /**
     * Tablename
     *
     * @var string
     */
    public $tableName;

    /**
     * Column name parent_id
     *
     * @var string
     */
    public $parentId;

    /**
     * Column name primary key
     *
     * @var string
     */
    public $primaryKey;

    /**
     * Column name text
     *
     * @var string
     */
    public $text;

    /**
     * Column name lft
     *
     * @var string
     */
    public $lft;

    /**
     * Column name rgt
     *
     * @var string
     */
    public $rgt;

    /**
     * $cache Cache object
     *
     * @var object
     */
    public $cache;

    /**
     * Sql query or cached sql query
     * result array
     *
     * @var object
     */
    protected $resultArray;

    /**
     * Constructor
     *
     * @param object $c      container
     * @param array  $params config
     */
    public function __construct($c, $params = array())
    {
        $this->db = ( ! isset($params['db'])) ? $c->load('return service/provider/db') : $params['db']; // set database object.

        $this->tableName  = static::TABLE_NAME; // set default values
        $this->primaryKey = static::PRIMARY_KEY;
        $this->parentId   = static::PARENT_ID;
        $this->text       = static::TEXT;
        $this->lft        = static::LEFT;
        $this->rgt        = static::RIGHT;
    }

    /**
     * Set database table name
     *
     * @param array $table database table name
     *
     * @return void
     */
    public function setTablename($table)
    {
        $this->tableName = $table;
    }

    /**
     * Set primary key column name
     *
     * @param string $key pk name
     *
     * @return void
     */
    public function setPrimaryKey($key)
    {
        $this->primaryKey = $key;
    }

    /**
     * Set parent id column name
     *
     * @param string $key pk name
     *
     * @return void
     */
    public function setParentId($key)
    {
        $this->parentId = $key;
    }

    /**
     * Set left column name
     *
     * @param string $lft left column name
     *
     * @return void
     */
    public function setLft($lft)
    {
        $this->lft = $lft;
    }

    /**
     * Set right column name
     *
     * @param string $rgt right column name
     *
     * @return void
     */
    public function setRgt($rgt)
    {
        $this->rgt = $rgt;
    }

    /**
     * Set text column name
     *
     * @param string $text text column name
     *
     * @return void
     */
    public function setText($text)
    {
        $this->text = $text;
    }

    /**
     * Empties the table currently in use - use with extreme caution!
     *
     * @return boolean
     */
    public function truncateTable()
    {
        return $this->db->exec('TRUNCATE TABLE %s', array($this->tableName));
    }

    /**
     * Adds the first entry to the table.
     *
     * @param string $text  text name
     * @param array  $extra extra data
     *
     * @return void
     */
    public function addTree($text, $extra = array())
    {
        $this->db->query(
            'SELECT MAX(%s) AS %s FROM %s',
            array(
                $this->db->protect($this->rgt),
                $this->db->protect($this->lft),
                $this->tableName
            )
        );
        $result = $this->db->rowArray();

        $data = array(
            $this->parentId => 0,
            $this->text     => $text,
            $this->lft      => (isset($result[$this->lft])) ? $result[$this->lft] + 1 : 0 + 1,
            $this->rgt      => (isset($result[$this->lft])) ? $result[$this->lft] + 2 : 0 + 2,
        );
        
        $data = $this->appendExtraData($data, $extra);

        $this->insert($this->tableName, $data);
    }

    /**
     * Insert to table.
     * 
     * @param string $tableName table name
     * @param array  $data      data
     * 
     * @return void
     */
    public function insert($tableName, $data)
    {
        $this->db->prepare(
            "INSERT INTO %s ( %s ) VALUES ( %s )",
            array(
                $tableName,
                implode(',', array_keys($data)),
                str_repeat("?,", count($data)-1) . '?'
            )
        );
        $i = 0;
        foreach ($data as $value) {
            $i++;
            $param = (is_numeric($value)) ? PARAM_INT : PARAM_STR;
            $this->db->bindValue($i, $value, $param);
        }
        return $this->db->execute();
    }

    /**
     * Get data
     * 
     * @param int $category_id primary key value
     * 
     * @return array sql data
     */
    public function getRow($category_id)
    {
        $this->db->prepare(
            'SELECT * FROM %s WHERE %s = ? LIMIT 1',
            array(
                $this->db->protect($this->tableName),
                $this->db->protect($this->primaryKey)
            )
        );
        $this->db->bindValue(1, $category_id, PARAM_INT);
        $this->db->execute();
        
        return $this->db->rowArray();
    }

    /**
     * Inserts a new node as the first child of the supplied parent node.
     *
     * @param int    $category_id primary key column value
     * @param string $text        value
     * @param array  $extra       extra data
     *
     * @return void
     */
    public function addChild($category_id, $text, $extra = array())
    {
        $row      = $this->getRow((int)$category_id);
        $lftValue = $row[$this->lft];

        $this->updateLeft(2, $lftValue + 1);
        $this->updateRight(2, $lftValue + 1);

        $data = array();
        $data[$this->parentId] = $category_id;
        $data[$this->text]     = $text;
        $data[$this->lft]      = $lftValue + 1;
        $data[$this->rgt]      = $lftValue + 2;

        $data = $this->appendExtraData($data, $extra);

        $this->insert($this->tableName, $data);
    }

    /**
     * Same as addChild except the new node is added as the last child
     *
     * @param int    $category_id primary key column value
     * @param string $text        value
     * @param array  $extra       extra data
     *
     * @return void
     */
    public function appendChild($category_id, $text, $extra = array())
    {
        $row      = $this->getRow((int)$category_id);
        $rgtValue = $row[$this->rgt];

        $this->updateLeft(2, $rgtValue);
        $this->updateRight(2, $rgtValue);
        
        $data = array();
        $data[$this->parentId] = $category_id;
        $data[$this->text]     = $text;
        $data[$this->lft]      = $rgtValue;
        $data[$this->rgt]      = $rgtValue + 1;

        $data = $this->appendExtraData($data, $extra);

        $this->insert($this->tableName, $data);
    }

    /**
     * Inserts a new node as the first sibling of the supplied parent node.
     *
     * @param int    $category_id primary key column value
     * @param string $text        value
     * @param array  $extra       extra data
     *
     * @return void
     */
    public function addSibling($category_id, $text, $extra = array())
    {
        $row      = $this->getRow((int)$category_id);
        $lftValue = $row[$this->lft];

        $this->updateLeft(2, $lftValue);
        $this->updateRight(2, $lftValue);
        
        $data = array();
        $data[$this->parentId] = $row[$this->parentId];
        $data[$this->text]     = $text;
        $data[$this->lft]      = $lftValue;
        $data[$this->rgt]      = $lftValue + 1;

        $data = $this->appendExtraData($data, $extra);

        $this->insert($this->tableName, $data);
    }

    /**
     * Insert a new node to the right of the supplied focusNode
     *
     * @param int    $category_id primary key column value
     * @param string $text        value
     * @param array  $extra       extra data
     *
     * @return void
     */
    public function appendSibling($category_id, $text, $extra = array())
    {
        $row      = $this->getRow((int)$category_id);
        $rgtValue = $row[$this->rgt];

        $this->updateLeft(2, $rgtValue + 1);
        $this->updateRight(2, $rgtValue + 1);

        $data = array();
        $data[$this->parentId] = $row[$this->parentId];
        $data[$this->text]     = $text;
        $data[$this->lft]      = $rgtValue + 1;
        $data[$this->rgt]      = $rgtValue + 2;

        $data = $this->appendExtraData($data, $extra);

        $this->insert($this->tableName, $data);
    }

    /**
     * Deletes the given node (and any children) from the tree table.
     *
     * @param int $primary_key primary key value
     *
     * @return boolean
     */
    public function deleteNode($primary_key)
    {
        $row      = $this->getRow((int)$primary_key);
        $lftValue = $row[$this->lft];
        $rgtValue = $row[$this->rgt];

        $this->db->prepare(
            'DELETE FROM %s WHERE %s >= ? AND  %s <= ?',
            array(
                $this->db->protect($this->tableName),
                $this->db->protect($this->lft),
                $this->db->protect($this->rgt),
            )
        );
        $this->db->bindValue(1, $lftValue, PARAM_INT);
        $this->db->bindValue(2, $rgtValue, PARAM_INT);

        $this->db->execute();

        $this->updateLeft(($lftValue - $rgtValue - 1), $rgtValue + 1);
        $this->updateRight(($lftValue - $rgtValue - 1), $rgtValue + 1);

        return $this->db->getStatement();
    }

    /**
     * Update node
     *
     * @param int $category_id category id
     * @param int $data        data
     *
     * @return void
     */
    public function updateNode($category_id, $data = array())
    {
        if (isset($data[$this->parentId]) AND intval($data[$this->parentId]) > 0) {
            $this->moveAsLastChild($category_id, $data[$this->parentId]);
            unset($data[$this->parentId]);
        }
        
        $update = '';
        foreach ($data as $key => $val) {
            $update .= $this->db->protect($key) . '=' . $this->db->escape($val) . ',';
        }
        $this->db->prepare(
            'UPDATE %s SET %s WHERE %s = ?',
            array(
                $this->db->protect($this->tableName),
                rtrim($update, ','),
                $this->db->protect($this->primaryKey),
            )
        );
        $this->db->bindValue(1, $category_id, PARAM_INT);

        return $this->db->execute();
    }

    /**
     * Get PDO Statement Object
     *
     * @return object
     */
    public function getStatement()
    {
        return $this->db->getStatement();
    }
    
    /**
     * Update parent id
     *
     * @param int $source      source data
     * @param int $category_id target category_id
     *
     * @return void
     */
    public function updateParentId($source, $category_id)
    {
        if (isset($source[$this->parentId]) AND is_numeric($source[$this->parentId])) {
            $parent_id = $source[$this->parentId];
        } else {
            $parent_id = $this->getParentId($source[$this->primaryKey]);
        }

        if ($parent_id === $category_id) {
            return;
        }
        $this->db->prepare(
            'UPDATE %s SET %s = ? WHERE %s = ?',
            array(
                $this->db->protect($this->tableName),
                $this->db->protect($this->parentId),
                $this->db->protect($this->primaryKey)
            )
        );
        $this->db->bindValue(1, $category_id, PARAM_INT);
        $this->db->bindValue(2, $source[$this->primaryKey], PARAM_INT);

        return $this->db->execute();
    }

    /**
     * Move as first child
     *
     * @param array $sourceId source primary key value (id)
     * @param array $targetId target primary key value (id)
     *
     * @return void
     */
    public function moveAsFirstChild($sourceId, $targetId)
    {
        $source = $this->getRow((int)$sourceId);
        $target = $this->getRow((int)$targetId);
        
        $sizeOfTree = $source[$this->rgt] - $source[$this->lft] + 1;
        $value = $target[$this->lft] + 1;

        $this->updateParentId($source, $target[$this->primaryKey]);

        /**
         * Modify Node
         */
        $this->updateLeft($sizeOfTree, $value);
        $this->updateRight($sizeOfTree, $value);

        /**
         * Extend current tree values
         */
        if ($source[$this->lft] >= $value) {
            $source[$this->lft] += $sizeOfTree;
            $source[$this->rgt] += $sizeOfTree;
        }

        /**
         * Modify Range
         */
        $this->updateLeft($value - $source[$this->lft], $source[$this->lft], "AND $this->lft <= ". $source[$this->rgt]);
        $this->updateRight($value - $source[$this->lft], $source[$this->lft], "AND $this->rgt <= " .$source[$this->rgt]);

        /**
         * Modify Node
         */
        $this->updateLeft(- $sizeOfTree, $source[$this->rgt] + 1);
        $this->updateRight(- $sizeOfTree, $source[$this->rgt] + 1);
    }

    /**
     * Move as last child
     *
     * @param array $sourceId source primary key value (id)
     * @param array $targetId target primary key value (id)
     *
     * @return void
     */
    public function moveAsLastChild($sourceId, $targetId)
    {
        $source = $this->getRow((int)$sourceId);
        $target = $this->getRow((int)$targetId);

        $sizeOfTree = $source[$this->rgt] - $source[$this->lft] + 1;
        $value = $target[$this->rgt];

        $this->updateParentId($source, $target[$this->primaryKey]);

        /**
         * Modify Node
         */
        $this->updateLeft($sizeOfTree, $value);
        $this->updateRight($sizeOfTree, $value);

        /**
         * Extend current tree values
         */
        if ($source[$this->lft] >= $value) {
            $source[$this->lft] += $sizeOfTree;
            $source[$this->rgt] += $sizeOfTree;
        }

        /**
         * Modify Range
         */
        $this->updateLeft($value - $source[$this->lft], $source[$this->lft], "AND $this->lft <= " .$source[$this->rgt]);
        $this->updateRight($value - $source[$this->lft], $source[$this->lft], "AND $this->rgt <= " .$source[$this->rgt]);

        /**
         * Modify Node
         */
        $this->updateLeft(- $sizeOfTree, $source[$this->rgt] + 1);
        $this->updateRight(- $sizeOfTree, $source[$this->rgt] + 1);
    }

    /**
     * Move as next sibling
     *
     * @param array $sourceId source primary key value (category_id)
     * @param array $targetId target primary key value (category_id)
     *
     *  @return void
     */
    public function moveAsNextSibling($sourceId, $targetId)
    {
        $source = $this->getRow((int)$sourceId);
        $target = $this->getRow((int)$targetId);

        $sizeOfTree = $source[$this->rgt] - $source[$this->lft] + 1;
        $value = $target[$this->rgt] + 1;

        $this->updateParentId($source, $target[$this->parentId]);

        /**
         * Modify Node
         */
        $this->updateLeft($sizeOfTree, $value);
        $this->updateRight($sizeOfTree, $value);

        /**
         * Extend current tree values
         */
        if ($source[$this->lft] >= $value) {
            $source[$this->lft] += $sizeOfTree;
            $source[$this->rgt] += $sizeOfTree;
        }

        /**
         * Modify Range
         */
        $this->updateLeft($value - $source[$this->lft], $source[$this->lft], "AND $this->lft <= " .$source[$this->rgt]);
        $this->updateRight($value - $source[$this->lft], $source[$this->lft], "AND $this->rgt <= " .$source[$this->rgt]);

        /**
         * Modify Node
         */
        $this->updateLeft(- $sizeOfTree, $source[$this->rgt] + 1);
        $this->updateRight(- $sizeOfTree, $source[$this->rgt] + 1);
    }

    /**
     * Move as prev sibling
     *
     * @param array $sourceId source primary key value (category_id)
     * @param array $targetId target primary key value (category_id)
     *
     * @return void
     */
    public function moveAsPrevSibling($sourceId, $targetId)
    {
        $source = $this->getRow((int)$sourceId);
        $target = $this->getRow((int)$targetId);

        $sizeOfTree = $source[$this->rgt] - $source[$this->lft] + 1;
        $value = $target[$this->lft];

        $this->updateParentId($source, $target[$this->parentId]);

        /**
         * Modify Node
         */
        $this->updateLeft($sizeOfTree, $value);
        $this->updateRight($sizeOfTree, $value);

        /**
         * Extend current tree values
         */
        if ($source[$this->lft] >= $value) {
            $source[$this->lft] += $sizeOfTree;
            $source[$this->rgt] += $sizeOfTree;
        }

        /**
         * Modify Range
         */
        $this->updateLeft($value - $source[$this->lft], $source[$this->lft], "AND $this->lft <= " .$source[$this->rgt]);
        $this->updateRight($value - $source[$this->lft], $source[$this->lft], "AND $this->rgt <= " .$source[$this->rgt]);

        /**
         * Modify Node
         */
        $this->updateLeft(- $sizeOfTree, $source[$this->rgt] + 1);
        $this->updateRight(- $sizeOfTree, $source[$this->rgt] + 1);
    }

    /**
     * Update left column value
     *
     * @param int    $setValue   sql SET value
     * @param int    $whereValue sql WHERE condition value
     * @param string $attribute  extra sql conditions e.g. "AND rgt >= 1"
     *
     * @return void
     */
    protected function updateLeft($setValue, $whereValue, $attribute = '')
    {
        $sql = 'UPDATE %s SET %s = %s + ? WHERE %s >= ? ' . ( ! empty($attribute) ? $attribute : '');
        $this->db->prepare(
            $sql,
            array(
                $this->db->protect($this->tableName),
                $this->db->protect($this->lft),
                $this->db->protect($this->lft),
                $this->db->protect($this->lft)
            )
        );
        $this->db->bindValue(1, $setValue, PARAM_INT);
        $this->db->bindValue(2, $whereValue, PARAM_INT);
        
        return $this->db->execute();
    }
    
    /**
     * Update right column value
     *
     * @param int    $setValue   sql SET value
     * @param int    $whereValue sql WHERE condition value
     * @param string $attribute  extra sql conditions e.g. "AND rgt >= 1"
     *
     * @return void
     */
    protected function updateRight($setValue, $whereValue, $attribute = '')
    {
        $sql = 'UPDATE %s SET %s = %s + ? WHERE %s >= ? ' . ( ! empty($attribute) ? $attribute : '');
        $this->db->prepare(
            $sql,
            array(
                $this->db->protect($this->tableName),
                $this->db->protect($this->rgt),
                $this->db->protect($this->rgt),
                $this->db->protect($this->rgt),
            )
        );
        $this->db->bindValue(1, $setValue, PARAM_INT);
        $this->db->bindValue(2, $whereValue, PARAM_INT);

        return $this->db->execute();
    }

    /**
     * Append extra data.
     *
     * @param array $data  data
     * @param array $extra extra data
     *
     * @return array
     */
    public function appendExtraData($data, $extra)
    {
        if (count($extra) > 0) {
            $data = array_merge($data, $extra);
        }
        $newData = array();
        foreach ($data as $k => $v) {
            $newData[$this->db->protect($k)] = $v;
        }
        return $newData;
    }

    /**
     * Get all tree
     *
     * @param mix    $nodeId primary key value or text column value
     * @param string $select select column
     *
     * @return array
     */
    public function getTree($nodeId = 1, $select = null)
    {
        $str        = ($select == null) ? $this->primaryKey . ',' . $this->text : $select;
        $select     = str_replace(',', $this->escapeChar . ',node.' . $this->escapeChar, $str);
        $columnName = $this->primaryKey;

        if (is_string($nodeId)) {
            $columnName = $this->text;
        }
        $this->db->prepare(
            'SELECT node.%s
                FROM %s AS node,
                %s AS parent
                WHERE node.%s BETWEEN parent.%s
                AND parent.%s
                AND parent.%s = ?
                ORDER BY node.%s',
            array(
                $this->db->protect($select),
                $this->db->protect($this->tableName),
                $this->db->protect($this->tableName),
                $this->db->protect($this->lft),
                $this->db->protect($this->lft),
                $this->db->protect($this->rgt),
                $columnName,
                $this->lft
            )
        );
        $this->db->bindValue(1, $nodeId, PARAM_INT);
        $this->db->execute();
        
        return $this->db->resultArray();
    }

    /**
     * Get depth of sub tree
     *
     * @param mix    $nodeId primary key value or text column value
     * @param string $select select column
     *
     * @return array
     */
    public function getDepthOfSubTree($nodeId = 1, $select = null)
    {
        $str = ($select == null) ? $this->primaryKey . ',' . $this->text : $select;
        $select = str_replace(',', $this->escapeChar . ',node.' . $this->escapeChar, $str);
        $columnName = $this->primaryKey;
        if (is_string($nodeId)) {
            $columnName = $this->text;
        }
        $this->db->prepare(
            'SELECT node.%s, (COUNT(parent.%s) - (sub_tree.depth + 1)) AS depth
                FROM %s AS node,
                %s AS parent,
                %s AS sub_parent,
                (
                SELECT node.%s, (COUNT(parent.%s) - 1) AS depth
                FROM %s AS node,
                %s AS parent
                WHERE node.%s BETWEEN parent.%s AND parent.%s
                AND node.%s = %s
                GROUP BY node.%s
                ORDER BY node.%s
                ) AS sub_tree
                WHERE node.%s BETWEEN parent.%s AND parent.%s
                AND node.%s BETWEEN sub_parent.%s AND sub_parent.%s
                AND sub_parent.%s = sub_tree.%s
                GROUP BY node.%s
                HAVING depth > 0
                ORDER BY node.%s',
            array(
                $this->db->protect($select),
                $this->db->protect($this->text),
                $this->db->protect($this->tableName),
                $this->db->protect($this->tableName),
                $this->db->protect($this->tableName),
                $this->db->protect($this->text),
                $this->db->protect($this->text),
                $this->db->protect($this->tableName),
                $this->db->protect($this->tableName),
                $this->db->protect($this->lft),
                $this->db->protect($this->lft),
                $this->db->protect($this->rgt),
                $columnName,
                $this->db->escape($nodeId),
                $columnName,
                $this->db->protect($this->lft),
                $this->db->protect($this->lft),
                $this->db->protect($this->lft),
                $this->db->protect($this->rgt),
                $this->db->protect($this->lft),
                $this->db->protect($this->lft),
                $this->db->protect($this->rgt),
                $this->db->protect($this->text),
                $this->db->protect($this->text),
                $columnName,
                $this->db->protect($this->lft)
            )
        );
        $this->db->execute();

        return $this->db->resultArray();
    }

    /**
     * Get depth of all tree
     *
     * [0] => Array
     * (
     * [name] => Electronics
     * [parent_id] => 0
     * [depth] => 0
     * )
     *
     * [1] => Array
     * (
     * [name] => Portable Electronics
     * [parent_id] => 1
     * [depth] => 1
     * )
     *
     * @param string $select select column
     *
     * @return array
     */
    public function getAllTree($select = null)
    {
        $str    = ($select == null) ? $this->primaryKey . ',' . $this->parentId . ',' . $this->text : $select;
        $select = str_replace(',', $this->escapeChar . ',node.' . $this->escapeChar, $str);

        $this->db->query(
            'SELECT node.%s, (COUNT(parent.%s) - 1) AS depth
                FROM %s AS node,
                %s AS parent
                WHERE node.%s BETWEEN parent.%s
                AND parent.%s
                GROUP BY node.%s
                ORDER BY node.%s',
            array(
                $this->db->protect($select),
                $this->db->protect($this->text),
                $this->db->protect($this->tableName),
                $this->db->protect($this->tableName),
                $this->db->protect($this->lft),
                $this->db->protect($this->lft),
                $this->db->protect($this->rgt),
                $this->db->protect($this->primaryKey),
                $this->db->protect($this->lft)
            )
        );
        $this->db->execute();

        return $this->db->resultArray();
    }

    /**
     * Return a root node
     *
     * @param string $select select column
     *
     * @return array
     */
    public function getRoot($select = null)
    {
        $str    = ($select == null) ? $this->primaryKey . ',' . $this->text : $select;
        $select = str_replace(',', $this->escapeChar . ',' . $this->escapeChar, $str);

        $this->db->query(
            'SELECT %s FROM %s WHERE %s = 0',
            array(
                $this->db->protect($select),
                $this->db->protect($this->tableName),
                $this->db->protect($this->parentId)
            )
        );
        return $this->db->resultArray();
    }

    /**
     * Return all the siblings of this node.
     *
     * @param int    $category_id category id
     * @param string $select      select column
     *
     * @return array
     */
    public function getSiblings($category_id, $select = null)
    {
        $str       = ($select == null) ? $this->primaryKey . ',' . $this->text : $select;
        $select    = str_replace(',', $this->escapeChar . ',' . $this->escapeChar, $str);
        $parent_id = $this->getParentId((int)$category_id);

        $this->db->prepare(
            'SELECT %s FROM %s WHERE %s = ?',
            array(
                $this->db->protect($select),
                $this->db->protect($this->tableName),
                $this->db->protect($this->parentId),
            )
        );
        $this->db->bindValue(1, $parent_id[$this->parentId], PARAM_INT);
        $this->db->execute();

        return $this->db->resultArray();
    }

    /**
     * Get parent id
     *
     * @param int $category_id category id
     *
     * @return array
     */
    public function getParentId($category_id)
    {
        $this->db->query(
            'SELECT %s FROM %s WHERE %s = %s LIMIT 1',
            array(
                $this->db->protect($this->parentId),
                $this->db->protect($this->tableName),
                $this->db->protect($this->primaryKey)
            )
        );
        $this->db->bindValue(1, $category_id, PARAM_INT);
        $this->db->execute();

        return $this->db->rowArray();
    }

}


// END Db.php File
/* End of file Db.php

/* Location: .Obullo/Tree/Db.php */