
## Tree Db Class

------

Tree class use nested set model. It is a particular technique for representing nested sets (also known as trees or hierarchies) in relational databases. The term was apparently introduced by Joe Celko; others describe the same technique without naming it or using different terms.

<a href="http://ftp.nchu.edu.tw/MySQL/tech-resources/articles/hierarchical-data.html">http://ftp.nchu.edu.tw/MySQL/tech-resources/articles/hierarchical-data.html</a>

### Initializing the Class

------

```php
<?php
$c->load('tree/db');
$this->treeDb->setTablename('categories');
```

### Using different database Object

Using second parameter you can choose a different database object.

```php
<?php
$c->load('tree/db', $c->load('service/provider/db'));
```

### Running SQL Code

First of all run below the sql query this will create the nested tree. 

```php
CREATE TABLE categories (
	category_id INT AUTO_INCREMENT PRIMARY KEY,
	name VARCHAR(20) NOT NULL,
	lft INT NOT NULL,
	rgt INT NOT NULL
);
```

Don't forget to add some indexes on your tables to speed up the read process. You should add indexes for parent_id, lft and rgt columns:

```php
ALTER TABLE  `categories` ADD INDEX  `lft` (  `lft` );
ALTER TABLE  `categories` ADD INDEX  `rgt` (  `rgt` );
```

### Add (first) root category

#### $this->treeDb->addTree(string $text);

Adds the main category to the table.

```php
<?php
$this->treeDb->addTree('Electronics');
```
Gives

```php
+-------------+----------------------+-----------+-----+-----+
| category_id | name                 | parent_id | lft | rgt |
+-------------+----------------------+-----------+-----+-----+
|           1 | Electronics          | 0  	     |  1  |  2  |
+-------------+----------------------+-----------+-----+-----+
```

#### $this->treeDb->addTree(string $text, $extra = array());

Adds to extra column data to table.

```php
<?php
$this->treeDb->addTree('Electronics', $extra = array('column' => 'value'));
```
Gives

```php
+-------------+----------------------+-----------+-----+-----+--------+
| category_id | name                 | parent_id | lft | rgt | column |
+-------------+----------------------+-----------+-----+-----+--------+
|           1 | Electronics          | 0 		 |  1  |  2  |  value |
+-------------+----------------------+-----------+-----+-----+--------+
```

### Adding nodes

#### $this->treeDb->addChild(int $category_id, string $text, $extra = array());

Inserts a new node as the first child of the supplied parent node.

```php
<?php
$this->treeDb->addChild($category_id = 1, 'Televisions');
```
Gives

```php
+-------------+----------------------+-----------+-----+-----+
| category_id | name                 | parent_id | lft | rgt |
+-------------+----------------------+-----------+-----+-----+
|           1 | Electronics          | 0		 |  1  |  4  |
|           2 | Televisions          | 1 		 |  2  |  3  |
+-------------+----------------------+-----------+-----+-----+
```

Let's add a Portable Electronics node as child of 

```php
<?php
$this->treeDb->addChild($category_id = 1, 'Portable Electronics');
```

Gives

```php
+-------------+----------------------+-----------+-----+-----+
| category_id | name                 | parent_id | lft | rgt |
+-------------+----------------------+-----------+-----+-----+
|           1 | Electronics          | 0 		 |  1  |  6  |
|           3 | Portable Electronics | 1 		 |  2  |  3  |
|           2 | Televisions          | 1 		 |  4  |  5  |
+-------------+----------------------+-----------+-----+-----+
```

#### $this->treeDb->appendChild(int $category_id, string $text, $extra = array());

Same as addChild except the new node is added as the last child.

```php
<?php
$this->treeDb->appendChild($category_id = 2, 'Lcd');
```
Gives

```php
+-------------+----------------------+-----------+-----+-----+
| category_id | name                 | parent_id | lft | rgt |
+-------------+----------------------+-----------+-----+-----+
|           1 | Electronics          | 0		 |  1  |  8  |
|           3 | Portable Electronics | 1 		 |  2  |  3  |
|           2 | Televisions          | 1 		 |  4  |  7  |
|           4 | Lcd				 	 | 2 		 |  5  |  6  |
+-------------+----------------------+-----------+-----+-----+
```

#### $this->treeDb->addSibling(int $category_id, string $text, $extra = array());

Inserts a new node as the first sibling of the supplied parent node.

```php
<?php
$this->treeDb->addSibling($category_id = 4, 'Tube');
```
Gives

```php
+-------------+----------------------+-----------+-----+-----+
| category_id | name                 | parent_id | lft | rgt |
+-------------+----------------------+-----------+-----+-----+
|           1 | Electronics          | 0		 |  1  |  10 |
|           3 | Portable Electronics | 1		 |  2  |  3  |
|           2 | Televisions          | 1		 |  4  |  9  |
|           5 | Tube				 | 2 		 |  5  |  6  |
|           4 | Lcd					 | 2		 |  7  |  8  |
+-------------+----------------------+-----------+-----+-----+
```

#### $this->treeDb->appendSibling(int $category_id, string $text, $extra = array());

Inserts a new node as the last sibling of the supplied parent node.

```php
<?php
$this->treeDb->appendSibling($category_id = 4, 'Plasma');
```
Gives

```php
+-------------+----------------------+-----------+-----+-----+
| category_id | name                 | parent_id | lft | rgt |
+-------------+----------------------+-----------+-----+-----+
|           1 | Electronics          | 0		 |  1  |  12 |
|           3 | Portable Electronics | 1		 |  2  |  3  |
|           2 | Televisions          | 1		 |  4  |  11 |
|           5 | Tube				 | 2		 |  5  |  6  |
|           4 | Lcd					 | 2		 |  7  |  8  |
|           6 | Plasma				 | 2 		 |  9  |  10 |
+-------------+----------------------+-----------+-----+-----+
```

** NOTE: **
This function added "Plasma" as sibling to "Lcd". If we wanted to add "Plasma" as sibling to "Lcd" we should set the value of the second parameter "Tube's" which represents the value of "rgt".

#### $this->treeDb->deleteNode(int $category_id);

Deletes the given node (and any children) from the tree table.

```php
<?php
$this->treeDb->deleteNode($category_id = 5); // deletes "Tube"
```
Gives

```php
+-------------+----------------------+-----------+-----+-----+
| category_id | name                 | parent_id | lft | rgt |
+-------------+----------------------+-----------+-----+-----+
|           1 | Electronics          | 0		 |  1  |  10 |
|           3 | Portable Electronics | 1		 |  2  |  3  |
|           2 | Televisions          | 1		 |  4  |  9  |
|           4 | Lcd					 | 2		 |  5  |  6  |
|           6 | Plasma				 | 2		 |  7  |  8  |
+-------------+----------------------+-----------+-----+-----+
```

#### $this->treeDb->updateNode($category_id, $data = array());

Updates your table row data using the primary key ( category_id ).

```php
<?php
$this->treeDb->updateNode($id = 2, array('name' => 'TV', 'column' => 'test'));
```
Gives

```php
+-------------+----------------------+-----------+-----+-----+--------+
| category_id | name                 | parent_id | lft | rgt | column |
+-------------+----------------------+-----------+-----+-----+--------+
|           1 | Electronics          | 0		 |  1  |  10 |		  |
|           3 | Portable Electronics | 1		 |  2  |  3  |		  |
|           2 | TV		             | 1		 |  4  |  9  | test   |
|           4 | Lcd					 | 2		 |  5  |  6  |		  |
|           6 | Plasma				 | 2		 |  7  |  8  |		  |
+-------------+----------------------+-----------+-----+-----+--------+
```

#### $this->treeDb->moveAsFirstChild($sourceId, $targetId);

Move as first child.

Before move operation our current table.

```php
+-------------+----------------------+-----------+-----+-----+
| category_id | name                 | parent_id | lft | rgt |
+-------------+----------------------+-----------+-----+-----+
|           1 | Electronics          | 0		 |  1  |  16 |
|           2 | Portable Electronics | 1		 |  2  |  7  |
|           3 | Flash				 | 2		 |  3  |  4  |
|           4 | Mp3 Player			 | 2		 |  5  |  6  |
|           5 | Televisions          | 1 		 |  8  |  15 |
|           6 | Lcd					 | 5		 |  9  |  10 |
|           7 | Tube				 | 5		 |  11 |  12 |
|           8 | Plasma				 | 5		 |  13 |  14 |
+-------------+----------------------+-----------+-----+-----+
```

We want to move "Portable Electronics" under the "Televisions" to be the first child.

```php
<?php
$sourceId = 2; // Portable Electronics primary key (category_id)
$targetId = 5; // Televisions primary key (category_id)

$this->treeDb->moveAsFirstChild($sourceId, $targetId);
```

After the move operation.

Gives

```php
+-------------+----------------------+-----------+-----+-----+
| category_id | name                 | parent_id | lft | rgt |
+-------------+----------------------+-----------+-----+-----+
|           1 | Electronics          | 0		 |  1  |  16 |
|           5 | Televisions          | 1		 |  2  |  15 |
|           2 | Portable Electronics | 5		 |  3  |  8  |
|           3 | Flash				 | 2		 |  4  |  5  |
|           4 | Mp3 Player			 | 2		 |  6  |  7  |
|           6 | Lcd					 | 5		 |  9  |  10 |
|           7 | Tube				 | 5		 |  11 |  12 |
|           8 | Plasma				 | 5		 |  13 |  14 |
+-------------+----------------------+-----------+-----+-----+
```

#### $this->treeDb->moveAsPrevSibling($sourceId, $targetId);

Move as prev sibling.

Before move operation our current table.

```php
+-------------+----------------------+-----------+-----+-----+
| category_id | name                 | parent_id | lft | rgt |
+-------------+----------------------+-----------+-----+-----+
|           1 | Electronics          | 0		 |  1  |  16 |
|           5 | Televisions          | 1		 |  2  |  15 |
|           2 | Portable Electronics | 5		 |  3  |  8  |
|           3 | Flash				 | 2		 |  4  |  5  |
|           4 | Mp3 Player			 | 2		 |  6  |  7  |
|           6 | Lcd					 | 5		 |  9  |  10 |
|           7 | Tube				 | 5		 |  11 |  12 |
|           8 | Plasma				 | 5		 |  13 |  14 |
+-------------+----------------------+-----------+-----+-----+
```

We want to move "Portable Electronics" as a previous sibling of "Televisions"

```php
<?php
$sourceId = 2; // Portable Electronics primary key (category_id)
$targetId = 5; // Televisions primary key (category_id)

$this->treeDb->moveAsPrevSibling($sourceId, $targetId);
```

After the move operation.

Gives

```php
+-------------+----------------------+-----------+-----+-----+
| category_id | name                 | parent_id | lft | rgt |
+-------------+----------------------+-----------+-----+-----+
|           1 | Electronics          | 0		 |  1  |  16 |
|           2 | Portable Electronics | 1		 |  2  |  7  |
|           3 | Flash				 | 2		 |  3  |  4  |
|           4 | Mp3 Player			 | 2		 |  5  |  6  |
|           5 | Televisions          | 1		 |  8  |  15 |
|           6 | Lcd					 | 5		 |  9  |  10 |
|           7 | Tube				 | 5		 |  11 |  12 |
|           8 | Plasma				 | 5		 |  13 |  14 |
+-------------+----------------------+-----------+-----+-----+
```

#### $this->treeDb->moveAsLastChild($sourceId, $targetId);

Move as last child.

Before move operation our current table.

```php
+-------------+----------------------+-----------+-----+-----+
| category_id | name                 | parent_id | lft | rgt |
+-------------+----------------------+-----------+-----+-----+
|           1 | Electronics          | 0		 |  1  |  16 |
|           2 | Portable Electronics | 1		 |  2  |  7  |
|           3 | Flash				 | 2		 |  3  |  4  |
|           4 | Mp3 Player			 | 2		 |  5  |  6  |
|           5 | Televisions          | 1		 |  8  |  15 |
|           6 | Lcd					 | 5		 |  9  |  10 |
|           7 | Tube				 | 5		 |  11 |  12 |
|           8 | Plasma				 | 5		 |  13 |  14 |
+-------------+----------------------+-----------+-----+-----+
```

We want to move "Portable Electronics" under the "Televisions" as a last child.

```php
<?php
$sourceId = 2; // Portable Electronics primary key (category_id)
$targetId = 5; // Televisions primary key (category_id)

$this->treeDb->moveAsLastChild($sourceId, $targetId);
```

After the move operation.

Gives

```php
+-------------+----------------------+-----------+-----+-----+
| category_id | name                 | parent_id | lft | rgt |
+-------------+----------------------+-----------+-----+-----+
|           1 | Electronics          | 0		 |  1  |  16 |
|           5 | Televisions          | 1		 |  2  |  15 |
|           6 | Lcd					 | 5		 |  3  |  4  |
|           7 | Tube				 | 5		 |  5  |  6  |
|           8 | Plasma				 | 5		 |  7  |  8  |
|           2 | Portable Electronics | 5		 |  9  |  14 |
|           3 | Flash				 | 2		 |  10 |  11 |
|           4 | Mp3 Player			 | 2		 |  12 |  13 |
+-------------+----------------------+-----------+-----+-----+
```

#### $this->treeDb->moveAsNextSibling($sourceId, $targetId);

Move as next sibling.

Before move operation our current table.

```php
+-------------+----------------------+-----------+-----+-----+
| category_id | name                 | parent_id | lft | rgt |
+-------------+----------------------+-----------+-----+-----+
|           1 | Electronics          | 0		 |  1  |  16 |
|           5 | Televisions          | 1		 |  2  |  15 |
|           6 | Lcd					 | 5		 |  3  |  4  |
|           7 | Tube				 | 5		 |  5  |  6  |
|           8 | Plasma				 | 5		 |  7  |  8  |
|           2 | Portable Electronics | 5		 |  9  |  14 |
|           3 | Flash				 | 2		 |  10 |  11 |
|           4 | Mp3 Player			 | 2		 |  12 |  13 |
+-------------+----------------------+-----------+-----+-----+
```

We want to move "Portable Electronics" as a next sibling of "Televisions" 

```php
<?php
$sourceId = 2; // Portable Electronics primary key (category_id)
$targetId = 5; // Televisions primary key (category_id)

$this->treeDb->moveAsNextSibling($sourceId, $targetId);
```

After the move operation.

Gives

```php
+-------------+----------------------+-----------+-----+-----+
| category_id | name                 | parent_id | lft | rgt |
+-------------+----------------------+-----------+-----+-----+
|           1 | Electronics          | 0		 |  1  |  16 |
|           5 | Televisions          | 1		 |  2  |  9  |
|           6 | Lcd					 | 5		 |  3  |  4  |
|           7 | Tube				 | 5		 |  5  |  6  |
|           8 | Plasma				 | 5		 |  7  |  8  |
|           2 | Portable Electronics | 1		 |  10 |  15 |
|           3 | Flash				 | 2		 |  11 |  12 |
|           4 | Mp3 Player			 | 2		 |  13 |  14 |
+-------------+----------------------+-----------+-----+-----+
```


#### $this->treeDb->truncateTable();

Truncate the table data.


### Querying Tree

------

#### $this->treeDb->getAllTree($select = 'category_id,name,parent_id');

Retrieving a Full Tree

We do not send anything. All the tree depth, is back with the parent id and name.

```php
<?php
print_r($this->treeDb->getAllTree());
```
Gives

```php
<?php
/*
Array
(
    [0] => Array
        (
            [category_id] => 1
            [name] => Electronics
            [parent_id] => 0
            [depth] => 0
        )

    [1] => Array
        (
            [category_id] => 2
            [name] => Portable Electronics
            [parent_id] => 1
            [depth] => 1
        )

    [2] => Array
        (
            [category_id] => 3
            [name] => Flash
            [parent_id] => 2
            [depth] => 2
        )
)
*/
```

#### $this->treeDb->getTree($nodeId = 1, $select = 'category_id,name');

Retrieving a Full Tree

We want to get the tree of "Electronics".

```php
<?php
print_r($this->treeDb->getTree($nodeId = 1, $select = 'category_id,name'));

// primary key value or text column value

print_r($this->treeDb->getTree($nodeId = 'Electronics'));
```
Gives (Same result)

```php
<?php
/*
Array
(
    [0] => Array
        (
            [category_id] => 1
            [name] => Electronics
        )
    [1] => Array
        (
            [category_id] => 2
            [name] => Portable Electronics
        )
)
*/
```

#### $this->treeDb->getDepthOfSubTree($nodeId = 1, $select = 'category_id,name');

Retrieves depth of sub categories.

```php
<?php
print_r($this->treeDb->getDepthOfSubTree($nodeId = 1, $select = 'category_id,name'));

// primary key value or text column value

print_r($this->treeDb->getDepthOfSubTree($nodeId = 'Electronics'));
```
Gives (Same result)

```php
<?php
/*
Array
(
    [0] => Array
        (
            [category_id] => 2
            [name] => Portable Electronics
            [depth] => 1
        )
    [1] => Array
        (
            [category_id] => 3
            [name] => Flash
            [depth] => 2
        )
    [2] => Array
        (
            [category_id] => 5
            [name] => Televisions
            [depth] => 1
        )
    [3] => Array
        (
            [category_id] => 6
            [name] => Lcd
            [depth] => 2
        )
)
*/
```

#### $this->treeDb->getSiblings($category_id = 2, $select = 'category_id,name');

We want to get "Portable Electronics" siblings.

```php
<?php
print_r($this->treeDb->getSiblings($nodeId = 2, $select = 'category_id,name'));

// primary key value or text column value

print_r($this->treeDb->getSiblings($nodeId = 'Portable Electronics'));
```
Gives

```php
<?php
/*
Array
(
    [0] => Array
        (
            [category_id] => 2
            [name] => Portable Electronics
        )

    [1] => Array
        (
            [category_id] => 5
            [name] => Televisions
        )

)
*/
```

#### $this->treeDb->getRoot();

We're just getting the all main tree.

```php
<?php
print_r($this->treeDb->getRoot());
```
Gives

```php
<?php
/*
Array
(
    [0] => Array
        (
            [category_id] => 1
            [name] => Electronics
        )
)
*/
```

#### $this->treeDb->getStatement();

Get PDO Statement Object

```php
<?php
print_r($this->treeDb->getStatement());
```
Gives

```php
<?php
/*
PDOStatement Object
(
    [queryString] => INSERT INTO foo (`parent_id`,`name`,`lft`,`rgt`) VALUES (?,?,?,?);
)
*/
```

### Function Reference

------

#### $this->treeDb->setEscapeChar(string $char = '`');

Allows set escape character to protect database column identifiers. It depends on your database driver.

#### $this->treeDb->setTablename(string $tablename = 'nested_category');

Set table name overridding to default value.

#### $this->treeDb->setPrimaryKey(string $primaryKey = 'category_id');

Set primary key column name overridding to default value.

#### $this->treeDb->setText(string $text = 'name');

Set text column name overridding to default value.

#### $this->treeDb->setLft(string $lft = 'lft');

Set left column name overridding to default value.

#### $this->treeDb->setRgt(string $rgt = 'rgt');

Set right column name overridding to default value.

#### $this->treeDb->addTree(string $text, $extra = array());

Adds the first entry to the table.

#### $this->treeDb->addChild(int $lftValue, string $text, $extra = array());

Inserts a new node as the first child of the supplied parent node.

#### $this->treeDb->appendChild(int $rgtValue, string $text, $extra = array());

Same as addChild except the new node is added as the last child.

#### $this->treeDb->addSibling(int $lftValue, string $text, $extra = array());

Inserts a new node as the first sibling of the supplied parent node.

#### $this->treeDb->appendSibling(int $rgtValue, string $text, $extra = array());

Inserts a new node as the last sibling of the supplied parent node.

#### $this->treeDb->deleteChild(int $lftValue, int $rgtValue);

Deletes the given node (and any children) from the tree table.

#### $this->treeDb->updateNode($category_id, $data = array());

Updates your table row using the primary key ( category_id ).

#### $this->treeDb->truncateTable();

Truncate the table data.

#### $this->treeDb->moveAsFirstChild(array $source, array $target);

Set node as first child.

#### $this->treeDb->moveAsPrevSibling(array $source, array $target);

Set node as prev sibling.

#### $this->treeDb->moveAsLastChild(array $source, array $target);

Set node as last child.

#### $this->treeDb->moveAsNextSibling(array $source, array $target);

Set node as next sibling.

#### $this->treeDb->getAllTree(string $select = 'category_id,name,parent_id');

Retrieving a Full Tree

#### $this->treeDb->getTree(int $nodeId = 1, string $select = 'category_id,name');

Retrieving a Full Tree

#### $this->treeDb->getDepthOfSubTree(int $nodeId = 1, string $select = 'category_id,name');

Retrieves depth of sub categories.

#### $this->treeDb->getSiblings(int $category_id = 2, string $select = 'category_id,name');

We want to get "Portable Electronics" siblings.

#### $this->treeDb->getRoot();

We're just getting the all main tree.

#### $this->treeDb->getStatement();

Get PDO Statement Object