
## Database

------

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

Open <kbd>Classes/Service/Provider/Db.php</kbd> and set your <u>Database Driver</u> like below the example example ( Default Mysql ).

```php
<?php

namespace Service;

/**
 * Db Provider
 *
 * @category  Provider
 * @package   Db
 * @author    Obullo Framework <obulloframework@gmail.com>
 * @copyright 2009-2014 Obullo
 * @license   http://opensource.org/licenses/MIT MIT license
 * @link      http://obullo.com/docs/providers
 */
Class Db implements ProviderInterface
{
    /**
     * Registry
     *
     * @param object $c container
     * 
     * @return void
     */
    public function register($c)
    {
        // ....
    }
}

// END Db class

/* End of file Db.php */
/* Location: .classes/Service/Provider/Db.php */
```

### Using Database Service

In your controller

```php
<?php
$this->c['db'];
$this->db->query('...');
```

In your classes

```php
<?php
$this->db = $this->c['return db'];

$this->db->query('...');
```

Creating new Pdo provider

```php
<?php
$this->c['new service provider pdo as anydb', array('db' => 'anydb')];

$this->anydb->query('...');
```

Using different driver


```php
<?php
$this->c['return new service/provider/db as anydb', array('db' => 'anydb', 'provider' => 'pgsql')];

$this->anydb->query('...');
```


To set your database configuration edit your <kbd>app/config/env/local/config.php</kbd>.

```php
<?php
/*
|--------------------------------------------------------------------------
| Database
|--------------------------------------------------------------------------
| Configuration file
|
*/
return array(
    
    'default' => array(
        'provider' => 'mysql',
    ),

    'handlers' => array(
        'mysql' => '\\Obullo\Database\Pdo\Handler\Mysql',
        'pgsql' => '\\Obullo\Database\Pdo\Handler\Pgsql',
        'yourhandler' => '\\Obullo\Database\Pdo\Handler\YourHandler',
    ),

    'key' => array(

        'db' => array(
            'host' => 'localhost',
            'username' => env('MYSQL_USERNAME'),
            'password' => env('MYSQL_PASSWORD', '', false),
            'database' => 'test',
            'port'     => '',
            'charset'  => 'utf8',
            'dsn'      => '',
            'pdo'      => array(
                'options'  => array()
            ),
        ),
    )

);

/* End of file database.php */
/* Location: .app/env/local/database.php */
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

    'connections' => array(

        'db' => array(
            'host' => 'localhost',
            'username' => env('MYSQL_USERNAME'),
            'password' => env('MYSQL_PASSWORD', '', false),
            'database' => 'test',
            'port'     => '',
            'charset'  => 'utf8',
            'dsn'      => '',
            'pdo'      => array(
                'options'  => array()
            ),
        ),

        'dbAny' => array(            // another database configuration
            'host'     => 'localhost',
            'username' => env('MYSQL_USERNAME'),
            'password' => env('MYSQL_PASSWORD', '', false),,
            ...
            ),
        ),

    )

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
* autoinit - Whether to set charset or bufferedQuery option when connection established.
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

namespace Welcome;

Class Welcome extends \Controller
{
    /**
     * Loader
     * 
     * @return void
     */
    public function load()
    {
        $this->c['view'];
        $this->c['db'];   // create a database connection
    }

    /**
     * Index
     * 
     * @return void
     */
    public function index()
    {
        $this->db->query('SELECT * FROM %s WHERE id = ?', array('users'), array(5));
        $results = $this->db->rowArray();

        print_r($results);
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


### Select Queries

------

```php
<?php
$this->db->query();
```

### Insert, Update, Delete Operations <a name="insert-update-delete"></a>

------

Default db object contains <b>insert</b>, <b>update</b> and <b>delete</b> methods which allows build safer queries with auto escape.

These methods simply build sql queries without effect your application performance.

### Insert

#### $this->db->query("INSERT INTO table %s", [['@insert' => $data]]);

```php
<?php
$data = array('username' => 'John', 'date' => time());

echo $this->db->query("INSERT INTO users %s", array(['@insert' => $data])); //  gives 1 ( affected rows )

// query : INSERT INTO users (username, date) VALUES ('John', 1401970851)
```

### Update

#### $this->db->query("UPDATE table SET %s WHERE id = ?", array(['@update' => $data]), [$id]);

```php
<?php

$data = array(
    'title' => 'Welcome',
    'content' => "Bob's Content",
)
$this->db->query("UPDATE entries SET %s WHERE entry_id = ?", array(['@update' => $data]), [4]);

// query : UPDATE users SET title='Welcome', content='Bob\'s Content' WHERE entry_id = 4
```

### Delete

#### $this->db->query("DELETE FROM table WHERE id = ?", array(), [$id]);

```php
<?php
$this->db->query("DELETE FROM table WHERE id IN (%s)", array(['@in' => [1,2,3]]));

// query: DELETE FROM users WHERE id IN ('1','2','3')
```

```php
<?php
$this->db->query("DELETE FROM table WHERE id = ? OR username = ?", array(), [1, 'john']);

// query: DELETE FROM users WHERE id = 1 OR username = 'john'
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


### Query Binding

------

```php
<?php
$this->db->query("SELECT * FROM %s LIMIT ?", array('users'), array(10));
```

### Insert

$this->db->query(
    'INSERT INTO users (%s,%s,%s) VALUES (?,?,?)', ['username','email', $this->db->protect('date')],
    [
        $this->username, 
        $this->email, 
        $this->date
    ]
);


### Manually Escaping Queries

------

It's a very good security practice to escape your data before submitting it into your database. Obullo has three methods that help you do this:

#### $this->db->escape(mixed $data)

This function determines the data type so that it can escape only string data. It also automatically adds single quotes around the data and it can automatically determine data types. 

```php
<?php
$title = 'Welcome';
$content = "John's blog";

$this->db->query(
    "INSERT INTO table (title, content) VALUES ("
    .$this->db->escape($title)
    .",".
    $this->db->escape($content)
    .')'
);

// INSERT INTO table (title, content) VALUES ('Welcome','John\'s blog')
```

## Traditional Query Binding <a name="query-binding"></a>

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

#### Traditional Bind Example

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
    

        $this->db->query(
            'INSERT INTO users (%s,%s,%s) VALUES (?,?,?)', ['username','email', $this->db->protect('date')],
            [
                $this->username, 
                $this->email, 
                $this->date
            ]
        );

    // $this->db->query("INSERT INTO persons (%s) VALUES (%s)", array(['@col' => $col], ['@val' => ['lazy','Jack']]));
    // $this->db->query("INSERT INTO persons (%s) VALUES (%s)", array(['@col' => $col], ['@val' => ['clever','Steve']]));
    // $this->db->query("INSERT INTO persons (%s) VALUES (%s)", array(['@col' => $col], ['@val' => ['beatiful','Alex']]));

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

$col = array('person_type', 'person_name');

$e = $this->db->transaction(
    function () use ($col) {
        // $this->db->query("INSERT INTO persons (%s) VALUES (%s)", array(['@col' => $col], ['@val' => ['lazy','Jack']]));
        // $this->db->query("INSERT INTO persons (%s) VALUES (%s)", array(['@col' => $col], ['@val' => ['clever','Steve']]));
        // $this->db->query("INSERT INTO persons (%s) VALUES (%s)", array(['@col' => $col], ['@val' => ['beatiful','Alex']]));
    }
);

if ($e !== true) {
    echo $e->getMessage();  // Returns to exceptional message.
}
```