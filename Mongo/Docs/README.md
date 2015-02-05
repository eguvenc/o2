
## Mongo Query Class

Mongo Db Class is a full featured <kbd>( CRUD based )</kbd> database management library for popular NoSQL database <b>Mongodb</b>.

### Initializing Mongo Provider

------

```php
<?php
$this->mongo = $this->c['service provider mongo']->get(['connection' => 'default'])->selectDb('collection');
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
            'username' => $c['env']['MONGO_USERNAME'],
            'password' => $c['env']['MONGO_PASSWORD'],
            'port' => '27017',
        ),
    ),
);
```

### Basic Usage

```php
<?php
$this->c['service/provider/mongo']->db('stats');

foreach ($this->mongo->users->find() as $val) {
    echo $val['_id'].'<br>';
}
```

Changing the database


```php
$this->c['service/provider/mongo')->db('db'];  // change database

foreach ($this->mongo->logs->find() as $val) {
    echo $val['message'].'<br>';
}
```


### Initializing Mongo Query Service


------

```php
<?php
$this->c['service/mongo']->db('name'];
$this->mongo->get('collection')->method();
```

### Connection for Mongo Query Service

```php
<?php
$this->c['service/mongo']->db('test'];

$this->mongo->get('users');
$row = $this->mongo->rowArray();

if($row) {
    foreach($docs as $row) {
        echo $row['username'].'<br />';
    }
}
```

```php
$this->mongo->select()->where('username', 'bob')->get('users');
$row = $this->mongo->row();

if ($row) {
    foreach($docs as $row) {
        echo $row->username.'<br />';
    }
}
```

### Using CRUD ( Create, read, update and delete ) Functions

------

#### $this->mongo->insert($collection, $data, $options)

```php
<?php
$options = array('query_safety' => true); // Optionally

$affectedRows = $this->mongo->insert('users', array('username'  => 'john', 'date' => new MongoDate()), $options);

echo $affectedRows.' row(s) added to database !'; '  // 2 row(s) added to database !';
```

#### $this->mongo->update($collection, $data)

```php
<?php
$options = array('query_safety' => true);  // Optionally

$this->mongo->where('_id', new MongoId('50a39b5e1657ae3817000000'));
$this->mongo->update('users', array('username' => 'bob'), $options) 
```

```php
<?php
$this->mongo->where('username', 'john');
$this->mongo->update('users', array('username' => 'bob', 'ts' => new MongoDate()))
```

#### $this->mongo->delete($collection)

```php
<?php
$this->mongo->where('_id', new MongoId('50a39b5e1657ae3817000000'))->delete('users');
```

#### $this->mongo->get()

```php
<?php
$this->mongo->get('users');

echo 'found '.$this->mongo->count().' row(s)';

foreach($this->mongo->resultArray() as $row) {
   echo $row['username'].'<br />';
}
```

#### Fetching one row as a object.

```php
<?php
$row = $this->mongo->get('users')->row();
echo $row->username;
```

```php
<?php
$docs = $this->mongo->select('username,  user_firstname')->get('users');
$row  = $docs->nextRow();

echo $row['username'];

 // Also you can provide array.
$this->mongo->select(array('username', 'user_firstname'));
$this->mongo->get('users');
```

#### $this->mongo->aggregate();

Aggregate function is a easy way to doing map / reduce functions. ( especially for group by and match operations )

```php
<?php
$this->mongo->aggregate('users', array('$or' => array(array('username' => 'john'), array('username' => 'bob'))));
$this->mongo->result();
```

#### $this->mongo->where()

```php
<?php
$this->mongo->where('username', 'bob')->get('users');

$row = $this->mongo->row();
echo $row['username'];
```


#### $this->mongo->where('field >', 'value') ( Greater than )

```php
<?php
$this->mongo->where('foo >', 20);
$this->mongo->get('foobar');
```

#### $this->mongo->where('field <', 'value') ( Less than )

```php
<?php
$this->mongo->where('foo <', 20);
$this->mongo->get('foobar');
```

#### $this->mongo->where('field >=', 'value') ( Greater than or equal to )

```php
<?php
$this->mongo->where('foo >=', 20);
$this->mongo->get('foobar');
```

#### $this->mongo->where('field <=', 'value') ( Less than or equal to )

```php
<?php
$this->mongo->where('foo <=', 20);
$this->mongo->get('foobar');
```

#### $this->mongo->where('field !=', 'value') ( Not equal to )

```php
<?php
$this->mongo->where('foo !=', 20);
$this->mongo->get('foobar');
```

#### $this->mongo->orWhere()

```php
<?php
$this->mongo->orWhere('username', 'bob');
$this->mongo->orWhere('username', 'john');

$docs = $this->mongo->get('users');
```

#### $this->mongo->whereIn()

```php
<?php
$this->mongo->whereIn('username', array('bob', 'john', 'jenny'));
```

#### $this->mongo->whereIn() ( Not In )

```php
<?php
$this->mongo->whereIn('username !=', array('bob', 'john', 'jenny'));

$docs = $this->mongo->get('users');
```

#### $this->mongo->whereInAll()

Gets all the results by querying with respect to elements in a given array. 

```php
<?php
$this->mongo->whereInAll('foo', array('bar', 'zoo', 'blah'));

$docs = $this->mongo->get('users');
```

#### $this->mongo->like($field = "", $value = "", $flags = "i", $enable_start_wildcard = true, $enable_end_wildcard = true)

```php
<?php
$this->mongo->like('username', 'bob');
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

#### $this->mongo->orLike()

```php
<?php
$this->mongo->orLike('username', 'bob');
```

#### $this->mongo->notLike()

```php
<?php
$this->mongo->notLike('username', 'bob');
```

#### $this->mongo->orderBy()

```php
<?php
$this->mongo->whereIn('username', array('bob', 'john', 'jenny'));
$this->mongo->orderBy('username', 'ASC');

$this->mongo->get('users');
$this->mongo->result();
```

#### $this->mongo->groupBy()

```php
<?php
$this->mongo->groupBy('username', array('count' => 0) ,'function (obj, prev) {prev.count++;}');

$this->mongo->get('users');
$this->mongo->result();
```

#### $this->mongo->limit()

```php
<?php
$this->mongo->whereIn('username', array('bob', 'john', 'jenny'));
$this->mongo->orderBy('username', 'ASC');
$this->mongo->limit(10);

$this->mongo->get('users');
$this->mongo->result();
```

#### $this->mongo->offset()

```php
<?php
$this->mongo->whereIn('username', array('bob', 'john', 'jenny'));
$this->mongo->orderBy('username', 'ASC');
$this->mongo->limit(10);
$this->mongo->offset(20);

$docs = $this->mongo->get('users');
```

#### $this->mongo->find($criteria, $fields)

```php
<?php
$this->mongo->from('users');
$docs = $this->mongo->find(array('$or' => array(array('username' => 'john'), array('username' => 'bob'))),  array('username'));

foreach($docs as  $row) {
    echo $row->username. '<br />';
}
```

#### $this->mongo->insertId()

```php
<?php

$this->mongo->insert('users', array('username' => 'john28', 'ts' => new MongoDate()));

echo $this->mongo->insertId();   // last inserted Mongo ID.
```

#### $this->mongo->inc()

Increments the value of a field.

```php
<?php
$this->mongo->where('blog_id', 123);
$this->mongo->inc(array('num_comments' => 1));
$this->mongo->update('blog_posts');
```

#### $this->mongo->dec()

Decrements the value of a field.

```php
<?php
$this->mongo->where('blog_id', 123);
$this->mongo->dec(array('num_comments' => 1));
$this->mongo->update('blog_posts');
```

#### $this->mongo->set()

Sets a field to a value.

```php
<?php
$this->mongo->where(array('blog_id'=>123))
$this->mongo->set('posted', 1);
$this->mongo->update('blog_posts');
```

#### $this->mongo->unsetField()

Unsets a field (or fields).

```php
<?php
$this->mongo->where(array('blog_id'=>123))
$this->mongo->unset('posted');
$this->mongo->update('blog_posts');
```

#### $this->mongo->addToSet()

Adds value to the array only if it is not already in the array.

```php
<?php
$this->mongo->where('blog_id', 123);
$this->mongo->addToSet('tags', 'php');
$this->mongo->update('blog_posts'); 

$this->mongo->where('blog_id', 123);
$this->mongo->addToSet('tags', array('php', 'test', 'mongodb'));
$this->mongo->update('blog_posts');
```

#### $this->mongo->pull()

Removes an array by the value of a field.

```php
<?php
$this->mongo->pull('comments', array('comment_id', 123));
$this->mongo->update('blog_posts');
```

#### $this->mongo->push()

Pushes values into a field (field must be an array).

```php
<?php
$this->mongo->where('blog_id', 123);
$this->mongo->push(array('comments' => array('text'=>'Hello world')), 'viewed_by' => array('Alex'));
$this->mongo->update('blog_posts');
```

#### $this->mongo->pop()

Pops the last value from a field (field must be an array).

```php
<?php
$this->mongo->where('blog_id', 123);
$this->mongo->pop(array('comments', 'viewed_by'));
$this->mongo->update('blog_posts');
```

#### $this->mongo->batchInsert()

Insert a new multiple document into the passed collection. For instance, you need to insert over the one million record to database at once.

```php
<?php
$this->mongo->batchInsert('foo',  $data = array());
```

#### $this->mongo->getInstance()

Returns to Mongodb instance of object. For example, you can store a file using mongo instance <kbd>gridfs</kbd> method. The gridfs functionality allows you to store media files to mongo db.

```php
<?php
$gridFS = $this->mongo->getInstance()->getGridFS();  // get a MongoGridFS instance
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
$gridFS = $this->mongo->getInstance()->getGridFS();
$gridFS->remove(array('user_id' => new MongoId($this->auth->getIdentity('_id')), 'filegroup' => 'profile-picture'));
?>
```

### Function Reference

-----

#### $this->mongo->select(mixed $includes = '');

Determine which fields to include OR which to exclude during the query process.

#### $this->mongo->from(string $collection = '');

Set a collection.

#### $this->mongo->where(mixed $wheres, $value = null);

Get the documents based on these search parameters.

#### $this->mongo->orWhere(mixed $wheres, $value = null);

Get the documents where the value of a $field may be something else

#### $this->mongo->whereIn(string $field = '', $in = array());

Get the documents where the value of a $field is in a given $in array().

#### $this->mongo->whereInAll(string $field = '', $in = array());

Get the documents where the value of a $field is in all of a given $in array().

#### $this->mongo->like(string $field = "", $value = "", $flags = "i", $enableStartWildcard = true, $enableEndWildCard = true);

Get the documents where the (string) value of a $field is like a value. The defaults allow for a case-insensitive search.

#### $this->mongo->orLike(string $field, $like = array());

The same as the aboce but multiple instances are joined by OR:

#### $this->mongo->notLike(string $field, $like = array());

The same as the above but multiple instances are joined by NOT LIKE:

#### $this->mongo->orderBy(string $col, $direction  = 'ASC');

Order by column name

#### $this->mongo->groupBy(string $key = null , $initial = array('count' => 0) ,$reduce ='$this->mongo->(obj, prev) { prev.count++;}' );

Group by

#### $this->mongo->limit(int $x = 99999);

Limit the result set to $x number of documents.

#### $this->mongo->offset(int $x = 0);

Offset the result set to skip $x number of documents.

#### $this->mongo->get(string $collection = '');

Get the documents based upon the passed parameters.

#### $this->mongo->find(array $criteria, array $fields);

Perform a search query based upon the passed criterias. Using second parameter you can pass select fields.

#### $this->mongo->aggregate(string $collection, array $pipeline, $options = null);

Perform an aggregation using the aggregation framework

#### $this->mongo->insert(string $collection = '', array $data = array(), $options = array());

Insert a new document into the passed collection

#### $this->mongo->batchInsert(string $collection = '', array $data = array(), $options = array());

Insert a multiple new document into the passed collection.

#### $this->mongo->update(string $collection = '', array $data = array(), $options = array());

Updates multiple document

#### $this->mongo->inc(mixed $fields = '', $value = 0);

Increments the value of a field.

#### $this->mongo->dec(mixed $fields = '', $value = 0);

Decrements the value of a field.

#### $this->mongo->set(mixed $fields, $value = null);

Sets a field to a value.

#### $this->mongo->unsetField(mixed $fields);

Unsets a field (or fields).

#### $this->mongo->addToSet(string $field, $values);

Adds value to the array only if its not in the array already.

#### $this->mongo->push(array $fields, $value = '');

Pushes values into a field (field must be an array).

#### $this->mongo->pop(array $field);

Pops the last value from a field (field must be an array).

#### $this->mongo->pull(string $field = '', $value = array());

Removes by an array by the value of a field.

#### $this->mongo->delete(string $collection = '', $options = array());

Delete all documents from the passed collection based upon certain criteria.

#### $this->mongo->connect();

Establish a connection to MongoDB using the connection string generated in the setConnectionString() method.

#### $this->mongo->setConnectionString() ;

Build the connection string from the config file.

#### $this->mongo->insertId();

Get last inserted Mongo id.

#### $this->mongo->isMongoId(mixed $string = '', $value = '');

Auto add mongo id if "_id" used  .

#### $this->mongo->lastError();

Get last occurence error.

#### $this->mongo->getInstance();

Returns to Mongodb instance of object.


### Function Reference of Query Results

------

#### $this->mongo->result();

This function returns the query result as object.

#### $this->mongo->resultArray();

This function returns the query result as a pure array, or an empty array when no result is produced.

#### $this->mongo->row();

This function fetches one item and returns query result as object or false on failure.

#### $this->mongo->rowArray();

Identical to the above row() function, except it returns an array.

#### $this->mongo->firstRow();

Fetch first row as object

#### $this->mongo->previousRow();

Fetch previous row as object

#### $this->mongo->nextRow();

Fetch next row as object

#### $this->mongo->lastRow();

Fetch last row as object

#### $this->mongo->count();

Get number of rows