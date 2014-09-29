<?php

namespace Obullo\Mongo;

use LogicException;

/**
 * Mongo Db "CRUD" Class
 *
 * Borrowed from www.alexbilbie.com | alex@alexbilbie.com ( Original Library )
 *
 * @category  Crud
 * @package   Mongo
 * @author    Obullo Framework <obulloframework@gmail.com>
 * @copyright 2009-2014 Obullo
 * @license   http://www.gnu.org/licenses/gpl-3.0.html GPL Licence
 * @link      http://alexbilbie.com
 * @link      http://obullo.com/docs/mongo
 */
Class Db {

    public $db;
    public $connection = null;
    public $connection_string = '';

    public $host;
    public $port;
    public $user;
    public $pass;
    public $dbname;
    public $config_options = array();

    public $selects  = array();
    public $wheres   = array(); // Public to make debugging easier
    public $sorts    = array();
    public $updates  = array(); // Public to make debugging easier
    public $groupBy  = array(); // Public to make debugging easier
    public $limit = 999999;
    public $offset = 0;
    public $insert_id = ''; // Last inserted id.
    
    public $collection = ''; // Set collection name using $this->db->from() ?
    public $mongoId = true; // Use or not use mongoid object
    public $resultObject;  // database result object
    public $operation;    // set operation type for latest query

    /**
    * Constructor
    * 
    * Automatically check if the Mongo PECL extension has been installed/enabled.
    * Generate the connection string and establish a connection to the MongoDB.
    *
    * @param string $db overrides to default mongo configuration
    * 
    * @throws Exception 
    */
    public function __construct($c, $db = 'db')
    {
        if ( ! class_exists('MongoClient', false)) {
            throw new RuntTimeException('The MongoDB PECL extension has not been installed or enabled.');
        }
        $this->host           = $c->load('config')['nosql']['mongo'][$db]['host'];
        $this->username       = $c->load('config')['nosql']['mongo'][$db]['username'];
        $this->password       = $c->load('config')['nosql']['mongo'][$db]['password'];
        $this->port           = $c->load('config')['nosql']['mongo'][$db]['port'];
        $this->dbname         = $db;
        $this->config_options = isset($c->load('config')['nosql']['mongo'][$db]['options']) ? $c->load('config')['nosql']['mongo'][$db]['options'] : array();
        $this->setConnectionString(); // Build the connection string from the config file
        $this->connect();
    }
    
    /**
     * [__call description]
     * @param  [type] $method    [description]
     * @param  [type] $arguments [description]
     * @return [type]            [description]
     */
    public function __call($method, $arguments)
    {
        
        if ( ! method_exists($this, $method)) {
            return call_user_func_array(array($this->resultObject, $method), $arguments);
        }
    }

    /**
     * Mongo id switch
     *
     * @param  boolean $bool On / Off
     * @return void
     */
    public function useMongoid($bool = true)
    {
        $this->mongoId = $bool;
    }
    
    /**
     * Determine which fields to include OR which to exclude during the query process.
     * Currently, including and excluding at the same time is not available, so the
     * $includes array will take precedence over the $excludes array.
     * 
     * If you want to only choose fields to exclude, leave $includes an empty array().
     * 
     * @usage: $this->db->select('foo,bar'))->get('foobar');
     * 
     * @param mixed $includes
     * @param array $excludes
     * @return type 
     */
    public function select($includes = '')
    {
        $includes = explode(',', $includes);

        if ( ! is_array($includes))
        {
            $includes = array($includes);
        }

        $includes = array_map('trim', $includes);  // trim spaces

        if ( ! empty($includes))
        {
            foreach ($includes as $col)
            {
                $this->selects[$col] = 1;
            }
        }

        return ($this);
    }

    /**
     * Set a collection.
     * 
     * @param string $collection 
     * @return object
     */
    public function from($collection = '')
    {
        $this->collection = $collection;
        return ($this);
    }
    
    /**
     * Get the documents based on these search parameters. The $wheres array can
     * be an associative array with the field as the key and the value as the search
     * criteria.
     * 
     * @usage : $this->db->where('foo','bar'))->get('foobar');
     * @usage : $this->db->where('foo >', 20)->get('foobar');
     * @usage : $this->db->where('foo <', 20)->get('foobar');
     * @usage : $this->db->where('foo >=', 20)->get('foobar');
     * @usage : $this->db->where('foo <=', 20)->get('foobar');
     * @usage : $this->db->where('foo !=', 20)->get('foobar');
     * 
     * @usage : $this->db->where('foo <', 10)->where('foo >', 25)->get('foobar');
     * @usage : $this->db->where('foo <=', 10)->where('foo >=', 25)->get('foobar');
     * 
     * @param string $wheres
     * @param string $value
     * @return object
     */
    public function where($wheres, $value = null, $mongo_id = true)
    {
        if(is_string($wheres) AND strpos(ltrim($wheres), ' ') > 0)
        {
            $array    = explode(' ', $wheres);
            $field    = $array[0];
            $criteria = $array[1];
          
            $this->_whereInit($field);
            
            switch ($criteria)
            {
                case '>':    // greater than
                    $this->wheres[$field]['$gt']  = $value;
                    break;
                
                case '<':    // less than
                    $this->wheres[$field]['$lt']  = $value;
                    break;
                
                case '>=':   // greater than or equal to
                    $this->wheres[$field]['$gte'] = $value;
                    break;
                
                case '<=':   // less than or equal to
                    $this->wheres[$field]['$lte'] = $value;
                    break;
                
                case '!=':   // not equal to
                    $this->wheres[$field]['$ne']  = $this->isMongoId($field, $value);
                    break;
                
                default:
                    break;
            }
              
            return ($this);
        }
      
        if (is_array($wheres))
        {
            foreach ($wheres as $wh => $val)
            {
                $this->wheres[$wh] = $this->isMongoId($wh, $val);
            }
        }
        else
        {
            $this->wheres[$wheres] = $this->isMongoId($wheres, $value);   
        }
       
        return ($this);
    }

    /**
     * Get the documents where the value of a $field may be something else
     * 
     * @usage : $this->db->orWhere('foo','bar')->get('foobar');
     * 
     * @param string $wheres
     * @param string $value
     * @return object
     */
    public function orWhere($wheres, $value = null)
    {
        if (is_array($value))
        {
            foreach ($value as $wh => $val)
            {
                $this->wheres['$or'][][$wh] = $this->isMongoId($wh, $val);
            }
        }
        else
        {
            $this->wheres['$or'][][$wheres] = $this->isMongoId($wheres, $value);
        }
        
        return ($this);
    }

    /**
     * Get the documents where the value of a $field is in a given $in array().
     * 
     * @usage : $this->db->whereIn('foo', array('bar', 'zoo', 'blah'))->get('foobar');
     * @usage : $this->db->whereIn('foo !=', array('bar', 'zoo', 'blah'))->get('foobar');
     * 
     * @param string $field
     * @param array $in
     * @return object
     */
    public function whereIn($field = '', $in = array())
    {
        if(strpos($field, '!=') > 0)
        {
            $array = explode('!=', $field);
            $field = trim($array[0]);
            
            $this->_whereInit($field);
            $this->wheres[$field]['$nin'] = $in;
            
            return ($this);
        }
        
        $this->_whereInit($field);
        $this->wheres[$field]['$in'] = $in;
        
        return ($this);
    }

    /**
     * Get the documents where the value of a $field is in all of a given $in array().
     * 
     * @usage : $this->db->whereInAll('foo', array('bar', 'zoo', 'blah'))->get('foobar');
     * 
     * @param type $field
     * @param type $in
     * @return \Db
     */
    public function whereInAll($field = '', $in = array())
    {
        $this->_whereInit($field);
        $this->wheres[$field]['$all'] = $in;
        
        return ($this);
    }
    
     /**
     *
     *  Get the documents where the (string) value of a $field is like a value. The defaults
     *  allow for a case-insensitive search.
     *
     *  @param $flags
     *  Allows for the typical regular expression flags:
     *      i = case insensitive
     *      m = multiline
     *      x = can contain comments
     *      l = locale
     *      s = dotall, "." matches everything, including newlines
     *      u = match unicode
     *
     *  @param $enable_start_wildcard
     *  If set to anything other than true, a starting line character "^" will be prepended
     *  to the search value, representing only searching for a value at the start of
     *  a new line.
     *
     *  @param $enable_end_wildcard
     *  If set to anything other than true, an ending line character "$" will be appended
     *  to the search value, representing only searching for a value at the end of
     *  a line.
     *
     *  @usage : $this->db->like('foo', 'bar', 'im', false, true);
     *
     *  @return object
     */
    public function like($field = "", $value = "", $flags = "i", $enable_start_wildcard = true, $enable_end_wildcard = true)
    {
        $field = (string) trim($field);

        $this->_whereInit($field);

        $value = (string) trim($value);

        $value = quotemeta($value);

        if ($enable_start_wildcard !== true)
        {
            $value = "^" . $value;
        }
        
        if ($enable_end_wildcard !== true)
        {
            $value .= "$";
        }
        
        $regex = "/$value/$flags";
        $this->wheres[$field] = new MongoRegex($regex);
        
        return $this;
    }

    /**
     * The same as the aboce but multiple instances are joined by OR:
     * 
     * @param  string $field
     * @param  array  $like
     * @return object
     */
    public function orLike($field, $like = array())
    {
        $this->_whereInit('$or');

        if (is_array($like) AND count($like) > 0)
        {
            foreach ($like as $admitted)
            {
                $this->wheres['$or'][] = array($field => new MongoRegex("/$admitted/"));
            }
        }

        return $this;
    }
    
    /**
     * The same as the above but multiple instances are joined by NOT LIKE:
     * 
     * @param  string $field
     * @param  array  $like
     * @return object
     */
    public function notLike($field, $like = array())
    {
        $this->_whereInit($field);

        if (is_array($like) AND count($like) > 0)
        {
            foreach ($like as $admitted)
            {
                $this->wheres[$field]['$nin'][] = new MongoRegex("/$admitted/");
            }
        }

        return $this;
    }

    /**
     * @usage : $this->db->orderBy('foo', 'ASC'))->get('foobar');
     * 
     * @param  string $col fieldname
     * @param  string $direction ASC, DESC
     * @return object
     */
    public function orderBy($col, $direction  = 'ASC')
    {
        if (strtolower($direction) == 'desc') {
            $this->sorts[$col] = -1;
        } else {
            $this->sorts[$col] = 1;
        }
        return ($this);
    }
    
    /**
     * @usage : $this->db->groupBy('foo', array('count' => 0) ,'function (obj, prev) {}')->get('foobar');
     * 
     * @param  string $key group Initial value of the aggregation counter objectc
     * @param  string $initial Initial value of the aggregation counter objectc
     * @param  string $reduce  A function that takes two arguments (the current document and the aggregation to this point) and does the aggregation
     * @return object
     */
    public function groupBy($key = NULL , $initial = array('count' => 0) ,$reduce ='function (obj, prev) { prev.count++;}' )
    {
        if ($key != NULL) {
            $this->groupBy['keys'][$key] = true;
            $this->groupBy['initial']    = $initial;
            $this->groupBy['reduce']     = $reduce;
        }
        return ($this);
    }


    
    /**
     * Limit the result set to $x number of documents.
     * 
     * @usage : $this->db->limit($x);
     * 
     * @param integer $x limit
     * @return object
     */
    public function limit($x = 99999)
    {
        if ($x !== null AND is_numeric($x) && $x >= 1) {
            $this->limit = (int) $x;
        }
        return ($this);
    }

    /**
     * Offset the result set to skip $x number of documents.
     * 
     * @usage : $this->db->offset($x);
     * 
     * @param integer $x
     * @return object
     */
    public function offset($x = 0)
    {
        if ($x !== null AND is_numeric($x) AND $x >= 1) {
            $this->offset = (int) $x;
        }
        return ($this);
    }
    
    /**
     * Get the documents based upon the passed parameters.
     * 
     * @usage : $this->db->get('foo');
     * 
     * @param string $collection tablename
     * @return object Mongo Db Result Object
     * @throws Exception 
     */
    public function get($collection = '')
    {
        $this->operation = 'read';  // Set operation for lastQuery output.
      
        $collection = (empty($this->collection)) ? $collection :  $this->collection;
        
        if (empty($collection))
        {
            throw new LogicException('You need to set a collection name using $this->db->from(\'table\') method.');
        }
        
        return $this->_query($collection);
    }

    /**
     * Private function get query results
     * 
     * @return object
     */
    public function _query($collection)
    {
        $rows = array();
        if(count($this->groupBy) == 0) {
            $cursor = $this->db->{$collection}
                ->find($this->wheres, $this->selects)
                ->limit((int) $this->limit)
                ->skip((int) $this->offset)
                ->sort($this->sorts);
            while($row = $cursor->getNext()) {
                $rows[] = $row;
            }
        } else {
            if ($this->wheres) {
                $cond   = array( 'condition' => $this->wheres );
                $cursor = $this->db->{$collection}->group(
                                                        $this->groupBy['keys'],
                                                        $this->groupBy['initial'],
                                                        $this->groupBy['reduce'] ,
                                                        $cond
                                                    );
            } else {
                $cursor = $this->db->{$collection}->group(
                                                        $this->groupBy['keys'],
                                                        $this->groupBy['initial'],
                                                        $this->groupBy['reduce']
                                                    );   
            }
            $rows = $cursor['retval'];
        }
        $this->resultObject = new Results($rows); // Load db results
        $this->_resetSelect();  // Reset
        return $this->resultObject;
    }

    /**
     *  Perform an aggregation using the aggregation framework
     *  @link http://docs.mongodb.org/manual/aggregation/
     *  @link http://docs.mongodb.org/manual/reference/sql-aggregation-comparison/
     *  WHERE     $match
     *  GROUP BY  $group
     *  HAVING    $match
     *  SELECT    $project
     *  ORDER BY  $sort
     *  LIMIT     $limit
     *  SUM()     $sum
     *  COUNT()   $sum
     *  join      No direct corresponding operator; however, the $unwind operator allows for 
     *  somewhat similar functionality, but with fields embedded within the document.
     * 
     * @param string $tablename
     * @param array $pipeline
     * @param array $options
     * @return object Mongo db result object
     * @throws Exception
     */
    public function aggregate($collection, $pipeline, $options = null)
    {
        $rows = array();
        if (empty($collection)) {
            throw new LogicException('You need to set a collection name using $this->db->from(\'table\') method.');
        }
        if (is_array($options)) {
            $cursor = $this->db->{$collection}
                ->aggregate($pipeline, $options);
        } else {
            $cursor = $this->db->{$collection}
                ->aggregate($pipeline);
        }
        $this->collection = ''; // reset from.
        foreach ($cursor['result'] as $key => $value)  {
            $rows[$key] = $value;
        }
        $this->resultObject = new Results($rows); // Load db results
        return $this->resultObject;
    }
    
    /**
     * Insert a new document into the passed collection
     *
     * @usage : $this->db->insert('foo', $data = array());
     * 
     * @param string $collection
     * @param array $data
     * @return int affected rows | boolean(false)
     * @throws Exception 
     */
    public function insert($collection = '', $data = array(), $options = array())
    {
        $this->operation  = 'insert';  // Set operation for lastQuery output.
        $this->collection = $collection;

        if (empty($collection)) {
            throw new LogicException('No Mongo collection selected to insert into.');
        }
        if (count($data) == 0 OR ! is_array($data)) {
            throw new LogicException('Nothing to insert into Mongo collection or insert data is not an array.');
        }
        $this->db->{$collection}
            ->insert($data, 
                array_merge($this->config_options, $options));
        $this->_resetSelect();
        if (isset($data['_id'])) {
            $this->insert_id = $data['_id'];
            return sizeof(array_pop($data)); // affected rows.
        } else {
            return (false);
        }
    }

    /**
     * Batch Insert
     * 
     * Insert a multiple new document into the passed collection.
     * 
     * @usage : $this->db->batchInsert('foo', $data = array());
     * 
     * @param string $collection tablename
     * @param array $data data
     * @return boolean | string Insert Id
     * @throws Exception 
     */
    public function batchInsert($collection = '', $data = array(), $options = array())
    {
        $this->operation  = 'insert';  // Set operation for getLastQuery output.
        $this->collection = $collection; 

        if (empty($collection)) {
            throw new LogicException('No Mongo collection selected to insert operation.');
        }
        if (count($data) == 0 OR ! is_array($data)) {
            throw new LogicException('Nothing to insert into Mongo collection or insert data is not an array.');
        }
        $this->_resetSelect();
        return $this->db->{$collection}
            ->batchInsert($data, 
                array_merge($this->config_options, $options));
    }

    /**
     * Updates multiple document
     * 
     * @usage: $this->db->update('foo', $data = array());
     * 
     * @param string $collection
     * @param array $data
     * @param array $options
     * @return int affected rows
     * @throws Exception 
     */
    public function update($collection = '', $data = array(), $options = array())
    {
        $this->operation  = 'update';  // Set operation for getLastQuery output.
        $this->collection =  $collection;
        if (is_array($data) AND count($data) > 0) {
            $this->updates = array_merge($data, $this->updates);
        }
        if (count($this->updates) == 0) {
            throw new LogicException('Nothing to update in Mongo collection or update data is not an array.');
        }
        // Update Modifiers  http://www.mongodb.org/display/DOCS/Updating
        $mods = array(
            '$set' => '', 
            '$unset' => '',
            '$pop' => '',
            '$push' => '',
            '$pushAll' => '',
            '$pull' => '', 
            '$pullAll' => '',
            '$inc' => '',
            '$each' => '',
            '$addToSet' => '',
            '$rename' => '',
            '$bit' => '');
        
        $default_options = array_merge(array('multiple' => true), $this->config_options);  // Multiple update behavior like MYSQL.
        
        //  If any modifier used remove the default modifier ( $set ).
        //  
        $used_modifier = array_keys($this->updates);
        $modifier      = (isset($used_modifier[0])) ? $used_modifier[0] : null;
        
        if ($modifier != null AND isset($mods[$modifier])) {
            $updates = $this->updates;
            $default_options['multiple'] = false;
        } else {
            $updates = array('$set' => $this->updates); // default mod = $set
        }        
        $this->db->{$collection}
            ->update($this->wheres,
                $updates,
                array_merge($default_options, $options)
            );

        $this->_resetSelect();
        return $this->db->{$collection}
            ->find($updates)
            ->count();
    }
    

    
    /**
     * Increments the value of a field.
     * 
     * @usage: $this->db->where(blog_id, 123)->inc('num_comments', 1))->update('blog_posts');
     * 
     * @param string $fields
     * @param integer $value
     * @return object
     */
    public function inc($fields = '', $value = 0)
    {
        $this->_updateInit('$inc');
        if (is_string($fields)) {
            $this->updates['$inc'][$fields] = $value;
        } elseif (is_array($fields)) {
            foreach ($fields as $field => $value) {
                $this->updates['$inc'][$field] = $value;
            }
        }
        return ($this);
    }


    
    /**
     * Decrements the value of a field.
     * 
     * @usage: $this->db->where('blog_id', 123))->dec('num_comments', 1))->update('blog_posts');
     * 
     * @param string $fields
     * @param integer $value
     * @return object
     */
    public function dec($fields = '', $value = 0)
    {
        $this->_updateInit('$inc');
        if (is_string($fields)) {
            $this->updates['$inc'][$fields] = $value;
        } elseif (is_array($fields)) {
            foreach ($fields as $field => $value) {
                $this->updates['$inc'][$field] = $value;
            }
        }
        return ($this);
    }

    /**
     * Sets a field to a value.
     * 
     * @usage: $this->db->where('blog_id',123)->set('posted', 1)->update('blog_posts');
     * @usage: $this->db->where('blog_id',123)->set(array('posted' => 1, 'time' => time()))->update('blog_posts');
     * 
     * @param string $fields
     * @param string $value
     * @return object
     */
    public function set($fields, $value = null)
    {
        $this->_updateInit('$set');
        if (is_string($fields)) {
            $this->updates['$set'][$fields] = $value;
        }
        elseif (is_array($fields)) {
            foreach ($fields as $field => $value) {
                $this->updates['$set'][$field] = $value;
            }
        }
        return ($this);
    }
    
    /**
     * Unsets a field (or fields).
     * 
     * @usage: $this->db->where('blog_id', 123)->unsetField('posted')->update('blog_posts');
     * @usage: $this->db->where('blog_id', 123)->set('posted','time')->update('blog_posts');
     * 
     * @param string $fields
     * @return object
     */
    public function unsetField($fields)
    {
        $this->_updateInit('$unset');

        if (is_string($fields))
        {
            $this->updates['$unset'][$fields] = 1;
        }
        elseif (is_array($fields))
        {
            foreach ($fields as $field)
            {
                $this->updates['$unset'][$field] = 1;
            }
        }

        return ($this);
    }


    
    /**
     * Adds value to the array only if its not in the array already.
     * 
     * @usage: $this->db->where('blog_id', 123))->addToSet('tags', 'php')->update('blog_posts');
     * @usage: $this->db->where('blog_id', 123))->addToSet('tags', array('php', 'obullo', 'mongodb'))->update('blog_posts');
     * 
     * @param string $field
     * @param mixed $values
     * @return object
     */
    public function addToSet($field, $values)
    {
        $this->_updateInit('$addToSet');

        if (is_string($values))
        {
            $this->updates['$addToSet'][$field] = $values;
        }
        elseif (is_array($values))
        {
            $this->updates['$addToSet'][$field] = array('$each' => $values);
        }

        return ($this);
    }



    /**
     * Pushes values into a field (field must be an array).
     * 
     * @usage: $this->db->where('blog_id', 123)->push('comments', array('text'=>'Hello world'))->update('blog_posts');
     * @usage: $this->db->where('blog_id', 123)->push(array('comments' => array('text'=>'Hello world')), 'viewed_by' => array('Alex'))->update('blog_posts');
     * 
     * @param string $fields
     * @param mixed $value
     * @return object
     */
    public function push($fields, $value = '')
    {
        $this->_updateInit('$push');

        if (is_string($fields))
        {
            $this->updates['$push'][$fields] = $value;
        }
        elseif (is_array($fields))
        {
            foreach ($fields as $field => $value)
            {
                $this->updates['$push'][$field] = $value;
            }
        }

        return ($this);
    }
    


    /**
     * Pops the last value from a field (field must be an array).
     *  
     * @usage: $this->db->where('blog_id', 123))->pop('comments')->update('blog_posts');
     * @usage: $this->db->where('blog_id', 123))->pop(array('comments', 'viewed_by'))->update('blog_posts');
     * 
     * @param string $field
     * @return object
     */
    public function pop($field)
    {
        $this->_updateInit('$pop');

        if (is_string($field))
        {
            $this->updates['$pop'][$field] = -1;
        }
        elseif (is_array($field))
        {
            foreach ($field as $pop_field)
            {
                $this->updates['$pop'][$pop_field] = -1;
            }
        }

        return ($this);
    }
    
    /**
     * Removes by an array by the value of a field.
     * 
     * @usage: $this->db->pull('comments', array('comment_id'=>123))->update('blog_posts');
     * 
     * @param string $field
     * @param array $value
     * @return object
     */
    public function pull($field = '', $value = array())
    {
        $this->_updateInit('$pull');
        $this->updates['$pull'] = array($field => $value);
        return ($this);
    }

    /**
     * Delete all documents from the passed collection based upon certain criteria.
     * 
     * @usage : $this->db->delete('foo', $data = array());
     * 
     * @param string $collection
     * @return int affected rows.
     * @throws Exception 
     */
    public function delete($collection = '', $options = array())
    {
        $this->operation  = 'delete';  // Set operation for lastQuery output
        $this->collection = $collection;

        $default_options = array_merge(array('justOne' => false), $this->config_options);

        if (empty($collection))
        {
            throw new LogicException('No Mongo collection selected to delete.');
        }

        if (isset($this->wheres['_id']) AND ! ($this->wheres['_id'] instanceof MongoId))
        {
            $this->wheres['_id'] = new MongoId($this->wheres['_id']);
        }
        
        $this->db->{$collection}
        ->remove($this->wheres, 
            array_merge($default_options, $options)
        );

        $affected_rows = $this->db->{$collection}
                                ->find($this->wheres)
                                ->count();
        $this->_resetSelect();
        return $affected_rows;

    }

    /**
     * Establish a connection to MongoDB using the connection string generated in
     * the setConnectionString() method.  If 'mongo_persist_key' was set to true in the
     * config file, establish a persistent connection.
     * 
     * We allow for only the 'persist'
     * option to be set because we want to establish a connection immediately.
     * 
     * @return type
     * @throws Exception 
     */
    public function connect($driver = null, $options = null)
    {
        if ($this->db == null) {
            $this->connection = new \MongoClient($this->connection_string);
            $this->db         = $this->connection->{$this->dbname};
        }
        return ($this);
    }

    /**
     * Returns to Mongodb instance of object.
     * 
     * @return object
     */
    public function getInstance()
    {
        return $this->db;
    }
    
    /**
     * Build the connection string from the config file.
     * 
     * @throws Exception 
     */
    public function setConnectionString() 
    {
        // $config = getConfig('nosql');
        
        // if($config['dsn'] != '')
        // {
        //     $this->connection_string = $config['dsn'];
        //     return;
        // }
        
        // $this->host           = $config['host'];
        // $this->port           = $config['port'];
        // $this->user           = $config['username'];
        // $this->pass           = $config['password'];
        // $this->dbname         = $config['database'];
        // $this->config_options = $config['options'];
        
        if($this->dbname == '')
        {
            throw new LogicException('Please set a $mongo[\'database\'] from app/config/mongo.php.');
        }
        
        $connection_string = "mongodb://";

        if (empty($this->host))
        {
            throw new LogicException('You need to specify a hostname connect to MongoDB.');
        }

        if (empty($this->dbname))
        {
            throw new LogicException('You need to specify a database name connect to MongoDB.');
        }

        if ( ! empty($this->user) AND ! empty($this->pass))
        {
            $connection_string .= "{$this->user}:{$this->pass}@";
        }

        if (isset($this->port) AND ! empty($this->port))
        {
            $connection_string .= "{$this->host}:{$this->port}";
        }
        else
        {
            $connection_string .= "{$this->host}";
        }

        $this->connection_string = trim($connection_string).'/'.$this->dbname;
    }

    /**
     *  Resets the class variables to default settings
     */
    public function _resetSelect()
    {
        // $this->_setLastQuery(); //  Build lastest sql query.

        $this->selects    = array();
        $this->updates    = array();
        $this->groupBy      = array();
        $this->wheres     = array();
        $this->limit      = 999999;
        $this->offset     = 0;
        $this->sorts      = array();
        $this->find       = false;
        $this->collection = '';
        $this->updateData = array();
        $this->operation  = null;
    }

    /**
     * Prepares parameters for insertion in $wheres array().
     * 
     * @param mixed $param key
     *
     * @return void
     */
    private function _whereInit($param)
    {
        if ( ! isset($this->wheres[$param])) {
            $this->wheres[$param] = array();
        }
    }
    
    /**
     * Prepares parameters for insertion in $updates array().
     * 
     * @param string $method method
     *
     * @return void
     */
    private function _updateInit($method)
    {
        $this->operation = 'update';  // Set operation for lastQuery output.
        if ( ! isset($this->updates[$method])) {
            $this->updates[$method] = array();
        }
    }
    
    /**
     * Get last inserted Mongo id.
     * 
     * @return string
     */
    public function insertId()
    {
        return $this->insert_id;
    }
    
    /**
     * Auto add mongo id if "_id" used  .
     * 
     * @param string $string key
     * @param mixed  $value  value
     * 
     * @return \MongoId 
     */
    public function isMongoId($string = '', $value = '')
    {
        if ($this->mongoId) {
            if ($string == '_id' AND ! is_object($value)) {
                return new \MongoId($value);
            }
        }
        return $value;
    }

    /**
     * Get last occurence error
     * 
     * @return mixed
     */
    public function lastError()
    { 
        return $this->db->lastError();
    }

    /**
     * Close the connection.
     */
    public function __destruct()
    {
        if (is_object($this->connection)) {
            $this->connection->close();
        } 
    }
    
}

// END Db Class

/* End of file Db.php */
/* Location: .Obullo/Mongo/Db.php */