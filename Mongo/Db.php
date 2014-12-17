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
Class Db
{
    /**
     * Container
     * 
     * @var object
     */
    protected $c;

    /**
     * MongoDb instance
     * 
     * @var object
     */
    public $db;

    /**
     * Mongo Connection
     * 
     * @var object
     */
    public $connection = null;

    /**
     * Connection
     * 
     * @var string
     */
    public $connectionString = '';

    /**
     * Hostname
     * 
     * @var string
     */
    public $host = '';

    /**
     * Db connection port
     * 
     * @var integer
     */
    public $port = 0;

    /**
     * Db connection username
     * 
     * @var string
     */
    public $user = '';

    /**
     * Db connection password
     * 
     * @var string
     */
    public $pass = '';

    /**
     * Database name
     * 
     * @var string
     */
    public $dbname = '';

    /**
     * Config options
     * 
     * @var array
     */
    public $configOptions = array();

    /**
     * Selects
     * 
     * @var array
     */
    public $selects  = array();

    /**
     * Wheres
     * Public to make debugging easier
     * 
     * @var array
     */
    public $wheres   = array();

    /**
     * Sorts
     * 
     * @var array
     */
    public $sorts    = array();

    /**
     * Updates
     * Public to make debugging easier
     * 
     * @var array
     */
    public $updates  = array();

    /**
     * Group by 
     * Public to make debugging easier
     * 
     * @var array
     */
    public $groupBy  = array();

    /**
     * Limit
     * 
     * @var integer
     */
    public $limit = 999999;

    /**
     * Offset
     * 
     * @var integer
     */
    public $offset = 0;

    /**
     * Last inserted id.
     * 
     * @var string
     */
    public $insertId = '';
    
    /**
     * Set collection name using $this->db->from() ?
     * 
     * @var string
     */
    public $collection = ''; 

    /**
     * Use or not use mongoid object
     * 
     * @var boolean
     */
    public $mongoId = true;

    /**
     * Database result object
     * 
     * @var object
     */
    public $resultObject = null;

    /**
     * Set operation type for latest query
     * 
     * @var string
     */
    public $operation = '';

    /**
    * Constructor
    * 
    * Automatically check if the Mongo PECL extension has been installed/enabled.
    * Generate the connection string and establish a connection to the MongoDB.
    *
    * @param object $c  container
    * @param string $db overrides to default mongo configuration
    * 
    * @throws Exception 
    */
    public function __construct($c, $db = 'db')
    {
        if ( ! class_exists('MongoClient', false)) {
            throw new RuntTimeException('The MongoDB PECL extension has not been installed or enabled.');
        }

        echo '<pre>';
        var_dump($db);
        var_dump($c->load('config')['nosql']['mongo'][$db]);
        die('die');
        $this->host          = $c->load('config')['nosql']['mongo'][$db]['host'];
        $this->username      = $c->load('config')['nosql']['mongo'][$db]['username'];
        $this->password      = $c->load('config')['nosql']['mongo'][$db]['password'];
        $this->port          = $c->load('config')['nosql']['mongo'][$db]['port'];
        $this->dbname        = $db;
        $this->configOptions = isset($c->load('config')['nosql']['mongo'][$db]['options']) ? $c->load('config')['nosql']['mongo'][$db]['options'] : array();
        $this->setConnectionString(); // Build the connection string from the config file
        $this->connect();
    }
    
    /**
     * Call method
     * 
     * @param string $method    method name
     * @param array  $arguments arguments
     * 
     * @return array
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
     * @param boolean $bool On / Off
     * 
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
     * @param mixed $includes includes
     * 
     * @uses $this->db->select('foo,bar'))->get('foobar');
     * 
     * @return type 
     */
    public function select($includes = '')
    {
        $includes = explode(',', $includes);

        if ( ! is_array($includes)) {
            $includes = array($includes);
        }
        $includes = array_map('trim', $includes);  // trim spaces

        if ( ! empty($includes)) {
            foreach ($includes as $col) {
                $this->selects[$col] = 1;
            }
        }
        return ($this);
    }

    /**
     * Set a collection.
     * 
     * @param string $collection collection
     * 
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
     * @param string $wheres wheres
     * @param string $value  values
     * 
     * @uses : $this->db->where('foo','bar'))->get('foobar');
     * @uses : $this->db->where('foo >', 20)->get('foobar');
     * @uses : $this->db->where('foo <', 20)->get('foobar');
     * @uses : $this->db->where('foo >=', 20)->get('foobar');
     * @uses : $this->db->where('foo <=', 20)->get('foobar');
     * @uses : $this->db->where('foo !=', 20)->get('foobar');
     * 
     * @uses : $this->db->where('foo <', 10)->where('foo >', 25)->get('foobar');
     * @uses : $this->db->where('foo <=', 10)->where('foo >=', 25)->get('foobar');
     * 
     * 
     * @return object
     */
    public function where($wheres, $value = null)
    {
        if (is_string($wheres) AND strpos(ltrim($wheres), ' ') > 0) {

            $array    = explode(' ', $wheres);
            $field    = $array[0];
            $criteria = $array[1];
          
            $this->whereInit($field);
            
            switch ($criteria) {
            case '>': // greater than
                $this->wheres[$field]['$gt']  = $value;
                break;
            case '<': // less than
                $this->wheres[$field]['$lt']  = $value;
                break;
            case '>=': // greater than or equal to
                $this->wheres[$field]['$gte'] = $value;
                break;
            case '<=': // less than or equal to
                $this->wheres[$field]['$lte'] = $value;
                break;
            case '!=': // not equal to
                $this->wheres[$field]['$ne']  = $this->isMongoId($field, $value);
                break;
            default:
                break;
            } 
            return ($this);
        }
        if (is_array($wheres)) {
            foreach ($wheres as $wh => $val) {
                $this->wheres[$wh] = $this->isMongoId($wh, $val);
            }
        } else {
            $this->wheres[$wheres] = $this->isMongoId($wheres, $value);   
        }
        return ($this);
    }

    /**
     * Get the documents where the value of a $field may be something else
     * 
     * @param string $wheres wheres
     * @param string $value  value
     * 
     * @uses : $this->db->orWhere('foo','bar')->get('foobar');
     * 
     * @return object
     */
    public function orWhere($wheres, $value = null)
    {
        if (is_array($value)) {
            foreach ($value as $wh => $val) {
                $this->wheres['$or'][][$wh] = $this->isMongoId($wh, $val);
            }
        } else {
            $this->wheres['$or'][][$wheres] = $this->isMongoId($wheres, $value);
        }
        return ($this);
    }

    /**
     * Get the documents where the value of a $field is in a given $in array().
     * 
     * @param string $field field
     * @param array  $in    in
     * 
     * @uses : $this->db->whereIn('foo', array('bar', 'zoo', 'blah'))->get('foobar');
     * @uses : $this->db->whereIn('foo !=', array('bar', 'zoo', 'blah'))->get('foobar');
     * 
     * @return object
     */
    public function whereIn($field = '', $in = array())
    {
        if (strpos($field, '!=') > 0) {
            $array = explode('!=', $field);
            $field = trim($array[0]);
            $this->whereInit($field);
            $this->wheres[$field]['$nin'] = $in;
            return ($this);
        }
        $this->whereInit($field);
        $this->wheres[$field]['$in'] = $in;
        
        return ($this);
    }

    /**
     * Get the documents where the value of a $field is in all of a given $in array().
     * 
     * @param type $field field name
     * @param type $in    in
     * 
     * @return \Db
     * @example $this->db->whereInAll('foo', array('bar', 'zoo', 'blah'))->get('foobar');
     */
    public function whereInAll($field = '', $in = array())
    {
        $this->whereInit($field);
        $this->wheres[$field]['$all'] = $in;
        
        return ($this);
    }
    
    /**
     * Get the documents where the (string) value of a $field is like a value.
     * The defaults allow for a case-insensitive search.
     * 
     * @param string  $field               field name
     * @param string  $value               value
     * @param string  $flags               Allows for the typical regular expression flags:
     * @param boolean $enableStartWildcard enable start wild card
     * @param boolean $enableEndWildCard   enable end wild card
     * 
     * @var $flags
     *      i = case insensitive
     *      m = multiline
     *      x = can contain comments
     *      l = locale
     *      s = dotall, "." matches everything, including newlines
     *      u = match unicode
     *  
     * @var $enableStartWildcard
     *  If set to anything other than true, a starting line character "^" will be prepended
     *  to the search value, representing only searching for a value at the start of a new line.
     *  
     * @var $enableEndWildCard
     *  If set to anything other than true, an ending line character "$" will be appended
     *  to the search value, representing only searching for a value at the end of a line.
     *
     * @return object
     * @example $this->db->like('foo', 'bar', 'im', false, true);
     */
    public function like($field = "", $value = "", $flags = "i", $enableStartWildcard = true, $enableEndWildCard = true)
    {
        $field = (string) trim($field);

        $this->whereInit($field);

        $value = (string) trim($value);
        $value = quotemeta($value);

        if ($enableStartWildcard !== true) {
            $value = "^" . $value;
        }
        if ($enableEndWildCard !== true) {
            $value .= "$";
        }
        $regex = "/$value/$flags";
        $this->wheres[$field] = new MongoRegex($regex);
        
        return $this;
    }

    /**
     * The same as the aboce but multiple instances are joined by OR:
     * 
     * @param string $field field name
     * @param array  $like  like
     * 
     * @return object
     */
    public function orLike($field, $like = array())
    {
        $this->whereInit('$or');

        if (is_array($like) AND count($like) > 0) {
            foreach ($like as $admitted) {
                $this->wheres['$or'][] = array($field => new MongoRegex("/$admitted/"));
            }
        }
        return $this;
    }
    
    /**
     * The same as the above but multiple instances are joined by NOT LIKE:
     * 
     * @param string $field field name
     * @param array  $like  like
     * 
     * @return object
     */
    public function notLike($field, $like = array())
    {
        $this->whereInit($field);

        if (is_array($like) AND count($like) > 0) {
            foreach ($like as $admitted) {
                $this->wheres[$field]['$nin'][] = new MongoRegex("/$admitted/");
            }
        }
        return $this;
    }

    /**
     * Order by column name
     * 
     * @param string $col       fieldname
     * @param string $direction ASC, DESC
     * 
     * @return object
     * @example $this->db->orderBy('foo', 'ASC'))->get('foobar');
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
     * Group by
     * 
     * @param string $key     group Initial value of the aggregation counter objectc
     * @param string $initial Initial value of the aggregation counter objectc
     * @param string $reduce  A function that takes two arguments (the current document and the aggregation to this point) and does the aggregation
     * 
     * @return object
     * @example $this->db->groupBy('foo', array('count' => 0) ,'function (obj, prev) {}')->get('foobar');
     */
    public function groupBy($key = null , $initial = array('count' => 0) ,$reduce ='function (obj, prev) { prev.count++;}' )
    {
        if ($key != null) {
            $this->groupBy['keys'][$key] = true;
            $this->groupBy['initial']    = $initial;
            $this->groupBy['reduce']     = $reduce;
        }
        return ($this);
    }

    /**
     * Limit the result set to $x number of documents.
     * 
     * @param integer $x limit
     * 
     * @return object
     * @example $this->db->limit($x);
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
     * @param integer $x limit
     * 
     * @return object
     * @example $this->db->offset($x);
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
     * @param string $collection tablename
     * 
     * @return object Mongo Db Result Object
     * @example $this->db->get('foo');
     * @throws Exception 
     */
    public function get($collection = '')
    {
        $this->operation = 'read';  // Set operation for lastQuery output.
      
        $collection = (empty($this->collection)) ? $collection :  $this->collection;
        
        if (empty($collection)) {
            throw new LogicException('You need to set a collection name using $this->db->from(\'table\') method.');
        }
        return $this->query($collection);
    }

    /**
     * Private function get query results
     * 
     * @param string $collection collection name
     * 
     * @return object
     */
    protected function query($collection)
    {
        $rows = array();
        if (count($this->groupBy) == 0) {
            $cursor = $this->db->{$collection}
                ->find($this->wheres, $this->selects)
                ->limit((int) $this->limit)
                ->skip((int) $this->offset)
                ->sort($this->sorts);
            while ($row = $cursor->getNext()) {
                $rows[] = $row;
            }
        } else {
            if ($this->wheres) {
                $cond   = array( 'condition' => $this->wheres );
                $cursor = $this->db->{$collection}->group(
                    $this->groupBy['keys'],
                    $this->groupBy['initial'],
                    $this->groupBy['reduce'],
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
        $this->resetSelect();  // Reset
        return $this->resultObject;
    }

    /**
     *  Perform an aggregation using the aggregation framework
     * 
     * @param string $collection collection
     * @param array  $pipeline   pipeline
     * @param array  $options    options
     * 
     * @link http://docs.mongodb.org/manual/aggregation/
     * @link http://docs.mongodb.org/manual/reference/sql-aggregation-comparison/
     *    WHERE     $match
     *    GROUP BY  $group
     *    HAVING    $match
     *    SELECT    $project
     *    ORDER BY  $sort
     *    LIMIT     $limit
     *    SUM()     $sum
     *    COUNT()   $sum
     *    join      No direct corresponding operator; however, the $unwind operator allows for 
     *              somewhat similar functionality, but with fields embedded within the document.
     * 
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
        foreach ($cursor['result'] as $key => $value) {
            $rows[$key] = $value;
        }
        $this->resultObject = new Results($rows); // Load db results
        return $this->resultObject;
    }
    
    /**
     * Insert a new document into the passed collection
     *
     * @param string $collection collection
     * @param array  $data       data
     * @param array  $options    options
     * 
     * @return int affected rows | boolean(false)
     * @example $this->db->insert('foo', $data = array());
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
        $this->db->{$collection}->insert($data, array_merge($this->configOptions, $options));

        $this->resetSelect();
        if (isset($data['_id'])) {
            $this->insertId = $data['_id'];
            return sizeof(array_pop($data)); // affected rows.
        } else {
            return (false);
        }
    }

    /**
     * Batch Insert
     * Insert a multiple new document into the passed collection.
     * 
     * @param string $collection tablename
     * @param array  $data       data
     * @param array  $options    options
     * 
     * @return boolean | string Insert Id
     * @example $this->db->batchInsert('foo', $data = array());
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
        $this->resetSelect();

        return $this->db->{$collection}->batchInsert($data, array_merge($this->configOptions, $options));
    }

    /**
     * Updates multiple document
     * 
     * @param string $collection collection
     * @param array  $data       data
     * @param array  $options    options
     * 
     * @return int affected rows
     * @example $this->db->update('foo', $data = array());
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
            '$set'      => '', 
            '$unset'    => '',
            '$pop'      => '',
            '$push'     => '',
            '$pushAll'  => '',
            '$pull'     => '', 
            '$pullAll'  => '',
            '$inc'      => '',
            '$each'     => '',
            '$addToSet' => '',
            '$rename'   => '',
            '$bit'      => ''
        );
        
        $defaultOptions = array_merge(array('multiple' => true), $this->configOptions);  // Multiple update behavior like MYSQL.
        
        // If any modifier used remove the default modifier ( $set ).
        $usedModifier = array_keys($this->updates);
        $modifier      = (isset($usedModifier[0])) ? $usedModifier[0] : null;
        
        if ($modifier != null AND isset($mods[$modifier])) {
            $updates = $this->updates;
            $defaultOptions['multiple'] = false;
        } else {
            $updates = array('$set' => $this->updates); // default mod = $set
        }        
        $this->db->{$collection}->update($this->wheres, $updates, array_merge($defaultOptions, $options));

        $this->resetSelect();
    }
    
    /**
     * Increments the value of a field.
     * 
     * @param string  $fields fields
     * @param integer $value  value
     * 
     * @return object
     * @example $this->db->where(blog_id, 123)->inc('num_comments', 1))->update('blog_posts');
     */
    public function inc($fields = '', $value = 0)
    {
        $this->updateInit('$inc');
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
     * @param string  $fields fields
     * @param integer $value  value
     * 
     * @return object
     * @example $this->db->where('blog_id', 123))->dec('num_comments', 1))->update('blog_posts');
     */
    public function dec($fields = '', $value = 0)
    {
        $this->updateInit('$inc');

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
     * @param string $fields fields
     * @param string $value  value
     * 
     * @return object
     * @example $this->db->where('blog_id',123)->set('posted', 1)->update('blog_posts');
     * @example $this->db->where('blog_id',123)->set(array('posted' => 1, 'time' => time()))->update('blog_posts');
     */
    public function set($fields, $value = null)
    {
        $this->updateInit('$set');
        if (is_string($fields)) {
            $this->updates['$set'][$fields] = $value;
        } elseif (is_array($fields)) {
            foreach ($fields as $field => $value) {
                $this->updates['$set'][$field] = $value;
            }
        }
        return ($this);
    }
    
    /**
     * Unsets a field (or fields).
     * 
     * @param string $fields fields
     * 
     * @return object
     * @example $this->db->where('blog_id', 123)->unsetField('posted')->update('blog_posts');
     * @example $this->db->where('blog_id', 123)->set('posted','time')->update('blog_posts');
     */
    public function unsetField($fields)
    {
        $this->updateInit('$unset');

        if (is_string($fields)) {
            $this->updates['$unset'][$fields] = 1;
        } elseif (is_array($fields)) {
            foreach ($fields as $field) {
                $this->updates['$unset'][$field] = 1;
            }
        }
        return ($this);
    }
    
    /**
     * Adds value to the array only if its not in the array already.
     * 
     * @param string $field  field
     * @param mixed  $values values
     * 
     * @return object
     * @example $this->db->where('blog_id', 123))->addToSet('tags', 'php')->update('blog_posts');
     * @example $this->db->where('blog_id', 123))->addToSet('tags', array('php', 'obullo', 'mongodb'))->update('blog_posts');
     */
    public function addToSet($field, $values)
    {
        $this->updateInit('$addToSet');

        if (is_string($values)) {
            $this->updates['$addToSet'][$field] = $values;
        } elseif (is_array($values)) {
            $this->updates['$addToSet'][$field] = array('$each' => $values);
        }
        return ($this);
    }

    /**
     * Pushes values into a field (field must be an array).
     * 
     * @param string $fields fields
     * @param mixed  $value  value
     * 
     * @return object
     * @example $this->db->where('blog_id', 123)->push('comments', array('text'=>'Hello world'))->update('blog_posts');
     * @example $this->db->where('blog_id', 123)->push(array('comments' => array('text'=>'Hello world')), 'viewed_by' => array('Alex'))->update('blog_posts');
     */
    public function push($fields, $value = '')
    {
        $this->updateInit('$push');

        if (is_string($fields)) {
            $this->updates['$push'][$fields] = $value;
        } elseif (is_array($fields)) {
            foreach ($fields as $field => $value) {
                $this->updates['$push'][$field] = $value;
            }
        }
        return ($this);
    }

    /**
     * Pops the last value from a field (field must be an array).
     * 
     * @param string $field field
     * 
     * @return object
     * @example $this->db->where('blog_id', 123))->pop('comments')->update('blog_posts');
     * @example $this->db->where('blog_id', 123))->pop(array('comments', 'viewed_by'))->update('blog_posts');
     */
    public function pop($field)
    {
        $this->updateInit('$pop');

        if (is_string($field)) {
            $this->updates['$pop'][$field] = -1;
        } elseif (is_array($field)) {
            foreach ($field as $pop_field) {
                $this->updates['$pop'][$pop_field] = -1;
            }
        }
        return ($this);
    }
    
    /**
     * Removes by an array by the value of a field.
     * 
     * @param string $field field
     * @param array  $value value
     * 
     * @return object
     * @example $this->db->pull('comments', array('comment_id'=>123))->update('blog_posts');
     */
    public function pull($field = '', $value = array())
    {
        $this->updateInit('$pull');
        $this->updates['$pull'] = array($field => $value);
        return ($this);
    }

    /**
     * Delete all documents from the passed collection based upon certain criteria.
     * 
     * @param string $collection collection
     * @param array  $options    options
     * 
     * @return int affected rows.
     * @example $this->db->delete('foo', $data = array());
     * @throws Exception 
     */
    public function delete($collection = '', $options = array())
    {
        $this->operation  = 'delete';  // Set operation for lastQuery output
        $this->collection = $collection;

        $defaultOptions = array_merge(array('justOne' => false), $this->configOptions);

        if (empty($collection)) {
            throw new LogicException('No Mongo collection selected to delete.');
        }
        if (isset($this->wheres['_id']) AND ! ($this->wheres['_id'] instanceof MongoId)) {
            $this->wheres['_id'] = new MongoId($this->wheres['_id']);
        }
        
        $this->db->{$collection}->remove($this->wheres, array_merge($defaultOptions, $options));

        $this->resetSelect();
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
    public function connect()
    {
        if ($this->db == null) {
            $this->connection = new \MongoClient($this->connectionString);
            $this->db = $this->connection->{$this->dbname};
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
     * @return void
     * @throws Exception
     */
    public function setConnectionString() 
    {
        if ($this->dbname == '') {
            throw new LogicException('Please set a $mongo[\'database\'] from app/config/mongo.php.');
        }
        $connectionString = "mongodb://";

        if (empty($this->host)) {
            throw new LogicException('You need to specify a hostname connect to MongoDB.');
        }
        if (empty($this->dbname)) {
            throw new LogicException('You need to specify a database name connect to MongoDB.');
        }
        if ( ! empty($this->user) AND ! empty($this->pass)) {
            $connectionString .= "{$this->user}:{$this->pass}@";
        }
        if (isset($this->port) AND ! empty($this->port)) {
            $connectionString .= "{$this->host}:{$this->port}";
        } else {
            $connectionString .= "{$this->host}";
        }
        $this->connectionString = trim($connectionString).'/'.$this->dbname;
    }

    /**
     *  Resets the class variables to default settings
     * 
     * @return void
     */
    protected function resetSelect()
    {
        $this->selects    = array();
        $this->updates    = array();
        $this->groupBy    = array();
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
    protected function whereInit($param)
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
    protected function updateInit($method)
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
        return $this->insertId;
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