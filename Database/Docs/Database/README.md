
## Database

<ul>
<li><a href="#requirements">Server Requirements</a></li>
<li><a href="#connection">Connection</a><ul>
        <li><a href="#choosing-driver">Choosing Database Driver</a></li>
        <li><a href="#supported-types">Supported Database Types</a></li>
        <li><a href="#explanation-of-values">Explanation of Values</a></li>
        <li><a href="#options-parameter">Options Parameter</a></li>
        <li><a href="#connection-tutorial">Connection Tutorial</a></li></ul>    
</li>
<li><a href="#generating-query-results">Generating Query Results</a></li>  
<li><a href="#insert-update-delete">Insert, Update, Delete Operations</a></li>  
<li><a href="#running-queries">Running And Escaping Queries</a></li>     
<li><a href="#query-binding">Query Binding</a></li>
<li><a href="#query-helper-functions">Query Helper Functions</a></li>
<li><a href="#transactions">Transactions</a></li>
</ul>

### Server Requirements <a name='requirements'></a>

------

Database class use <strong>PDO</strong> for database operations.

<strong>Mysql</strong> and <strong>SQLite</strong> drivers is enabled by default. If you want to use another Database driver you must enable related PDO Driver from your php.ini file.

Un-comment the PDO database file pdo.ini

```php
extension=pdo.so
```

and un-comment your driver file pdo_mysql.ini

```php
extension=pdo_mysql.so
```

Look at for more details http://www.php.net/manual/en/pdo.installation.php

## Connection

### Choosing Database Driver <a name='choosing-driver'></a>

Open <kbd>services.php</kbd> in your root and set your <u>Database Driver</u> like below the example example ( Default Mysql ).


```php
<?php
/*
|--------------------------------------------------------------------------
| Db
|--------------------------------------------------------------------------
*/
$c['db'] = function () use ($c) {
    return new Obullo\Database\Pdo\Mysql($c->load('config')['database']);
};
```

To set your database configuration edit your <kbd>app/config/env/local/config.php</kbd>.

```php
<?php
/*
|--------------------------------------------------------------------------
| Database
|--------------------------------------------------------------------------
*/
'database' => array(
        'host' => 'localhost',
        'username' => 'root',
        'password' => '123456',
        'database' => 'demo_blog',
        'prefix'   => '',
        'port'     => '',
        'char_set' => 'utf8',
        'dsn'      => '',
        'options'  => array() // array( PDO::ATTR_PERSISTENT => false ); 
),
```

### Supported Database Types <a name='supported-types'></a>

------

<table class="span9">
<thead>
<tr>
<th>PDO Driver Name</th>
<th>Connection Name</th>
<th>Database Name</th>
</tr>
</thead>
<tbody>
<tr>
<td>PDO_DBLIB</td>
<td>dblib / mssql / sybase / freetds</td>
<td>FreeTDS / Microsoft SQL Server / Sybase</td>
</tr>
<tr>
<td>PDO_FIREBIRD</td>
<td>firebird</td>
<td>Firebird/Interbase 6</td>
</tr>
<tr>
<td>PDO_IBM</td>
<td>ibm / db2</td>
<td>IBM DB2</td>
</tr>
<tr>
<td>PDO_MYSQL</td>
<td>mysql</td>
<td>MySQL 3.x/4.x/5.x</td>
</tr>
<tr>
<td>PDO_OCI</td>
<td>oracle / (or alias oci)</td>
<td>Oracle Call Interface</td>
</tr>
<tr>
<td>PDO_ODBC</td>
<td>odbc</td>
<td>ODBC v3 (IBM DB2, unixODBC and win32 ODBC)</td>
</tr>
<tr>
<td>PDO_PGSQL</td>
<td>pgsql</td>
<td>PostgreSQL</td>
</tr>
<tr>
<td>PDO_SQLITE</td>
<td>sqlite / sqlite2 / sqlite3</td>
<td>SQLite 3 and SQLite 2</td>
</tr>
<tr>
<td>PDO_4D</td>
<td>4d</td>
<td>4D</td>
</tr>
<tr>
<td>PDO_CUBRID</td>
<td>cubrid</td>
<td>Cubrid</td>
</tr>
</tbody>
</table>

Framework has a config file that lets you store your database connection values (username, password, database name, etc.). The config file is located in:

<kbd>app/config/env/local/config.php</kbd>

If you want to add a second or third database connection <strong>copy/paste</strong> above the settings and change the <strong>'database'</strong> key name of your version you have choosen.

```php
<?php

'database' => array(
    'host'     => 'localhost',
    'username' => 'root',
    'password' => '',
    'database' => 'example_db',
    'prefix'   => '',
    'port'     => '',
    'charset'  => 'utf8',
    'dsn'      => '',
    'options'  => array()
    ),
),
'dbAny' => array(            // another database configuration
    'host'     => 'localhost',
    'username' => 'root',
    'password' => '',
    ...
    ),
),
```

Then you need add it to <kbd>services.php</kbd>.

```php
<?php

/*
|--------------------------------------------------------------------------
| Db2
|--------------------------------------------------------------------------
*/
$c['dbAny'] = function () use ($c) {
    return new Obullo\Database\Pdo\Mysql($c->load('config')['dbAny']);
};
```

Finally you can run it in the controller like below.

```php
<?php

$this->dbAny->query('...');
```

If you want to add <strong>dsn</strong> connection you don't need to provide some other parameters like this..

```php
<?php

'db' => array(
    'dsn'      => "mysql:host=localhost;port=3307;dbname=test_db;username=root;password=1234;",
    'options'  => array()
    ),
```

#### Explanation of Values <a name='explanation-of-values'></a>

* host     - The hostname of your database server. Often this is "localhost".
* username - The username used to connect to the database.
* password - The password used to connect to the database.
* database - The name of the database you want to connect to.
* driver   - The database type. ie: mysql, postgres, odbc, etc. Must be specified in lower case.
* port     - The database port number.
* charset  - The character set used in communicating with the database.
* dsn - Data source name.If you want to use dsn, you will not need to supply other parameters.
* options - Pdo set attribute options.

<strong>Note:</strong> Depending on what database platform you are using (MySQL, Postgres, etc.) not all values will be needed. For example, when using SQLite you will not need to supply a username or password, and the database name will be the path to your database file. The information above assumes you are using MySQL.

#### Options Parameter  <a name='options-parameter'></a>

There is a global <strong>PDO options</strong> parameter in your database configuration. You can <strong>set connection attributes</strong> for each connection. if you want to <strong>Persistent Connection</strong> you can do it like.

```php
<?php

'options'  => array( PDO::ATTR_PERSISTENT => true );
```
You can add more attributes in your option array like this.

```php
<?php

'options' => array( PDO::ATTR_PERSISTENT => false , PDO::MYSQL_ATTR_USE_BUFFERED_QUERY => true );
```

<strong>Tip:</strong>You can learn more details about PDO Predefined Constants.


### Connection Tutorial <a name='connection-tutorial'></a>

------

Putting this code into your Controller enough for the current database connection.

```php
<?php

Class Welcome extends Controller
{
    /**
     * Loader
     * 
     * @return void
     */
    public function load()
    {
        $this->c->load('view');
        $this->c->load('service/provider/database as db');   // create a database connection
    }

    /**
     * Index
     * 
     * @return void
     */
    public function index($t = false)
    {
        $this->view->load(
            'welcome',
            function () {
                $this->db->query('SELECT * FROM %s', array('users'));
                $results = $this->db->resultArray();

                print_r($results);
            }
        );
    }
}

/* End of file hello_world.php */
/* Location: .public/tutorials/controller/hello_world.php */
```

### Generating Query Results  <a name="generating-query-results"></a>

------

#### $this->db->count();

Count number of rows and returns to integer value.

#### $this->db->result();

This function returns the query result as object.

#### $this->db->result(true);

This function same as result() except <b>returns to empty array</b> if result false.

#### $this->db->resultArray();

This function returns the query result as a pure array, or an empty array when no result is produced.

#### $this->db->resultArray(true);

This function same as resultArray() except <b>returns to empty array</b> if result false.

#### $this->db->row();

This function fetches one item and returns query result as object or false on failure.

#### $this->db->row(true);

This function same as row() except <b>returns to empty array</b> if result false.

#### $this->db->rowArray();

Identical to the above row() function, except it returns an array.

#### $this->db->rowArray(true);

This function same as rowArray() except <b>returns to empty array</b> if result false.

#### $this->db->lastQuery();

Returns to last executed sql string.


### Insert, Update, Delete Operations <a name="insert-update-delete"></a>

------

Default db object contains <b>insert</b>, <b>update</b> and <b>delete</b> methods which allows build safer queries with auto escape.

These methods simply build sql queries without effect your application performance.

### Insert

#### $this->db->insert(string $tablename, array $data);

```php
<?php

echo $this->db->insert('users', array('username' => 'John', 'date' => time()));  //  gives 1 ( affected rows )

// query : INSERT INTO users (username, date) VALUES ('John', 1401970851)
```

### Update

#### $this->db->update(string $tablename, array $data, array $where = array(), $extraSql = '', integer $limit = '');

```php
<?php

$data = array(
    'username' => 'Bob',
    'modification_date' => time()
)
echo $this->db->update('users', $data, array('user_id' => 5));  //  gives 1 ( affected rows )

// query : UPDATE users SET username = 'Bob' modification_date = 1401970851 WHERE user_id = 5
```

### Delete

#### $this->db->delete(string $tablename, array $where = array(), string $extraSql = '', integer $limit = '');

```php
<?php

$this->db->delete('users', array('user_id' => 5));  //  gives 1 ( affected rows )

// query : DELETE FROM users WHERE user_id = 5

$this->db->delete('users', array('user_id' => 5), array(), 1);

// query : DELETE FROM users WHERE user_id = 5 LIMIT 1

$this->db->delete('users', array(), ' WHERE username LIKE '.$this->db->escapeLike('test'));

// query : DELETE FROM users WHERE username LIKE '%test%'
```

Above the methods don't protect your identifiers. ( `tablename`, `column names` ..  ) If you need to advanced functionalities you can prefer <b>Crud</b> class.

Crud class will replace your database object with crud functions also protect your sql identifiers.

Forexample

```php
<?php

$this->c->load('crud');
$this->db->where('userd_id', 5);
$this->db->update('users');         // query : UPDATE `users` SET `user_id` = 5;

$this->db->where('userd_id', 5);
$this->db->get('users');            // query : SELECT * FROM `users` WHERE `user_id` = 5;

pirnt_r($this->db->resultArray());
```

#### Testing Results

```php
<?php

$this->db->query('YOUR QUERY');

if ($this->db->count() > 0) {
   $row = $this->db->rowArray();
   echo $row['title'];
   echo $row['name'];
   echo $row['body'];
} 
```

#### Testing Results with Crud

------

```php
<?php

$this->c->load('crud');

$this->db->where('user_id', 5)->get('users');

if ($this->db->count() !== false) {
    $b = $this->db->resultArray();
    print_r($b);    // output array( ... )   
}
```

## Running and Escaping Queries <a name="running-queries"></a>

### Direct Query

------

To submit a query, use the following function:

```php
<?php

$this->db->query('YOUR QUERY HERE');
```

The <dfn>query()</dfn> function returns a database result **object** when "read" type queries are run, which you can use to show your results. When retrieving data you will typically assign the query to your own variable, like this:

```php
<?php

$query = $this->db->query('YOUR QUERY HERE');
```

### Exec Query

------

This query type same as direct query just it returns the $affected_rows automatically. You should use **execQuery** function for INSERT, UPDATE, DELETE operations.

```php
<?php

$affectedRows = $this->db->exec('INSERT QUERY'); 

echo $affectedRows;   //output  1
```

### Escaping Queries

------

It's a very good security practice to escape your data before submitting it into your database. Obullo has three methods that help you do this:

#### $this->db->escape()

This function determines the data type so that it can escape only string data. It also automatically adds single quotes around the data and it can automatically determine data types. 

```php
<?php

$sql = "INSERT INTO table (title) VALUES(".$this->db->escape((string)$title).")";
```

Supported data types: <samp>(int), (string), (boolean)</samp>

#### $this->escapeStr();

This function escapes the data passed to it, regardless of type. Most of the time you'll use the above function rather than this one. Use the function like this:

```php
<?php

$sql = "INSERT INTO table (title) VALUES('".$this->db->escapeStr($title)."')";
```

#### $this->db->escapeLike()

This method should be used when strings are to be used in LIKE conditions so that LIKE wildcards ('%', '_') in the string are also properly escaped. 

```php
<?php

$search = '20% raise';
$sql = "SELECT id FROM table WHERE column LIKE '%".$this->db->escapeLike($search)."%'";
```

**Note:** You don't need to **$this->escapeLike** function when you use Crud class because of it do auto escape for like conditions.

```php
<?php

$this->db->select("*");
$this->db->like('article','%%blabla')
$this->db->orLike('article', 'blabla')
$query = $this->db->get('articles');

echo $query->lastQuery();

// Output
```

## Query Binding <a name="query-binding"></a>

------

Framework offers PDO bindValue functionality, using query binding will help you for the performance and security:

#### Bind Types

<table>
    <thead>
        <tr>
            <th>Friendly Constant</th>
            <th>PDO CONSTANT</th>
            <th>Description</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td>PARAM_BOOL</td>
            <td>PDO::PARAM_BOOL</td>
            <td>Boolean</td>
        </tr>
        <tr>
            <td>PARAM_NULL</td>
            <td>PDO::PARAM_NULL</td>
            <td>NULL</td>
        </tr>
        <tr>
            <td>PARAM_INT</td>
            <td>PDO::PARAM_INT</td>
            <td>String</td>
        </tr>
        <tr>
            <td>PARAM_STR</td>
            <td>PDO::PARAM_STR</td>
            <td>Integer</td>
        </tr>
        <tr>
            <td>PARAM_LOB</td>
            <td>PDO::PARAM_LOB</td>
            <td>Large Object Data (LOB)</td>
        </tr>
    </tbody>
</table>

The **double dots** in the query are automatically replaced with the values of **bindValue** functions.

#### Bind Example

##### $this->db->bindValue($paramater, $variable, $type)

### Question Mark Binding

```php
<?php

$this->db->prepare("SELECT * FROM %s WHERE %s = ? OR %s = ?", array('articles', 'article_id', 'tag'));
$this->db->bindValue(1, 1, PARAM_INT);  
$this->db->bindValue(2,'php', PARAM_STR); 
$this->db->execute();

$a = $this->db->result(); 
var_dump($a);
```

### Colon Binding

```php
<?php

$this->db->prepare("SELECT * FROM %s WHERE %s = :id OR %s = :tag", array('articles', 'article_id', 'tag'));
$this->db->bindValue(':id', 1, PARAM_INT);  
$this->db->bindValue(':tag', 'php', PARAM_STR); 
$this->db->execute();

$a = $this->db->resultArray(); 
print_r($a);
```

The using **colons** in the query are automatically replaced with the values of **bindValue** methods.

**Note:**  The secondary benefit of using binds is that the values are automatically escaped, producing safer queries. You don't have to remember to manually escape data; the engine does it automatically for you.


### Array Binding

```php
<?php

$this->db->prepare("SELECT * FROM articles WHERE article_id = ? OR tag = ?");
$this->db->execute(array(1, 'php'));

$a = $this->db->result(); 
var_dump($a);
```

## Query Helper Functions<a name="query-helper-functions"></a>

------

#### $this->db->insertId()

The insert ID number when performing database inserts.

#### $this->db->getDrivers()

Outputs the database platform you are running (MySQL, MS SQL, Postgres, etc...):

```php
<?php

$drivers = $this->db->getDrivers();   print_r($drivers);  // Array ( [0] => mssql [1] => mysql [2] => sqlite2 )
```
 
#### $this->db->getVersion()

Outputs the database version you are running (MySQL, MS SQL, Postgres, etc...):

```php
<?php

echo $this->db->getVersion(); // output like 5.0.45 or returns to null if server does not support this feature..
```

#### $this->db->isConnected()

Checks the database connection is active or not

```php
<?php

echo $this->db->isConnected(); // output 1 or 0
```

#### $this->db->lastQuery();

Returns the last query that was run (the query string, not the result). Example:

```php
<?php

$str = $this->db->lastQuery();
```

#### $this->db->setAttribute($key, $val);

Sets PDO connection attribute.

```php
<?php

$this->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);

$this->db->query(" .. ");

print_r($this->db->errorInfo());  // handling pdo errors

$this->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION); // restore error mode
```

#### $this->db->errorInfo();

Gets the database errors in pdo slient mode instead of getting pdo exceptions.

The following two functions help simplify the process of writing database INSERT s and UPDATE s.

## Database Transactions <a name="transactions"></a>

### Transactions 

------

Database abstraction allows you to use transactions with databases that support transaction-safe table types. In MySQL, you'll need to be running <b>InnoDB</b> or <b>BDB</b> table types rather than the more common MyISAM. Most other database platforms support transactions natively.

If you are not familiar with transactions we recommend you find a good online resource to learn about them for your particular database. The information below assumes you have a basic understanding of transactions.


#### Auto Transaction

Auto transaction execute begin transaction, commit and rollBack operations within a closure function.

```php
<?php

$e = $this->db->transaction(
    function () {
        $this->db->query("INSERT INTO persons (person_type, person_name) VALUES ('clever', 'john')");
    }
);

if ($e !== true) {
    echo $e->getMessage();  // Returns to exceptional message.
}
```

#### Manual Transaction

To run your queries using transactions manually you need to use <kbd>$this->db->transaction()</kbd>, <kbd>$this->db->commit()</kbd> and <kbd>$this->db->rollBack()</kbd> methods as follows:

```php
<?php

try {
    
    $this->db->transaction(); // begin the transaction
    
    // INSERT statements
    
    $this->db->query("INSERT INTO persons (person_type, person_name) VALUES ('lazy', 'ersin')");
    $this->db->query("INSERT INTO persons (person_type, person_name) VALUES ('clever', 'john')");
    $this->db->query("INSERT INTO persons (person_type, person_name) VALUES ('funny', 'bob')");

    $this->db->commit();    // commit the transaction

    echo 'Data entered successfully'; // echo a message to say the database was created

} catch(Exception $e)
{    
    $this->db->rollBack();       // roll back the transaction if we fail
    echo $e->getMessage();  // echo exceptional error message
}
```

You can run as many queries as you want between the transaction/commit functions and they will all be committed or rolled back based on success or failure of any given query.

### Running Transactions with Multiple Operations

Also you use active record class like this

```php
<?php

$e = $this->db->transaction(
    function () {
        $this->db->insert('persons', array('person_type' => 'lazy', 'person_name' => 'ersin'));
        $this->db->insert('persons', array('person_type' => 'clever','person_name' => 'john'));
        $this->db->insert('persons', array('person_type' => 'funny','person_name' => 'bob'));
    }
);

if ($e !== true) {
    echo $e->getMessage();  // Returns to exceptional message.
}
```