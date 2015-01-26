
## Permissions Class

------

The Rbac Permissions class execute add, update, delete, move methods on permission tree. Also assign / de-assign given permission and role ids to permission table.

### Initializing the Rbac Service

------

```php
<?php
$c->load('service/rbac', $c->load('return service/provider/db'));
$this->rbac->permissions->method();
```

### Adding Operations.

------

#### Add Root

#### $this->rbac->permissions->addRoot(string $permName, $extra = array());

```php
<?php
$this->rbac->permissions->addRoot($permName = 'marketing' => array('perm_resource' => 'admin/marketing', 'perm_type' => 'page');
```
Gives

```php
+-------------+-----------+----------------------+------------------------+-----------+-----+-----+
| perm_id     | parent_id | perm_name            | perm_resource          | perm_type | lft | rgt |
+-------------+-----------+----------------------+------------------------+-----------+-----+-----+
| 1           | 0  	      | marketing            | admin/marketing        | page      |  1  |  2  |
+-------------+-----------+----------------------+------------------------+-----------+-----+-----+
```

#### $this->rbac->permissions->append($permId, $permName, $extra = array());

##### We define new permissions under "marketing".

```php
<?php
$this->rbac->permissions->append($permId = 1, $permName = 'marketing_view', array('perm_resource' => 'admin/marketing/view', 'perm_type' => 'page'));
$this->rbac->permissions->append($permId = 1, $permName = 'marketing_edit', array('perm_resource' => 'admin/marketing/edit', 'perm_type' => 'page'));
$this->rbac->permissions->append($permId = 1, $permName = 'marketing_delete', array('perm_resource' => 'admin/marketing/delete', 'perm_type' => 'page'));
```
Gives

```php
+-------------+-----------+----------------------+------------------------+-----------+-----+-----+
| perm_id     | parent_id | perm_name            | perm_resource          | perm_type | lft | rgt |
+-------------+-----------+----------------------+------------------------+-----------+-----+-----+
|1            | 0         | marketing            | admin/marketing/index  | page      |  1  |  8  |
|2            | 1         | marketing_view       | admin/marketing/view   | page      |  2  |  3  |
|3            | 1         | marketing_edit       | admin/marketing/edit   | page      |  4  |  5  |
|4            | 1         | marketing_delete     | admin/marketing/delete | page      |  6  |  7  |
+-------------+-----------+----------------------+------------------------+-----------+-----+-----+
```

##### We define new permissions for "form" under "marketing".

```php
<?php
$this->rbac->permissions->append($permId = 1, $permName = 'form_1', array('perm_resource' => 'admin/marketing/index', 'perm_type' => 'object'));
```
Let's add an "Operations" node under the "form_1".

```php
<?php
$this->rbac->permissions->append($permId = 5, $permName = 'username', array('perm_resource' => 'admin/marketing/index', 'perm_type' => 'object'));
$this->rbac->permissions->append($permId = 5, $permName = 'password', array('perm_resource' => 'admin/marketing/index', 'perm_type' => 'object'));
$this->rbac->permissions->append($permId = 5, $permName = 'email, array('perm_resource' => 'admin/marketing/index', 'perm_type' => 'object'));
```

Gives

```php
+-------------+-----------+----------------------+------------------------+-----------+-----+-----+
| perm_id     | parent_id | perm_name            | perm_resource          | perm_type | lft | rgt |
+-------------+-----------+----------------------+------------------------+-----------+-----+-----+
|1            | 0         | marketing            | admin/marketing/index  | page      |  1  |  15 |
|2            | 1         | marketing_view       | admin/marketing/view   | page      |  2  |  3  |
|3            | 1         | marketing_edit       | admin/marketing/edit   | page      |  4  |  5  |
|4            | 1         | marketing_delete     | admin/marketing/delete | page      |  6  |  7  |
|5            | 1         | form_1               | admin/marketing/index  | object    |  8  |  14 |
|6            | 5         | username             | admin/marketing/index  | object    |  9  |  10 |
|7            | 5         | password             | admin/marketing/index  | object    |  11 |  12 |
|8            | 5         | email                | admin/marketing/index  | object    |  12 |  13 |
+-------------+-----------+----------------------+------------------------+-----------+-----+-----+
```

#### $this->rbac->permissions->assignRole(int $roleId, int $permId);

Assign a permission to a role.

```php
<?php
print_r($this->rbac->permissions->assignRole($roleId = 1, $permId = 1));
```

Gives

```php
<?php
/*
PDOStatement Object
(
    [queryString] => INSERT INTO rbac_role_permissions (`role_id`,`perm_id`) VALUES (?,?)
)
*/
```

#### $this->rbac->permissions->assignOperation(int $roleId, int $permId, int $opId);

Assign a permission to a operation.

```php
<?php
print_r($this->rbac->permissions->assignOperation($roleId = 1, $permId = 1, $opId = 1));
```

Gives

```php
<?php
/*
PDOStatement Object
(
    [queryString] => INSERT INTO rbac_op_permissions (`role_id`,`perm_id`, 'op_id') VALUES (?,?,?)
)
*/
```

#### $this->rbac->permissions->deAssignRole(int $roleId, int $permId);

De-assign a permission from a role.

```php
<?php
$this->rbac->permissions->deAssign($roleId = 1, $permId = 1);
```

Gives

```php
<?php
/*
PDOStatement Object
(
    [queryString] => DELETE FROM `rbac_role_permissions` WHERE `roleId` = ? AND `permId` = ?
)
*/
```

#### $this->rbac->permissions->deAssignRoles(int $permId);

De-assign permission to all roles.

```php
<?php
$this->rbac->permissions->deAssignRoles($permId = 1);
```

Gives

```php
<?php
/*
PDOStatement Object
(
    [queryString] => DELETE FROM `rbac_role_permissions` WHERE `permId` = ?
)
*/
```

#### $this->rbac->permissions->deAssignOperation($roleId = 1, $permId = 1, $opId = 1);

De-assign operation to from permission.

```php
<?php
$this->rbac->permissions->deAssignOperation($roleId = 1, $permId = 1, $opId = 1);
```

Gives

```php
<?php
/*
PDOStatement Object
(
    [queryString] => DELETE FROM `rbac_op_permissions` WHERE `roleId` = ? AND `permId` = ? AND `opId` = ?
)
*/
```

#### $this->rbac->permissions->deAssignOperations($roleId = 1, $permId = 1);

De-assign role, permission to all operation.

```php
<?php
$this->rbac->permissions->deAssignOperations($roleId = 1, $permId = 1);
```

Gives

```php
<?php
/*
PDOStatement Object
(
    [queryString] => DELETE FROM `rbac_op_permissions` WHERE `roleId` = ? AND `permId` = ? AND `opId` = ?
)
*/
```

#### $this->rbac->permissions->deAssignAllOperations($permId = 1);

De-assign permission to all operation.

```php
<?php
$this->rbac->permissions->deAssignOperations($permId = 1);
```

Gives

```php
<?php
/*
PDOStatement Object
(
    [queryString] => DELETE FROM `rbac_op_permissions` WHERE `permId` = ?
)
*/
```

### Moving Operations.

------

#### $this->rbac->permissions->moveAsFirst($sourceId, $targetId);


Before move operation our current table.

```php
+-------------+-----------+----------------------+------------------------+-----------+-----+-----+
| perm_id     | parent_id | perm_name            | perm_resource          | perm_type | lft | rgt |
+-------------+-----------+----------------------+------------------------+-----------+-----+-----+
|1            | 0         | marketing            | admin/marketing/index  | page      |  1  |  15 |
|2            | 1         | marketing_view       | admin/marketing/view   | page      |  2  |  3  |
|3            | 1         | marketing_edit       | admin/marketing/edit   | page      |  4  |  5  |
|4            | 1         | marketing_delete     | admin/marketing/delete | page      |  6  |  7  |
|5            | 1         | form_1               | admin/marketing/index  | object    |  8  |  14 |
|6            | 5         | username             | admin/marketing/index  | object    |  9  |  10 |
|7            | 5         | password             | admin/marketing/index  | object    |  11 |  12 |
|8            | 5         | email                | admin/marketing/index  | object    |  12 |  13 |
+-------------+-----------+----------------------+------------------------+-----------+-----+-----+
```

We want to move "form_1" under the "marketing_view" to be the first node.

```php
<?php
$this->rbac->permissions->moveAsFirst($sourceId = 5, $targetId = 2);
```
After the move operation.

Gives

```php
+-------------+-----------+----------------------+------------------------+-----------+-----+-----+
| perm_id     | parent_id | perm_name            | perm_resource          | perm_type | lft | rgt |
+-------------+-----------+----------------------+------------------------+-----------+-----+-----+
|1            | 0         | marketing            | admin/marketing/index  | page      |  1  |  15 |
|2            | 1         | marketing_view       | admin/marketing/view   | page      |  2  |  11 |
|5            | 2         | form_1               | admin/marketing/index  | object    |  3  |  4  |
|6            | 5         | username             | admin/marketing/index  | object    |  5  |  6  |
|7            | 5         | password             | admin/marketing/index  | object    |  7  |  8  |
|8            | 5         | email                | admin/marketing/index  | object    |  9  |  10 |
|3            | 1         | marketing_edit       | admin/marketing/edit   | page      |  11 |  12 |
|4            | 1         | marketing_delete     | admin/marketing/delete | page      |  13 |  14 |
+-------------+-----------+----------------------+------------------------+-----------+-----+-----+
```

#### $this->rbac->permissions->moveAsLast($sourceId, $targetId);

Before move operation our current table.

```php
+-------------+-----------+----------------------+------------------------+-----------+-----+-----+
| perm_id     | parent_id | perm_name            | perm_resource          | perm_type | lft | rgt |
+-------------+-----------+----------------------+------------------------+-----------+-----+-----+
|1            | 0         | marketing            | admin/marketing/index  | page      |  1  |  15 |
|2            | 1         | marketing_view       | admin/marketing/view   | page      |  2  |  11 |
|5            | 2         | form_1               | admin/marketing/index  | object    |  3  |  4  |
|6            | 5         | username             | admin/marketing/index  | object    |  5  |  6  |
|7            | 5         | password             | admin/marketing/index  | object    |  7  |  8  |
|8            | 5         | email                | admin/marketing/index  | object    |  9  |  10 |
|3            | 1         | marketing_edit       | admin/marketing/edit   | page      |  11 |  12 |
|4            | 1         | marketing_delete     | admin/marketing/delete | page      |  13 |  14 |
+-------------+-----------+----------------------+------------------------+-----------+-----+-----+
```

We want to move "form_1" under the "marketing" to be the last node.

```php
<?php
$this->rbac->permissions->moveAsLast($sourceId = 5, $targetId = 1);
```
After the move operation.

Gives

```php
+-------------+-----------+----------------------+------------------------+-----------+-----+-----+
| perm_id     | parent_id | perm_name            | perm_resource          | perm_type | lft | rgt |
+-------------+-----------+----------------------+------------------------+-----------+-----+-----+
|1            | 0         | marketing            | admin/marketing/index  | page      |  1  |  15 |
|2            | 1         | marketing_view       | admin/marketing/view   | page      |  2  |  3  |
|3            | 1         | marketing_edit       | admin/marketing/edit   | page      |  4  |  5  |
|4            | 1         | marketing_delete     | admin/marketing/delete | page      |  6  |  7  |
|5            | 1         | form_1               | admin/marketing/index  | object    |  8  |  14 |
|6            | 5         | username             | admin/marketing/index  | object    |  9  |  10 |
|7            | 5         | password             | admin/marketing/index  | object    |  11 |  12 |
|8            | 5         | email                | admin/marketing/index  | object    |  12 |  13 |
+-------------+-----------+----------------------+------------------------+-----------+-----+-----+
```

#### $this->rbac->permissions->moveAsPrevSibling($sourceId, $targetId);

Before move operation our current table.

```php
+-------------+-----------+----------------------+------------------------+-----------+-----+-----+
| perm_id     | parent_id | perm_name            | perm_resource          | perm_type | lft | rgt |
+-------------+-----------+----------------------+------------------------+-----------+-----+-----+
|1            | 0         | marketing            | admin/marketing/index  | page      |  1  |  15 |
|2            | 1         | marketing_view       | admin/marketing/view   | page      |  2  |  3  |
|3            | 1         | marketing_edit       | admin/marketing/edit   | page      |  4  |  5  |
|4            | 1         | marketing_delete     | admin/marketing/delete | page      |  6  |  7  |
|5            | 1         | form_1               | admin/marketing/index  | object    |  8  |  14 |
|6            | 5         | username             | admin/marketing/index  | object    |  9  |  10 |
|7            | 5         | password             | admin/marketing/index  | object    |  11 |  12 |
|8            | 5         | email                | admin/marketing/index  | object    |  12 |  13 |
+-------------+-----------+----------------------+------------------------+-----------+-----+-----+
```

We want to move "marketing_delete" as a previous sibling of "marketing_view"

```php
<?php
$this->rbac->permissions->moveAsPrevSibling($sourceId = 4, $targetId = 2);
```
After the move operation.

Gives

```php
+-------------+-----------+----------------------+------------------------+-----------+-----+-----+
| perm_id     | parent_id | perm_name            | perm_resource          | perm_type | lft | rgt |
+-------------+-----------+----------------------+------------------------+-----------+-----+-----+
|1            | 0         | marketing            | admin/marketing/index  | page      |  1  |  15 |
|4            | 1         | marketing_delete     | admin/marketing/delete | page      |  2  |  3  |
|2            | 1         | marketing_view       | admin/marketing/view   | page      |  4  |  5  |
|3            | 1         | marketing_edit       | admin/marketing/edit   | page      |  6  |  7  |
|5            | 1         | form_1               | admin/marketing/index  | object    |  8  |  14 |
|6            | 5         | username             | admin/marketing/index  | object    |  9  |  10 |
|7            | 5         | password             | admin/marketing/index  | object    |  11 |  12 |
|8            | 5         | email                | admin/marketing/index  | object    |  12 |  13 |
+-------------+-----------+----------------------+------------------------+-----------+-----+-----+
```

#### $this->rbac->permissions->moveAsNextSibling($sourceId, $targetId);

Before move operation our current table.

```php
+-------------+-----------+----------------------+------------------------+-----------+-----+-----+
| perm_id     | parent_id | perm_name            | perm_resource          | perm_type | lft | rgt |
+-------------+-----------+----------------------+------------------------+-----------+-----+-----+
|1            | 0         | marketing            | admin/marketing/index  | page      |  1  |  15 |
|4            | 1         | marketing_delete     | admin/marketing/delete | page      |  2  |  3  |
|2            | 1         | marketing_view       | admin/marketing/view   | page      |  4  |  5  |
|3            | 1         | marketing_edit       | admin/marketing/edit   | page      |  6  |  7  |
|5            | 1         | form_1               | admin/marketing/index  | object    |  8  |  14 |
|6            | 5         | username             | admin/marketing/index  | object    |  9  |  10 |
|7            | 5         | password             | admin/marketing/index  | object    |  11 |  12 |
|8            | 5         | email                | admin/marketing/index  | object    |  12 |  13 |
+-------------+-----------+----------------------+------------------------+-----------+-----+-----+
```

We want to move "marketing_delete" as a previous sibling of "marketing_edit"

```php
<?php
$this->rbac->permissions->moveAsNextSibling($sourceId = 4, $targetId = 3);
```
After the move operation.

Gives

```php
+-------------+-----------+----------------------+------------------------+-----------+-----+-----+
| perm_id     | parent_id | perm_name            | perm_resource          | perm_type | lft | rgt |
+-------------+-----------+----------------------+------------------------+-----------+-----+-----+
|1            | 0         | marketing            | admin/marketing/index  | page      |  1  |  15 |
|2            | 1         | marketing_view       | admin/marketing/view   | page      |  2  |  3  |
|3            | 1         | marketing_edit       | admin/marketing/edit   | page      |  4  |  5  |
|4            | 1         | marketing_delete     | admin/marketing/delete | page      |  6  |  7  |
|5            | 1         | form_1               | admin/marketing/index  | object    |  8  |  14 |
|6            | 5         | username             | admin/marketing/index  | object    |  9  |  10 |
|7            | 5         | password             | admin/marketing/index  | object    |  11 |  12 |
|8            | 5         | email                | admin/marketing/index  | object    |  12 |  13 |
+-------------+-----------+----------------------+------------------------+-----------+-----+-----+
```

### Querying Operations.

------

#### $this->rbac->permissions->getPermissions($select = 'perm_id,perm_name,perm_resource');

Retrieving a Full Permissions

```php
<?php
print_r($this->rbac->permissions->getPermissions($select = 'perm_id,perm_name,perm_resource'));
```

Gives

```php
<?php
/*
Array
(
    [0] => Array
        (
            [perm_id] => 1
            [perm_name] => marketing
            [perm_resource] => admin/marketing/index
            [depth] => 0
        )
    [1] => Array
        (
            [perm_id] => 2
            [perm_name] => marketing_view
            [perm_resource] => admin/marketing/view
            [depth] => 1
        )
    [2] => Array
        (
            [perm_id] => 3
            [perm_name] => marketing_edit
            [perm_resource] => admin/marketing/edit
            [depth] => 1
        )
    [3] => Array
        (
            [perm_id] => 4
            [perm_name] => marketing_delete
            [perm_resource] => admin/marketing/delete
            [depth] => 1
        )
)
*/
```

#### $this->rbac->permissions->getRoles(int $permId, $select = null);

Retrieving a Full Permissions

```php
<?php
print_r($this->rbac->permissions->getRoles($permId = 1));
```

Gives

```php
<?php
/*
Array
(
    [0] => Array
        (
            [role_id] => 1
        )
    [1] => Array
        (
            [role_id] => 2
        )
    [2] => Array
        (
            [role_id] => 5
        )
)
*/
```

#### $this->rbac->permissions->getRoot($select = 'perm_id,perm_name,perm_resource');

```php
<?php
print_r($this->rbac->permissions->getRoot($select = 'perm_id,perm_name,perm_resource'));
```

Gives

```php
<?php
/*
Array
(
    [0] => Array
        (
            [perm_id] => 1
            [perm_name] => marketing
            [perm_resource] => admin/marketing/index
        )
)
*/
```

#### $this->rbac->permissions->getSiblings($permId, $select = 'perm_id,perm_name,perm_resource');


```php
<?php
print_r($this->rbac->permissions->getSiblings(2, $select = 'perm_id,perm_name,perm_resource'));
```

Gives

```php
<?php
/*
Array
(
    [0] => Array
        (
            [perm_id] => 2
            [perm_name] => marketing_view
            [perm_resource] => admin/marketing/view
        )
    [1] => Array
        (
            [perm_id] => 3
            [perm_name] => marketing_edit
            [perm_resource] => admin/marketing/edit
        )
    [2] => Array
        (
            [perm_id] => 4
            [perm_name] => marketing_delete
            [perm_resource] => admin/marketing/delete
        )
)
*/
```

#### $this->rbac->permissions->update($permId, $data = array());

Updates your table row data using the primary key ( perm_id ).

Before update operation our current table looks like below the example.

```php
+-------------+-----------+----------------------+----------------------+-----------+-----+-----+
| perm_id     | parent_id | perm_name            | perm_resource        | perm_type | lft | rgt |
+-------------+-----------+----------------------+----------------------+-----------+-----+-----+
| 1           | 0         | marketing            | admin/marketing      | page      |  1  |  2  |
+-------------+-----------+----------------------+----------------------+-----------+-----+-----+
```

We want to update "marketing" name and perm_resource column value.

```php
<?php
$this->rbac->permissions->update($permId = 1, $data = array('perm_name' => 'sales', 'perm_resource' => 'admin/sales'));
```

After the update operation.

```php
+-------------+-----------+----------------------+----------------------+-----------+-----+-----+
| perm_id     | parent_id | perm_name            | perm_resource        | perm_type | lft | rgt |
+-------------+-----------+----------------------+----------------------+-----------+-----+-----+
| 1           | 0         | sales                | admin/sales          | page      |  1  |  2  |
+-------------+-----------+----------------------+----------------------+-----------+-----+-----+
```

#### $this->rbac->permissions->delete($permId);

Before delete operation our current table.

```php
+-------------+-----------+----------------------+------------------------+-----------+-----+-----+
| perm_id     | parent_id | perm_name            | perm_resource          | perm_type | lft | rgt |
+-------------+-----------+----------------------+------------------------+-----------+-----+-----+
|1            | 0         | marketing            | admin/marketing/index  | page      |  1  |  15 |
|2            | 1         | marketing_view       | admin/marketing/view   | page      |  2  |  3  |
|3            | 1         | marketing_edit       | admin/marketing/edit   | page      |  4  |  5  |
|4            | 1         | marketing_delete     | admin/marketing/delete | page      |  6  |  7  |
|5            | 1         | form_1               | admin/marketing/index  | object    |  8  |  14 |
|6            | 5         | username             | admin/marketing/index  | object    |  9  |  10 |
|7            | 5         | password             | admin/marketing/index  | object    |  11 |  12 |
|8            | 5         | email                | admin/marketing/index  | object    |  12 |  13 |
+-------------+-----------+----------------------+------------------------+-----------+-----+-----+
```

Deletes the given node (and any node) from the permissions table.

We delete "form_1" form. This operation also delete all nodes under the "form_1" form.

```php
<?php
$this->rbac->permissions->delete($permId = 5);
```

After the delete operation.

Gives

```php
+-------------+-----------+----------------------+------------------------+-----------+-----+-----+
| perm_id     | parent_id | perm_name            | perm_resource          | perm_type | lft | rgt |
+-------------+-----------+----------------------+------------------------+-----------+-----+-----+
|1            | 0         | marketing            | admin/marketing/index  | page      |  1  |  8  |
|2            | 1         | marketing_view       | admin/marketing/view   | page      |  2  |  3  |
|3            | 1         | marketing_edit       | admin/marketing/edit   | page      |  4  |  5  |
|4            | 1         | marketing_delete     | admin/marketing/delete | page      |  6  |  7  |
+-------------+-----------+----------------------+------------------------+-----------+-----+-----+
```

#### $this->rbac->permissions->getStatement();

Get PDO Statement Object

```php
<?php
print_r($this->rbac->permissions->getStatement());
```
Gives

```php
<?php
PDOStatement Object
(
    [queryString] => INSERT INTO foo (`parent_id`,`name`,`lft`,`rgt`) VALUES (?,?,?,?);
)
```

### Function Reference

-----

#### $this->rbac->permissions->addRoot($permName, $extra = array());

Add root.

#### $this->rbac->permissions->add($permId, $permName, $extra = array());

Add node.

#### $this->rbac->permissions->append($permId, $permName, $extra = array());

Append node.

#### $this->rbac->permissions->assign(int $roleId, int $permId);

Assign a permission to a role.

#### $this->rbac->permissions->deAssign(int $roleId, int $permId);

De-assign a permission from a role.

#### $this->rbac->permissions->moveAsFirst($sourceId, $targetId);

Move as first node.

#### $this->rbac->permissions->moveAsLast($sourceId, $targetId);

Move as last node.

#### $this->rbac->permissions->moveAsPrevSibling($sourceId, $targetId);

Move as prev sibling

#### $this->rbac->permissions->moveAsNextSibling($sourceId, $targetId);

Move as next sibling.

#### $this->rbac->permissions->getPermissions($select = 'perm_id,perm_name,perm_resource');

Get all permissions.

#### $this->rbac->permissions->getRoot($select = 'perm_id,perm_name,perm_resource');

Get root.

#### $this->rbac->permissions->getSiblings($roleId, $select = 'perm_id,perm_name,perm_resource');

Get siblings.

#### $this->rbac->permissions->update($roleId, $data = array());

Update node.

#### $this->rbac->permissions->delete($roleId);

Delete node.

#### $this->rbac->permissions->getStatement();

Returns to PDO Statement Object.