<?php

namespace Obullo\Database\Pdo;

use RunTimeException,
    Exception;

/**
 * CRUD ( CREATE - READ - UPDATE - DELETE ) Class
 * 
 * Borrowed from Codeigniter Active Record
 * 
 * @category  Database
 * @package   Crud
 * @author    Obullo Framework <obulloframework@gmail.com>
 * @copyright 2009-2014 Obullo
 * @license   http://opensource.org/licenses/MIT MIT license
 * @link      http://obullo.com/package/database
 */
Class QueryBuilder
{
    public $arSelect = array();
    public $arDistinct = false;
    public $arFrom = array();
    public $arJoin = array();
    public $arWhere = array();
    public $arLike = array();
    public $arGroupBy = array();
    public $arHaving = array();
    public $arLimit = false;
    public $arOffset = false;
    public $arOrder = false;
    public $arOrderBy = array();
    public $arSet = array();
    public $arWherein = array();
    public $arAliasedTables = array();
    public $arStoreArray = array();

    /** Caching variables **/

    public $arCaching = false;
    public $arCacheExists = array();
    public $arCacheSelect = array();
    public $arCacheFrom = array();
    public $arCacheJoin = array();
    public $arCacheWhere = array();
    public $arCacheLike = array();
    public $arCacheGroupBy = array();
    public $arCacheHaving = array();
    public $arCacheOrderBy = array();
    public $arCacheSet = array();

    /**
     * Pdo Adapter ( mysql, pgsql .. )
     * 
     * @var object
     */
    public $adapter;

    /**
     * Constructor
     *
     * @param object $c  container
     * @param object $db database object
     */
    public function __construct($c, $db)
    {
        $c['config']->load('database');

        if ( ! is_object($db)) {
            throw new RunTimeException('Crud class requires database object.');
        }
        $this->adapter = $db;  // load pdo adapter object.
    }

    /**
     * If method not exists call from adapter class
     * 
     * @param string $method    methodname
     * @param array  $arguments method arguments
     * 
     * @return mixed
     */
    public function __call($method, $arguments)
    {
        return call_user_func_array(array($this->adapter, $method), $arguments);
    }

    /**
     * Select
     * 
     * @param string  $select fields
     * @param boolean $escape use escape or noe 
     * 
     * @return object
     */
    public function select($select = '*', $escape = null)
    {
        if (is_bool($escape)) {   // Set the global value if this was sepecified  
            $this->adapter->protectIdentifiers = $escape;
        }
        if (is_string($select)) {
            $select = explode(',', $select);
        }
        foreach ($select as $val) {
            $val = trim($val);
            if ($val != '') {
                $this->arSelect[] = $val;
                if ($this->arCaching === true) {
                    $this->arCacheSelect[] = $val;
                    $this->arCacheExists[] = 'select';
                }
            }
        }
        return $this;
    }

    /**
     * Join tables
     * 
     * @param string $table tablename
     * @param string $cond  join condition
     * @param string $type  join type ( inner, outer, left, right  .. )
     * 
     * @return object
     */
    public function join($table, $cond, $type = '')
    {
        if ($type != '') {
            $type = strtoupper(trim($type));

            if ( ! in_array($type, array('LEFT', 'RIGHT', 'OUTER', 'INNER', 'LEFT OUTER', 'RIGHT OUTER'))) {
                $type = '';
            } else {
                $type.= ' ';
            }
        }
        //
        // Extract any aliases that might exist.  We use this information
        // in the _protectIdentifiers to know whether to add a table prefix 
        // 
        $this->_trackAliases($table);

        if (preg_match('/([\w\.]+)([\W\s]+)(.+)/', $cond, $match)) {   // Strip apart the condition and protect the identifiers
            $match[1] = $this->_protectIdentifiers($match[1]);
            $match[3] = $this->_protectIdentifiers($match[3]);

            $cond = $match[1] . $match[2] . $match[3];
        }
        $join = $type . 'JOIN ' . $this->_protectIdentifiers($table, true, null, false) . ' ON ' . $cond;  // Assemble the JOIN statement
        $this->arJoin[] = $join;

        if ($this->arCaching === true) {
            $this->arCacheJoin[] = $join;
            $this->arCacheExists[] = 'join';
        }
        return $this;
    }

    /**
     * From 
     * 
     * @param array $from sql from
     * 
     * @return object
     */
    public function from($from)
    {
        foreach ((array) $from as $val) {
            if (strpos($val, ',') !== false) {
                foreach (explode(',', $val) as $v) {
                    $v = trim($v);
                    $this->_trackAliases($v);
                    $this->arFrom[] = $this->_protectIdentifiers($v, true, null, false);

                    if ($this->arCaching === true) {
                        $this->arCacheFrom[] = $v;
                        $this->arCacheExists[] = 'from';
                    }
                }
            } else {

                $val = trim($val);

                // Extract any aliases that might exist.  We use this information
                // in the protect identifiers to know whether to add a table prefix 
                $this->_trackAliases($val);

                $this->arFrom[] = $this->_protectIdentifiers($val, true, null, false);

                if ($this->arCaching === true) {
                    $this->arCacheFrom[] = $this->_protectIdentifiers($val, true, null, false);
                    $this->arCacheExists[] = 'from';
                }
            }
        } // end
        return ($this);
    }

    /**
     * Get
     *
     * Compiles the select statement based on the other functions called
     * and runs the query
     *
     * @param string  $table  the table
     * @param integer $limit  the limit clause
     * @param integer $offset the offset clause
     * 
     * @return object
     */
    public function get($table = '', $limit = null, $offset = null)
    {
        if ($table != '') {
            $this->_trackAliases($table);
            $this->from($table);
        }
        if ( ! is_null($limit)) {
            $this->limit($limit, $offset);
        }
        $this->adapter->sql = $this->_compileSelect();

        $result = $this->adapter->query($this->adapter->sql);
        $this->_resetSelect();
        return $result;
    }

    /**
     * Where clause
     * 
     * @param string  $key    key
     * @param mixed   $value  value
     * @param boolean $escape whether to false quotes
     * 
     * @return object
     */
    public function where($key, $value = null, $escape = true)
    {
        return $this->_where($key, $value, 'AND ', $escape);
    }

    /**
     * Like where but use also IN clause
     * 
     * @param string $key    key
     * @param array  $values values
     * 
     * @return object
     */
    public function whereIn($key = null, $values = null)
    {
        return $this->_whereIn($key, $values);
    }

    /**
     * Like where but use also OR clause
     * 
     * @param string  $key    key
     * @param mixed   $value  value
     * @param boolean $escape whether to false quotes
     * 
     * @return object
     */
    public function orWhere($key, $value = null, $escape = true)
    {
        return $this->_where($key, $value, 'OR ', $escape);
    }

    /**
     * Like where but use also IN clause
     * 
     * @param string $key    key
     * @param array  $values values
     * 
     * @return object
     */
    public function orWhereIn($key = null, $values = null)
    {
        return $this->_whereIn($key, $values, false, 'OR ');
    }

    /**
     * Like where but use also OR NOT IN clause
     * 
     * @param string $key    key
     * @param array  $values values
     * 
     * @return object
     */
    public function orWhereNotIn($key = null, $values = null)
    {
        return $this->_whereIn($key, $values, true, 'OR ');
    }

    /**
     * Like where but use also NOT IN clause
     * 
     * @param string $key    key
     * @param array  $values values
     * 
     * @return object
     */
    public function whereNotIn($key = null, $values = null)
    {
        return $this->_whereIn($key, $values, true);
    }

    /**
     * Where
     *
     * Called by where() or orWhere()
     *
     * @param mixed  $key    key
     * @param mixed  $value  value
     * @param string $type   clause type
     * @param string $escape whether to escape
     * 
     * @return object
     */
    public function _where($key, $value = null, $type = 'AND ', $escape = null)
    {
        if ( ! is_array($key)) {
            $key = array($key => $value);
        }
        if ( ! is_bool($escape)) {   // If the escape value was not set will base it on the global setting
            $escape = $this->adapter->protectIdentifiers;
        }
        foreach ($key as $k => $v) {
            $prefix = (sizeof($this->arWhere) == 0 AND sizeof($this->arCacheWhere) == 0) ? '' : $type;

            if (is_null($v) AND ! $this->_hasOperator($k)) {
                $k .= ' IS null';   // value appears not to have been set, assign the test to IS null 
            }
            if ( ! is_null($v)) {

                if ($escape === true) {

                    $k = $this->_protectIdentifiers($k, false, $escape);
                    $v = ' ' . $this->adapter->escape($v);

                } elseif (is_string($v)) {
                    $v = "{$v}";
                }
                if ( ! $this->_hasOperator($k)) {
                    $k .= ' =';
                }
                
            } else {
                $k = $this->_protectIdentifiers($k, false, $escape);
            }
            $this->arWhere[] = $prefix . $k . $v;

            if ($this->arCaching === true) {
                $this->arCacheWhere[]  = $prefix . $k . $v;
                $this->arCacheExists[] = 'where';
            }
        }
        return $this;
    }

    /**
     * Where_in
     *
     * Called by where_in, where_in_or, where_not_in, where_not_in_or
     *
     * @param string  $key    The field to search
     * @param array   $values The values searched on
     * @param boolean $not    If the statement would be IN or NOT IN
     * @param string  $type   clause type
     * 
     * @return object
     */
    public function _whereIn($key = null, $values = null, $not = false, $type = 'AND ')
    {
        if ($key === null OR $values === null) {
            return;
        }
        if (!is_array($values)) {
            $values = array($values);
        }
        $not = ($not) ? ' NOT' : '';

        foreach ($values as $value) {
            $this->arWherein[] = $this->escape($value);
        }
        $prefix = (sizeof($this->arWhere) == 0) ? '' : $type;
        $where_in = $prefix . $this->_protectIdentifiers($key) . $not . " IN (" . implode(", ", $this->arWherein) . ") ";

        $this->arWhere[] = $where_in;
        if ($this->arCaching === true) {
            $this->arCacheWhere[]  = $where_in;
            $this->arCacheExists[] = 'where';
        }
        $this->arWherein = array();   // reset the array for multiple calls
        return $this;
    }

    /**
     * Like clause
     * 
     * @param string $field name
     * @param string $match value
     * @param string $side  direction
     * 
     * @return object
     */
    public function like($field, $match = '', $side = 'both')
    {
        return $this->_like($field, $match, 'AND ', $side);
    }

    /**
     * Like "Like" also use OR
     * 
     * @param string $field name
     * @param string $match value
     * @param string $side  direction
     * 
     * @return object
     */
    public function orLike($field, $match = '', $side = 'both')
    {
        return $this->_like($field, $match, 'OR ', $side);
    }

    /**
     * Like "Like" also use NOT
     * 
     * @param string $field name
     * @param string $match value
     * @param string $side  direction
     * 
     * @return object
     */
    public function notLike($field, $match = '', $side = 'both')
    {
        return $this->_like($field, $match, 'AND ', $side, 'NOT');
    }

    /**
     * Like "Like" also use OR NOT
     * 
     * @param string $field name
     * @param string $match value
     * @param string $side  direction
     * 
     * @return object
     */
    public function orNotLike($field, $match = '', $side = 'both')
    {
        return $this->_like($field, $match, 'OR ', $side, 'NOT');
    }

    /**
     * Sets the ORDER BY value
     *
     * @param string $orderby   fieldname(s)
     * @param string $direction ASC or DESC
     * 
     * @return object
     */
    public function orderBy($orderby, $direction = '')
    {
        $direction = strtoupper(trim($direction));
        if ($direction != '') {
            switch ($direction) {
            case 'ASC':
                $direction = ' ASC';
                break;
            case 'DESC':
                $direction = ' DESC';
                break;
            default:
                $direction = ' ASC';
            }
        }
        if (strpos($orderby, ',') !== false) {
            $temp = array();
            foreach (explode(',', $orderby) as $part) {
                $part = trim($part);
                if (!in_array($part, $this->arAliasedTables)) {
                    $part = $this->_protectIdentifiers(trim($part));
                }
                $temp[] = $part;
            }
            $orderby = implode(', ', $temp);
        } else {
            $orderby = $this->_protectIdentifiers($orderby);
        }
        $orderby_statement  = $orderby . $direction;
        $this->arOrderBy[] = $orderby_statement;

        if ($this->arCaching === true) {
            $this->arCacheOrderBy[] = $orderby_statement;
            $this->arCacheExists[]  = 'orderby';
        }
        return $this;
    }

    /**
     * Sets the HAVING values
     *
     * Called by having() or orHaving()
     *
     * @param string $key    name
     * @param string $value  value
     * @param string $type   type
     * @param string $escape whether to escape
     * 
     * @return   object
     */
    public function _having($key, $value = '', $type = 'AND ', $escape = true)
    {
        if (!is_array($key)) {
            $key = array($key => $value);
        }
        foreach ($key as $k => $v) {
            $prefix = (sizeof($this->arHaving) == 0) ? '' : $type;
            if ($escape === true) {
                $k = $this->_protectIdentifiers($k);
            }
            if (!$this->_hasOperator($k)) {
                $k .= ' = ';
            }
            if ($v != '') {
                $v = ' ' . $this->adapter->escape($v);  // obullo changes ..
            }
            $this->arHaving[] = $prefix . $k . $v;
            if ($this->arCaching === true) {
                $this->arCacheHaving[] = $prefix . $k . $v;
                $this->arCacheExists[] = 'having';
            }
        }
        return $this;
    }

    /**
     * orHaving description
     * 
     * @param string  $key    key
     * @param string  $value  value
     * @param boolean $escape whether to escape
     * 
     * @return object
     */
    public function orHaving($key, $value = '', $escape = true)
    {
        return $this->_having($key, $value, 'OR ', $escape);
    }

    /**
     * Like
     *
     * Called by like() or orLike()
     *
     * @param mixed  $field fieldname
     * @param mixed  $match match
     * @param string $type  type
     * @param string $side  direction
     * @param string $not   not clause
     * 
     * @return object
     */
    public function _like($field, $match = '', $type = 'AND ', $side = 'both', $not = '')
    {
        if ( ! is_array($field)) {
            $field = array($field => $match);
        }
        foreach ($field as $k => $v) {
            $k = $this->_protectIdentifiers($k);
            $prefix = (sizeof($this->arLike) == 0) ? '' : $type;

            $like_statement = $prefix . " $k $not LIKE " . $this->adapter->escapeLike($v, $side);

            // some platforms require an escape sequence definition for LIKE wildcards
            if ($this->adapter->likeEscapeStr != '') {
                $like_statement = $like_statement . sprintf($this->adapter->likeEscapeStr, $this->adapter->likeEscapeChr);
            }
            $this->arLike[] = $like_statement;
            if ($this->arCaching === true) {
                $this->arCacheLike[] = $like_statement;
                $this->arCacheExists[] = 'like';
            }
        }
        return $this;
    }

    /**
     * Limit
     * 
     * @param integer $value  limit value
     * @param integer $offset offset
     * 
     * @return object
     */
    public function limit($value, $offset = '')
    {
        $this->arLimit = $value;
        if ($offset != '') {
            $this->arOffset = $offset;
        }
        return $this;
    }

    /**
     * Offset
     * 
     * @param integer $offset offset
     * 
     * @return object
     */
    public function offset($offset)
    {
        $this->arOffset = $offset;
        return $this;
    }

    /**
     * Group by
     *
     * @param string $by group by
     * 
     * @return object
     */
    public function groupBy($by)
    {
        if (is_string($by)) {
            $by = explode(',', $by);
        }
        foreach ($by as $val) {
            $val = trim($val);
            if ($val != '') {
                $this->arGroupBy[] = $this->_protectIdentifiers($val);
                if ($this->arCaching === true) {
                    $this->arCacheGroupBy[] = $this->_protectIdentifiers($val);
                    $this->arCacheExists[]  = 'groupby';
                }
            }
        }
        return $this;
    }

    /**
     * Compile the SELECT statement
     *
     * Generates a query string based on which functions were used.
     * Should not be called directly.  The get() function calls it.
     *
     * @param boolean $select_override whether override
     * 
     * @return string
     */
    public function _compileSelect($select_override = false)
    {
        $this->_mergeCache();   // Combine any cached components with the current statements

        if ($select_override !== false) { // Write the "select" portion of the query
            $sql = $select_override;
        } else {
            $sql = ( ! $this->arDistinct) ? 'SELECT ' : 'SELECT DISTINCT ';

            if (count($this->arSelect) == 0) {
                $sql .= '*';
            } else {
                // Cycle through the "select" portion of the query and prep each column name.
                // The reason we protect identifiers here rather then in the select() function
                // is because until the user calls the from() function we don't know if there are aliases
                foreach ($this->arSelect as $key => $val) {
                    $this->arSelect[$key] = $this->_protectIdentifiers($val);
                }
                $sql .= implode(', ', $this->arSelect);
            }
        }

        // Write the "FROM" portion of the query

        if (count($this->arFrom) > 0) {
            $sql .= "\nFROM ";
            $sql .= $this->adapter->_fromTables($this->arFrom);
        }

        // Write the "JOIN" portion of the query

        if (count($this->arJoin) > 0) {
            $sql .= "\n";
            $sql .= implode("\n", $this->arJoin);
        }

        // Write the "WHERE" portion of the query

        if (count($this->arWhere) > 0 OR count($this->arLike) > 0) {
            $sql .= "\n";
            $sql .= "WHERE ";
        }

        $sql .= implode("\n", $this->arWhere);

        // Write the "LIKE" portion of the query

        if (count($this->arLike) > 0) {
            if (count($this->arWhere) > 0) {
                $sql .= "\nAND ";
            }
            $sql .= implode("\n", $this->arLike);
        }

        // Write the "GROUP BY" portion of the query

        if (count($this->arGroupBy) > 0) {
            $sql .= "\nGROUP BY ";
            $sql .= implode(', ', $this->arGroupBy);
        }

        // Write the "HAVING" portion of the query

        if (count($this->arHaving) > 0) {
            $sql .= "\nHAVING ";
            $sql .= implode("\n", $this->arHaving);
        }

        // Write the "ORDER BY" portion of the query

        if (count($this->arOrderBy) > 0) {
            $sql .= "\nORDER BY ";
            $sql .= implode(', ', $this->arOrderBy);

            if ($this->arOrder !== false) {
                $sql .= ($this->arOrder == 'desc') ? ' DESC' : ' ASC';
            }
        }

        // Write the "LIMIT" portion of the query

        if (is_numeric($this->arLimit)) {
            $sql .= "\n";
            $sql = $this->adapter->_limit($sql, $this->arLimit, $this->arOffset);
        }
        return $sql;
    }

    /**
     * Tests whether the string has an SQL operator
     *
     * @param string $str sql string
     * 
     * @return bool
     */
    public function _hasOperator($str)
    {
        $str = trim($str);
        if ( ! preg_match("/(\s|<|>|!|=|is null|is not null)/i", $str)) {
            return false;
        }
        return true;
    }

    /**
     * Resets the active record values.  Called by the get() function
     *
     * @param array $ar_reset_items an array of fields to reset
     * 
     * @return void
     */
    public function _resetRun($ar_reset_items)
    {
        foreach ($ar_reset_items as $item => $default_value) {
            if ( ! in_array($item, $this->arStoreArray)) {
                $this->{$item} = $default_value;
            }
        }
    }

    /**
     * Resets the active record values.  Called by the get() function
     *
     * @return    void
     */
    public function _resetSelect()
    {
        $ar_reset_items = array(
            'arSelect' => array(),
            'arFrom' => array(),
            'arJoin' => array(),
            'arWhere' => array(),
            'arLike' => array(),
            'arGroupBy' => array(),
            'arHaving' => array(),
            'arOrderBy' => array(),
            'arWherein' => array(),
            'arAliasedTables' => array(),
            'arDistinct' => false,
            'arLimit' => false,
            'arOffset' => false,
            'arOrder' => false,
        );
        $this->_resetRun($ar_reset_items);
    }

    /**
     * Resets the active record "write" values.
     *
     * Called by the insert() update() and delete() functions
     *
     * @return    void
     */
    public function _resetWrite()
    {
        $ar_reset_items = array(
            'arSet' => array(),
            'arFrom' => array(),
            'arWhere' => array(),
            'arLike' => array(),
            'arOrderBy' => array(),
            'arLimit' => false,
            'arOrder' => false,
        );
        $this->_resetRun($ar_reset_items);
    }

    /**
     * Protect Identifiers
     *
     * This function adds backticks if appropriate based on db type
     *
     * @param mixed  $item         the item to escape
     * @param boelan $prefixSingle prefix single option
     * 
     * @return mixed the item with backticks
     */
    public function protectIdentifiers($item, $prefixSingle = false)
    {
        return $this->_protectIdentifiers($item, $prefixSingle);
    }

    /**
     * Protect Identifiers
     *
     * This function is used extensively by the Active Record class, and by
     * a couple functions in this class.
     * It takes a column or table name (optionally with an alias) and inserts
     * the table prefix onto it.  Some logic is necessary in order to deal with
     * column names that include the path.  Consider a query like this:
     *
     * SELECT * FROM hostname.database.table.column AS c FROM hostname.database.table
     *
     * Or a query with aliasing:
     *
     * SELECT m.member_id, m.member_name FROM members AS m
     *
     * Since the column name can include up to four segments (host, DB, table, column)
     * or also have an alias prefix, we need to do a bit of work to figure this out and
     * insert the table prefix (if it exists) in the proper position, and escape only
     * the correct identifiers.
     *
     * @param string  $item               item
     * @param boolean $prefixSingle       feature
     * @param mixed   $protectIdentifiers feature
     * @param bool    $fieldExists        control
     * 
     * @return string
     */
    public function _protectIdentifiers($item, $prefixSingle = false, $protectIdentifiers = null, $fieldExists = true)
    {
        if ( ! is_bool($protectIdentifiers)) {
            $protectIdentifiers = $this->adapter->protectIdentifiers;
        }

        if (is_array($item)) {
            $escaped_array = array();
            foreach ($item as $k => $v) {
                $escaped_array[$this->_protectIdentifiers($k)] = $this->_protectIdentifiers($v);
            }
            return $escaped_array;
        }
        $item = preg_replace('/[\t ]+/', ' ', $item); // Convert tabs or multiple spaces into single spaces

        // If the item has an alias declaration we remove it and set it aside.
        // Basically we remove everything to the right of the first space
        $alias = '';
        if (strpos($item, ' ') !== false) {
            $alias = strstr($item, " ");
            $item = substr($item, 0, - strlen($alias));
        }

        // This is basically a bug fix for queries that use MAX, MIN, etc.
        // If a parenthesis is found we know that we do not need to
        // escape the data or add a prefix.  There's probably a more graceful
        // way to deal with this, but I'm not thinking of it -- Rick
        if (strpos($item, '(') !== false) {
            return $item . $alias;
        }

        // Break the string apart if it contains periods, then insert the table prefix
        // in the correct location, assuming the period doesn't indicate that we're dealing
        // with an alias. While we're at it, we will escape the components
        if (strpos($item, '.') !== false) {
            $parts = explode('.', $item);

            // Does the first segment of the exploded item match
            // one of the aliases previously identified?  If so,
            // we have nothing more to do other than escape the item
            if (in_array($parts[0], $this->arAliasedTables)) {
                if ($protectIdentifiers === true) {
                    foreach ($parts as $key => $val) {
                        if ( ! in_array($val, $this->adapter->reservedIdentifiers)) {
                            $parts[$key] = $this->adapter->_escapeIdentifiers($val);
                        }
                    }
                    $item = implode('.', $parts);
                }
                return $item . $alias;
            }

            // Is there a table prefix defined in the config file?  If not, no need to do anything
            if ($this->adapter->prefix != '') {
                // We now add the table prefix based on some logic.
                // Do we have 4 segments (hostname.database.table.column)?
                // If so, we add the table prefix to the column name in the 3rd segment.
                if (isset($parts[3])) {
                    $i = 2;
                } elseif (isset($parts[2])) {       // Do we have 3 segments (database.table.column)?
                    $i = 1;                         // If so, we add the table prefix to the column name in 2nd position
                } else {                  // Do we have 2 segments (table.column)?
                    $i = 0;               // If so, we add the table prefix to the column name in 1st segment
                }

                // This flag is set when the supplied $item does not contain a field name.
                // This can happen when this function is being called from a JOIN.
                if ($fieldExists == false) {
                    $i++;
                }
                // We only add the table prefix if it does not already exist
                if (substr($parts[$i], 0, strlen($this->adapter->prefix)) != $this->adapter->prefix) {
                    $parts[$i] = $this->adapter->prefix . $parts[$i];
                }
                // Put the parts back together
                $item = implode('.', $parts);
            }
            if ($protectIdentifiers === true) {
                $item = $this->adapter->_escapeIdentifiers($item);
            }
            return $item . $alias;
        }
        if ($this->adapter->prefix != '') {          // Is there a table prefix?  If not, no need to insert it
            // Do we prefix an item with no segments?
            if ($prefixSingle == true AND substr($item, 0, strlen($this->adapter->prefix)) != $this->adapter->prefix) {
                $item = $this->adapter->prefix . $item;
            }
        }
        if ($protectIdentifiers === true AND !in_array($item, $this->adapter->reservedIdentifiers)) {
            $item = $this->adapter->_escapeIdentifiers($item);
        }
        return $item . $alias;
    }

    /**
     * Track Aliases
     *
     * Used to track SQL statements written with aliased tables.
     *
     * @param string $table the table to inspect
     * 
     * @return string
     */
    private function _trackAliases($table)
    {
        if (is_array($table)) {
            foreach ($table as $t) {
                $this->_trackAliases($t);
            }
            return;
        }
        // Does the string contain a comma?  If so, we need to separate
        // the string into discreet statements
        if (strpos($table, ',') !== false) {
            return $this->_trackAliases(explode(',', $table));
        }
        // if a table alias is used we can recognize it by a space
        if (strpos($table, " ") !== false) {
            // if the alias is written with the AS keyword, remove it
            $table = preg_replace('/ AS /i', ' ', $table);

            // Grab the alias
            $table = trim(strrchr($table, " "));

            // Store the alias, if it doesn't already exist
            if (!in_array($table, $this->arAliasedTables)) {
                $this->arAliasedTables[] = $table;
            }
        }
    }

    /**
     * DISTINCT
     *
     * Sets a flag which tells the query string compiler to add DISTINCT
     *
     * @param boolean $val value
     * 
     * @return object
     */
    public function distinct($val = true)
    {
        $this->arDistinct = (is_bool($val)) ? $val : true;
        return $this;
    }

    /**
     * Delete
     *
     * Compiles a delete string and runs the query
     *
     * @param mixed   $table      the table(s) to delete from. String or array
     * @param mixed   $where      the where clause
     * @param mixed   $limit      the limit clause
     * @param boolean $reset_data feature
     * 
     * @return object
     */
    public function delete($table = '', $where = '', $limit = null, $reset_data = true)
    {
        $this->_mergeCache();   // Combine any cached components with the current statements
        if ($table == '') {
            $table = $this->arFrom[0];
        } else {
            $table = $this->_protectIdentifiers($table, true, null, false);
        }
        if ($where != '') {
            $this->where($where);
        }
        if ($limit != null) {
            $this->limit($limit);
        }
        if (sizeof($this->arWhere) == 0 AND sizeof($this->arWherein) == 0 AND sizeof($this->arLike) == 0) {
            throw new Exception('Deletes are not allowed unless they contain a "where" or "like" clause.');
        }
        $this->from($table); // set tablename for set() function.
        $sql = $this->adapter->_delete($table, $this->arWhere, $this->arLike, $this->arLimit);

        if ($reset_data) {
            $this->_resetWrite();
        }
        return $this->adapter->exec($sql); // return number of  affected rows
    }

    /**
     * The "set" function.  Allows key / value pairs to be set for inserting or updating
     *
     * @param mixed   $key    array or string
     * @param string  $value  update string
     * @param boolean $escape whether to escape
     * 
     * @return   void
     */
    public function set($key, $value = '', $escape = true)
    {
        if ( ! is_array($key)) {
            $key = array($key => $value);
        }
        foreach ($key as $k => $v) {
            if ($escape === false) {
                if (is_string($v)) {
                    $v = "{$v}";  // PDO changes.
                }
                $this->arSet[$this->_protectIdentifiers($k)] = $v;
            } else {
                $this->arSet[$this->_protectIdentifiers($k)] = $this->adapter->escape($v);
            }
        }
        return $this;
    }

    /**
     * Replace
     *
     * Compiles an replace into string and runs the query
     *
     * @param string $table the table to replace data into
     * @param array  $set   an associative array of insert values
     * 
     * @return  object
     */
    public function replace($table = '', $set = null)
    {
        if ($table == '') {
            $table = $this->arFrom[0];
        } else {
            $this->from($table); // set tablename for set() function.
        }
        if ( ! is_null($set)) {
            $this->set($set);
        }
        $sql = $this->adapter->_replace($this->_protectIdentifiers($table, true, null, false), array_keys($this->arSet), array_values($this->arSet));
        $this->_resetWrite();
        return $this->adapter->exec($sql);
    }

    /**
     * Update
     *
     * Compiles an update string and runs the query
     *
     * @param string $table   the table to retrieve the results from
     * @param array  $set     an associative array of update values
     * @param array  $options update options
     * 
     * @return   PDO exec number of affected rows
     */
    public function update($table = '', $set = null, $options = array())
    {
        $options = array();     // Update options.
        if ($table == '') {     // Set table
            $table = $this->arFrom[0];
        } else {
            $this->from($table); // set tablename for set() function.
        }
        $this->_mergeCache();  // Combine any cached components with the current statements

        if ( ! is_null($set)) {
            $this->set($set);
        }
        $sql = $this->adapter->_update($this->_protectIdentifiers($table, true, null, false), $this->arSet, $this->arWhere, $this->arOrderBy, $this->arLimit);
        $this->_resetWrite();
        
        return $this->adapter->exec($sql);  // return number of affected rows.  
    }

    /**
     * Insert
     *
     * Compiles an insert string and runs the query
     *
     * @param string $table   the table to retrieve the results from
     * @param array  $set     an associative array of insert values
     * @param array  $options insert options
     * 
     * @return PDO exec number of affected rows.
     */
    public function insert($table = '', $set = null, $options = array())
    {
        $options = array();
        if ($table == '') {  // Set table
            $table = $this->arFrom[0];
        } else {
            $this->from($table); // set tablename correctly.
        }
        if ( ! is_null($set)) {
            $this->set($set);
        }
        $sql = $this->adapter->_insert($this->_protectIdentifiers($table, true, null, false), array_keys($this->arSet), array_values($this->arSet));
        $this->_resetWrite();

        return $this->adapter->exec($sql);  // return affected rows ( PDO support )
    }

    /**
     * Start Cache
     *
     * Starts AR caching
     *
     * @return    void
     */
    public function startCache()
    {
        $this->arCaching = true;
    }

    /**
     * Stop Cache
     *
     * Stops AR caching
     *
     * @return    void
     */
    public function stopCache()
    {
        $this->arCaching = false;
    }

    /**
     * Flush Cache
     *
     * Empties the AR cache
     *
     * @return    void
     */
    public function flushCache()
    {
        $this->_resetRun(
            array(
                'arCacheSelect'  => array(),
                'arCacheFrom'    => array(),
                'arCacheJoin'    => array(),
                'arCacheWhere'   => array(),
                'arCacheLike'    => array(),
                'arCacheGroupBy' => array(),
                'arCacheHaving'  => array(),
                'arCacheOrderBy' => array(),
                'arCacheSet'     => array(),
                'arCacheExists'  => array()
            )
        );
    }

    /**
     * Merge Cache
     *
     * When called, this function merges any cached AR arrays with 
     * locally called ones.
     *
     * @return    void
     */
    private function _mergeCache()
    {
        if (count($this->arCacheExists) == 0) {
            return;
        }
        foreach ($this->arCacheExists as $val) {
            $arVariable = 'ar' . $val;
            $arCacheVar = 'arCache' . $val;
            if (count($this->$arCacheVar) == 0) {
                continue;
            }
            $this->$arVariable = array_unique(array_merge($this->$arCacheVar, $this->$arVariable));
        }
    }

}

// END Query
/* End of file Query.php

/* Location: .Obullo/Database/Pdo/QueryBuilder.php */