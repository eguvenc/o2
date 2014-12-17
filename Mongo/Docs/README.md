
## MongoDb Class

Mongo Class is a lightweight <kbd>( CRUD based )</kbd> database management library for popular NoSQL database <b>Mongodb</b>.

### Initializing MongoDb Class

------

```php
<?php
$this->c->load('service/mongo');
$this->mongo->method();
```

Once loaded, the Mongo object will be available using: <kbd>$this->mongo->method();</kbd>

### Configuring Mongodb Options

------

You can set advanced mongodb options using <kbd>app/config/local/nosql.php</kbd> file.

```php
<?php
return array(
    'mongo' => array(
        'db' => array(
            'host' => 'localhost',
            'username' => envget('MONGO_USERNAME'),
            'password' => envget('MONGO_PASSWORD'),
            'port' => '27017',
        ),
    ),
);
```

```php
<?php
$c->load('mongo/db');

$this->mongo->get('users');
$row = $this->db->getRowArray();

if($row) {
    foreach($docs as $row) {
        echo $row['username'].'<br />';
    }
}
```

### Some Crud (Active Record) Functions Are Available

```php
<?php
$this->mongo->select();
$this->mongo->where('username', 'bob');

$row = $this->db->getRow();

if ($row) {
    foreach($docs as $row) {
        echo $row->username.'<br />';
    }
}
```

### Using MongoDb for CRUD ( Create, read, update and delete ) Functions

------

#### $this->db->insert($collection, $data, $options)

```php
$options = array('query_safety' => true); // Optionally

$affectedRows = $this->db->insert('users', array('username'  => 'john', 'date' => new MongoDate()), $options);

echo $affectedRows.' row(s) added to database !'; '  // 2 row(s) added to database !';
```

#### $this->db->update($collection, $data)

```php
$options = array('query_safety' => true);  // Optionally

$this->db->where('_id', new MongoId('50a39b5e1657ae3817000000'));
$this->db->update('users', array('username' => 'bob'), $options) 
```

```php
$this->db->where('username', 'john');
$this->db->update('users', array('username' => 'bob', 'ts' => new MongoDate()))
```

#### $this->db->delete($collection)

```php
$this->db->where('_id', new MongoId('50a39b5e1657ae3817000000'));
$this->db->delete('users')
```

#### $this->db->get()

```php
<?php
$this->db->get('users');

echo 'found '.$this->db->getCount().' row(s)';

foreach($this->db->getResultArray() as $row)
{
   echo $row['username'].'<br />';
}
```

#### Fetching one row as a object.

```php
<?php
$this->db->get('users');
$row = $this->db->getRow();

echo $row->username;
```

```php
<?php
$this->db->select('username,  user_firstname');

$docs = $this->db->get('users');
$row = $docs->getNext();

echo $row['username'];

 // Also you can provide array.
$this->db->select(array('username', 'user_firstname'));
$this->db->get('users');
```

#### $this->db->aggregate();

Aggregate function is a easy way to doing map / reduce functions. ( especially for group by and match operations )

```php
<?php
$this->db->agrregate('users', array('$or' => array(array('username' => 'john'), array('username' => 'bob'))));
$this->db->getResult();
```

#### $this->db->where()

```php
<?php
$this->db->where('username', 'bob');
$this->db->get('users');

$row = $this->db->getRow();
echo $row['username'];
```


#### $this->db->where('field >', 'value') ( Greater than )

```php
<?php
$this->db->where('foo >', 20);
$this->db->get('foobar');
```

#### $this->db->where('field <', 'value') ( Less than )

```php
<?php
$this->db->where('foo <', 20);
$this->db->get('foobar');
```

#### $this->db->where('field >=', 'value') ( Greater than or equal to )

```php
<?php
$this->db->where('foo >=', 20);
$this->db->get('foobar');
```

#### $this->db->where('field <=', 'value') ( Less than or equal to )

```php
<?php
$this->db->where('foo <=', 20);
$this->db->get('foobar');
```

#### $this->db->where('field !=', 'value') ( Not equal to )

```php
<?php
$this->db->where('foo !=', 20);
$this->db->get('foobar');
```

#### $this->db->orWhere()

```php
<?php
$this->db->orWhere('username', 'bob');
$this->db->orWhere('username', 'john');

$docs = $this->db->get('users');
```

#### $this->db->whereIn()

```php
<?php
$this->db->whereIn('username', array('bob', 'john', 'jenny'));
```

#### $this->db->whereIn() ( Not In )

```php
<?php
$this->db->whereIn('username !=', array('bob', 'john', 'jenny'));

$docs = $this->db->get('users');
```

#### $this->db->whereInAll()

Gets all the results by querying with respect to elements in a given array. 

```php
<?php
$this->db->whereInAll('foo', array('bar', 'zoo', 'blah'));

$docs = $this->db->get('users');
```

#### $this->db->like($field = "", $value = "", $flags = "i", $enable_start_wildcard = true, $enable_end_wildcard = true)

```php
<?php
$this->db->like('username', 'bob');
```

##### Flags

```php
/*
*  @var $flags
*    i = case insensitive
*    m = multiline
*    x = can contain comments
*    l = locale
*    s = dotall, "." matches everything, including newlines
*    u = match unicode
*  
* @var $enableStartWildcard
*    If set to anything other than true, a starting line character "^" will be prepended
*    to the search value, representing only searching for a value at the start of a new line.
*  
* @var $enableEndWildCard
*    If set to anything other than true, an ending line character "$" will be appended
*    to the search value, representing only searching for a value at the end of a line.
*/
```

#### $this->db->orLike()

```php
<?php
$this->db->orLike('username', 'bob');
```

#### $this->db->notLike()

```php
<?php
$this->db->notLike('username', 'bob');
```

#### $this->db->orderBy()

```php
<?php
$this->db->whereIn('username', array('bob', 'john', 'jenny'));
$this->db->orderBy('username', 'ASC');

$this->db->get('users');
$this->db->getResult();
```

#### $this->db->groupBy()

```php
<?php
$this->db->groupBy('username', array('count' => 0) ,'function (obj, prev) {prev.count++;}');

$this->db->get('users');
$this->db->getResult();
```

#### $this->db->limit()

```php
<?php
$this->db->whereIn('username', array('bob', 'john', 'jenny'));
$this->db->orderBy('username', 'ASC');
$this->db->limit(10);

$this->db->get('users');
$this->db->getResult();
```

#### $this->db->offset()

```php
<?php
$this->db->whereIn('username', array('bob', 'john', 'jenny'));
$this->db->orderBy('username', 'ASC');
$this->db->limit(10);
$this->db->offset(20);

$docs = $this->db->get('users');
```

#### $this->db->find($criteria, $fields)

```php
<?php
$this->db->from('users');
$docs = $this->db->find(array('$or' => array(array('username' => 'john'), array('username' => 'bob'))),  array('username'));

foreach($docs as  $row)
{
    echo $row->username. '<br />';
}
```

#### $this->db->findOne($criteria, $fields)

```php
<?php

$this->db->select('username');
$this->db->from('users');
$docs = $this->db->find(array('$or' => array(array('username' => 'john'), array('username' => 'bob'))));

foreach($docs as  $row)
{
    echo $row->username. '<br />';
}
```

#### $this->db->insertId()

```php
<?php

$this->db->insert('users', array('username' => 'john28', 'ts' => new MongoDate()));

echo $this->db->insertId();   // last inserted Mongo ID.
```

#### $this->db->inc()

Increments the value of a field.

```php
<?php
$this->db->where('blog_id', 123);
$this->db->inc(array('num_comments' => 1));
$this->db->update('blog_posts');
```

#### $this->db->dec()

Decrements the value of a field.

```php
<?php
$this->db->where('blog_id', 123);
$this->db->dec(array('num_comments' => 1));
$this->db->update('blog_posts');
```

#### $this->db->set()

Sets a field to a value.

```php
<?php
$this->db->where(array('blog_id'=>123))
$this->db->set('posted', 1);
$this->db->update('blog_posts');
```

#### $this->db->unsetField()

Unsets a field (or fields).

```php
<?php
$this->db->where(array('blog_id'=>123))
$this->db->unset('posted');
$this->db->update('blog_posts');
```

#### $this->db->addToSet()

Adds value to the array only if it is not already in the array.

```php
<?php
$this->db->where('blog_id', 123);
$this->db->addToSet('tags', 'php');
$this->db->update('blog_posts'); 

$this->db->where('blog_id', 123);
$this->db->addToSet('tags', array('php', 'test', 'mongodb'));
$this->db->update('blog_posts');
```

#### $this->db->pull()

Removes an array by the value of a field.

```php
<?php
$this->db->pull('comments', array('comment_id', 123));
$this->db->update('blog_posts');
```

#### $this->db->push()

Pushes values into a field (field must be an array).

```php
<?php
$this->db->where('blog_id', 123);
$this->db->push(array('comments' => array('text'=>'Hello world')), 'viewed_by' => array('Alex'));
$this->db->update('blog_posts');
```

#### $this->db->pop()

Pops the last value from a field (field must be an array).

```php
<?php
$this->db->where('blog_id', 123);
$this->db->pop(array('comments', 'viewed_by'));
$this->db->update('blog_posts');
```

#### $this->db->batchInsert()

Insert a new multiple document into the passed collection. For instance, you need to insert over the one million record to database at once.

```php
<?php
$this->db->batchInsert('foo',  $data = array());
```

#### $this->db->getInstance()

Returns to Mongodb instance of object. For example, you can store a file using mongo instance <kbd>gridfs</kbd> method. The gridfs functionality allows you to store media files to mongo db.

```php
<?php
$gridFS = $this->db->getInstance()->getGridFS();  // get a MongoGridFS instance
$id     = $gridFS->storeFile(
    $filepath,
    array(
      'filename' => uniqid(time()), 
      'filetype' => $_FILES['field']['type'],
      'filegroup'=> 'profile-picture',
      'caption'  => 'Profile Picture'
    )
);
echo $id;
```
Removes a gridfs file using mongo instance.

```php
<?php
$gridFS = $this->db->getInstance()->getGridFS();
$gridFS->remove(array('user_id' => new MongoId($this->auth->getIdentity('_id')), 'filegroup' => 'profile-picture'));
?>
```

### Function Reference

-----

#### $this->mongo->useMongoid($bool = true);

Mongo id switch

#### $this->mongo->select($includes = '');

Determine which fields to include OR which to exclude during the query process.

#### $this->mongo->from($collection = '');

Set a collection.

#### $this->mongo->where($wheres, $value = null);

Get the documents based on these search parameters.

#### $this->mongo->orWhere($wheres, $value = null);

Get the documents where the value of a $field may be something else

#### $this->mongo->whereIn($field = '', $in = array());

Get the documents where the value of a $field is in a given $in array().

#### $this->mongo->whereInAll($field = '', $in = array());

Get the documents where the value of a $field is in all of a given $in array().

#### $this->mongo->like($field = "", $value = "", $flags = "i", $enableStartWildcard = true, $enableEndWildCard = true);

Get the documents where the (string) value of a $field is like a value. The defaults allow for a case-insensitive search.

#### $this->mongo->orLike($field, $like = array());

The same as the aboce but multiple instances are joined by OR:

#### $this->mongo->notLike($field, $like = array());

The same as the above but multiple instances are joined by NOT LIKE:

#### $this->mongo->orderBy($col, $direction  = 'ASC');

Order by column name

#### $this->mongo->groupBy($key = null , $initial = array('count' => 0) ,$reduce ='$this->mongo->(obj, prev) { prev.count++;}' );

Group by

#### $this->mongo->limit($x = 99999);

Limit the result set to $x number of documents.

#### $this->mongo->offset($x = 0);

Offset the result set to skip $x number of documents.

#### $this->mongo->get($collection = '');

Get the documents based upon the passed parameters.

#### $this->mongo->aggregate($collection, $pipeline, $options = null);

Perform an aggregation using the aggregation framework

#### $this->mongo->insert($collection = '', $data = array(), $options = array());

Insert a new document into the passed collection

#### $this->mongo->batchInsert($collection = '', $data = array(), $options = array());

Insert a multiple new document into the passed collection.

#### $this->mongo->update($collection = '', $data = array(), $options = array());

Updates multiple document

#### $this->mongo->inc($fields = '', $value = 0);

Increments the value of a field.

#### $this->mongo->dec($fields = '', $value = 0);

Decrements the value of a field.

#### $this->mongo->set($fields, $value = null);

Sets a field to a value.

#### $this->mongo->unsetField($fields);

Unsets a field (or fields).

#### $this->mongo->addToSet($field, $values);

Adds value to the array only if its not in the array already.

#### $this->mongo->push($fields, $value = '');

Pushes values into a field (field must be an array).

#### $this->mongo->pop($field);

Pops the last value from a field (field must be an array).

#### $this->mongo->pull($field = '', $value = array());

Removes by an array by the value of a field.

#### $this->mongo->delete($collection = '', $options = array());

Delete all documents from the passed collection based upon certain criteria.

#### $this->mongo->connect();

Establish a connection to MongoDB using the connection string generated in the setConnectionString() method.

#### $this->mongo->getInstance();

Returns to Mongodb instance of object.

#### $this->mongo->setConnectionString() ;

Build the connection string from the config file.

#### $this->mongo->insertId();

Get last inserted Mongo id.

#### $this->mongo->isMongoId($string = '', $value = '');

Auto add mongo id if "_id" used  .

#### $this->mongo->lastError();

Get last occurence error

### Function Reference of Query Results

------

#### $this->results->result();

This function returns the query result as object.

#### $this->results->resultArray();

This function returns the query result as a pure array, or an empty array when no result is produced.

#### $this->results->row();

This function fetches one item and returns query result as object or false on failure.

#### $this->results->rowArray();

Identical to the above row() function, except it returns an array.

#### $this->results->count();

Get number of rows