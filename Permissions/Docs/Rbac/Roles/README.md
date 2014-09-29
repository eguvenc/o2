
## Rbac Role Class

------

RBAC is a secure method of restricting account access to authorized users. This method enables the account owner to add users to the account and assign each user to specific roles. Each role has specific permissions defined by Rackspace. RBAC allows users to perform various actions based on the scope of their assigned role. 

<a href="https://www.sans.org/reading-room/whitepapers/sysadmin/role-based-access-control-nist-solution-1270">https://www.sans.org/reading-room/whitepapers/sysadmin/role-based-access-control-nist-solution-1270</a>

### Initializing the Class

------

First you need to define <kbd>Permission/rbac/roles</kbd> class as services. Update your service.php

```php
/*
|--------------------------------------------------------------------------
| Roles
|--------------------------------------------------------------------------
*/
$c['roles'] = function () use ($c) {
    return new Obullo\Permissions\Rbac\Roles(
    	array(
			'db.tablename'   => 'rbac_roles',
			'db.primary_key' => 'role_id',
			'db.parent_id'   => 'parent_id',
			'db.text'        => 'role_name',
			'db.left'        => 'lft',
			'db.right'       => 'rgt',
    	)
    );
};
```

```php
$c->load('roles');
$this->roles->method();
```

### Adding Operations.

------

#### Add Root

#### $this->roles->addRoot($roleName, $extra = array());

```php
$this->roles->addRoot($roleName = 'root');
```
Gives
```php
+-------------+-----------+----------------------+-----+-----+
| role_id     | parent_id | role_name            | lft | rgt |
+-------------+-----------+----------------------+-----+-----+
|           1 | 0  	      | root		         |  1  |  2  |
+-------------+-----------+----------------------+-----+-----+
```

#### Add Nodes

#### $this->roles->add($roleId, $roleName, $extra = array());

```php
$this->roles->add($roleId = 1, $roleName = 'CEO');
```
Gives
```php
+-------------+-----------+----------------------+-----+-----+
| role_id     | parent_id | role_name            | lft | rgt |
+-------------+-----------+----------------------+-----+-----+
| 1			  | 0  	      | root		         |  1  |  4  |
| 2           | 1  	      | CEO		             |  2  |  3  |
+-------------+-----------+----------------------+-----+-----+
```

Let's add an "Operations" node under the "CEO".

```php
$this->roles->add($roleId = 2, $roleName = 'Operations');
```
Gives
```php
+-------------+-----------+----------------------+-----+-----+
| role_id     | parent_id | role_name            | lft | rgt |
+-------------+-----------+----------------------+-----+-----+
| 1			  | 0  	      | root		         |  1  |  6  |
| 2           | 1  	      | CEO		             |  2  |  5  |
| 3           | 2  	      | Operations           |  3  |  4  |
+-------------+-----------+----------------------+-----+-----+
```

#### $this->roles->append($roleId, $roleName, $extra = array());

```php
$this->roles->append($roleId = 2, $roleName = 'Financial');
```
Gives
```php
+-------------+-----------+----------------------+-----+-----+
| role_id     | parent_id | role_name            | lft | rgt |
+-------------+-----------+----------------------+-----+-----+
| 1			  | 0  	      | root		         |  1  |  8  |
| 2           | 1  	      | CEO		             |  2  |  7  |
| 3           | 2  	      | Operations           |  3  |  4  |
| 4           | 2  	      | Financial            |  5  |  6  |
+-------------+-----------+----------------------+-----+-----+
```

Let's add an "Financial" node under the "CEO".

```php
$this->roles->append($roleId = 2, $roleName = 'IT');
```
Gives
```php
+-------------+-----------+----------------------+-----+-----+
| role_id     | parent_id | role_name            | lft | rgt |
+-------------+-----------+----------------------+-----+-----+
| 1			  | 0  	      | root		         |  1  |  10 |
| 2           | 1  	      | CEO		             |  2  |  9  |
| 3           | 2  	      | Operations           |  3  |  4  |
| 4           | 2  	      | Financial            |  5  |  6  |
| 5           | 2  	      | IT                   |  7  |  8  |
+-------------+-----------+----------------------+-----+-----+
```

#### We define new roles under "Financial".

```php
$this->roles->add($roleId = 4, $roleName = 'Sales');
$this->roles->append($roleId = 4, $roleName = 'Marketing');
$this->roles->append($roleId = 4, $roleName = 'Payroll');
```
Gives
```php
+-------------+-----------+----------------------+-----+-----+
| role_id     | parent_id | role_name            | lft | rgt |
+-------------+-----------+----------------------+-----+-----+
| 1			  | 0  	      | root		         |  1  |  16 |
| 2           | 1  	      | CEO		             |  2  |  15 |
| 3           | 2  	      | Operations           |  3  |  4  |
| 4           | 2  	      | Financial            |  5  |  12 |
| 6           | 4  	      | Sales                |  6  |  7  |
| 7           | 4  	      | Marketing            |  8  |  9  |
| 8           | 4  	      | Payroll              |  10 |  11 |
| 5           | 2  	      | IT                   |  13 |  14 |
+-------------+-----------+----------------------+-----+-----+
```

#### We define new roles under "IT".

```php
$this->roles->add($roleId = 5, $roleName = 'Network');
$this->roles->append($roleId = 5, $roleName = 'Security');
$this->roles->append($roleId = 5, $roleName = 'Admin');
```
Gives
```php
+-------------+-----------+----------------------+-----+-----+
| role_id     | parent_id | role_name            | lft | rgt |
+-------------+-----------+----------------------+-----+-----+
| 1			  | 0  	      | root		         |  1  |  22 |
| 2           | 1  	      | CEO		             |  2  |  21 |
| 3           | 2  	      | Operations           |  3  |  4  |
| 4           | 2  	      | Financial            |  5  |  12 |
| 6           | 4  	      | Sales                |  6  |  7  |
| 7           | 4  	      | Marketing            |  8  |  9  |
| 8           | 4  	      | Payroll              |  10 |  11 |
| 5           | 2  	      | IT                   |  13 |  20 |
| 9           | 5  	      | Network              |  14 |  15 |
| 10          | 5  	      | Security             |  16 |  17 |
| 11          | 5  	      | Admin                |  18 |  19 |
+-------------+-----------+----------------------+-----+-----+
```

### Moving Operations.

------
#### $this->roles->moveAsFirst($sourceId, $targetId);

Before move operation our current table.

```php
+-------------+-----------+----------------------+-----+-----+
| role_id     | parent_id | role_name            | lft | rgt |
+-------------+-----------+----------------------+-----+-----+
| 1			  | 0  	      | root		         |  1  |  22 |
| 2           | 1  	      | CEO		             |  2  |  21 |
| 3           | 2  	      | Operations           |  3  |  4  |
| 4           | 2  	      | Financial            |  5  |  12 |
| 6           | 4  	      | Sales                |  6  |  7  |
| 7           | 4  	      | Marketing            |  8  |  9  |
| 8           | 4  	      | Payroll              |  10 |  11 |
| 5           | 2  	      | IT                   |  13 |  20 |
| 9           | 5  	      | Network              |  14 |  15 |
| 10          | 5  	      | Security             |  16 |  17 |
| 11          | 5  	      | Admin                |  18 |  19 |
+-------------+-----------+----------------------+-----+-----+
```

We want to move "Admin" under the "IT" to be the first node.

```php
$this->roles->moveAsFirst($sourceId = 11, $targetId = 5);
```
After the move operation.

Gives
```php
+-------------+-----------+----------------------+-----+-----+
| role_id     | parent_id | role_name            | lft | rgt |
+-------------+-----------+----------------------+-----+-----+
| 1			  | 0  	      | root		         |  1  |  22 |
| 2           | 1  	      | CEO		             |  2  |  21 |
| 3           | 2  	      | Operations           |  3  |  4  |
| 4           | 2  	      | Financial            |  5  |  12 |
| 6           | 4  	      | Sales                |  6  |  7  |
| 7           | 4  	      | Marketing            |  8  |  9  |
| 8           | 4  	      | Payroll              |  10 |  11 |
| 5           | 2  	      | IT                   |  13 |  20 |
| 11          | 5  	      | Admin                |  14 |  15 |
| 9           | 5  	      | Network              |  16 |  17 |
| 10          | 5  	      | Security             |  18 |  19 |
+-------------+-----------+----------------------+-----+-----+
```

#### $this->roles->moveAsLast($sourceId, $targetId);

Before move operation our current table.

```php
+-------------+-----------+----------------------+-----+-----+
| role_id     | parent_id | role_name            | lft | rgt |
+-------------+-----------+----------------------+-----+-----+
| 1			  | 0  	      | root		         |  1  |  22 |
| 2           | 1  	      | CEO		             |  2  |  21 |
| 3           | 2  	      | Operations           |  3  |  4  |
| 4           | 2  	      | Financial            |  5  |  12 |
| 6           | 4  	      | Sales                |  6  |  7  |
| 7           | 4  	      | Marketing            |  8  |  9  |
| 8           | 4  	      | Payroll              |  10 |  11 |
| 5           | 2  	      | IT                   |  13 |  20 |
| 11          | 5  	      | Admin                |  14 |  15 |
| 9           | 5  	      | Network              |  16 |  17 |
| 10          | 5  	      | Security             |  18 |  19 |
+-------------+-----------+----------------------+-----+-----+
```

We want to move "Admin" under the "IT" to be the last node.

```php
$this->roles->moveAsLast($sourceId = 11, $targetId = 5);
```
After the move operation.

Gives
```php
+-------------+-----------+----------------------+-----+-----+
| role_id     | parent_id | role_name            | lft | rgt |
+-------------+-----------+----------------------+-----+-----+
| 1			  | 0  	      | root		         |  1  |  22 |
| 2           | 1  	      | CEO		             |  2  |  21 |
| 3           | 2  	      | Operations           |  3  |  4  |
| 4           | 2  	      | Financial            |  5  |  12 |
| 6           | 4  	      | Sales                |  6  |  7  |
| 7           | 4  	      | Marketing            |  8  |  9  |
| 8           | 4  	      | Payroll              |  10 |  11 |
| 5           | 2  	      | IT                   |  13 |  20 |
| 9           | 5  	      | Network              |  14 |  15 |
| 10          | 5  	      | Security             |  16 |  17 |
| 11          | 5  	      | Admin                |  18 |  19 |
+-------------+-----------+----------------------+-----+-----+
```

#### $this->roles->moveAsPrevSibling($sourceId, $targetId);

Before move operation our current table.

```php
+-------------+-----------+----------------------+-----+-----+
| role_id     | parent_id | role_name            | lft | rgt |
+-------------+-----------+----------------------+-----+-----+
| 1			  | 0  	      | root		         |  1  |  22 |
| 2           | 1  	      | CEO		             |  2  |  21 |
| 3           | 2  	      | Operations           |  3  |  4  |
| 4           | 2  	      | Financial            |  5  |  12 |
| 6           | 4  	      | Sales                |  6  |  7  |
| 7           | 4  	      | Marketing            |  8  |  9  |
| 8           | 4  	      | Payroll              |  10 |  11 |
| 5           | 2  	      | IT                   |  13 |  20 |
| 9           | 5  	      | Network              |  14 |  15 |
| 10          | 5  	      | Security             |  16 |  17 |
| 11          | 5  	      | Admin                |  18 |  19 |
+-------------+-----------+----------------------+-----+-----+
```

We want to move "IT" as a previous sibling of "Financial"

```php
$this->roles->moveAsPrevSibling($sourceId = 5, $targetId = 4);
```
After the move operation.

Gives
```php
+-------------+-----------+----------------------+-----+-----+
| role_id     | parent_id | role_name            | lft | rgt |
+-------------+-----------+----------------------+-----+-----+
| 1			  | 0  	      | root		         |  1  |  22 |
| 2           | 1  	      | CEO		             |  2  |  21 |
| 3           | 2  	      | Operations           |  3  |  4  |
| 5           | 2  	      | IT                   |  5  |  12 |
| 9           | 5  	      | Network              |  6  |  7  |
| 10          | 5  	      | Security             |  8  |  9  |
| 11          | 5  	      | Admin                |  10 |  11 |
| 4           | 2  	      | Financial            |  13 |  20 |
| 6           | 4  	      | Sales                |  14 |  15 |
| 7           | 4  	      | Marketing            |  16 |  17 |
| 8           | 4  	      | Payroll              |  18 |  19 |
+-------------+-----------+----------------------+-----+-----+
```

#### $this->roles->moveAsNextSibling($sourceId, $targetId);

Before move operation our current table.

```php
+-------------+-----------+----------------------+-----+-----+
| role_id     | parent_id | role_name            | lft | rgt |
+-------------+-----------+----------------------+-----+-----+
| 1			  | 0  	      | root		         |  1  |  22 |
| 2           | 1  	      | CEO		             |  2  |  21 |
| 3           | 2  	      | Operations           |  3  |  4  |
| 5           | 2  	      | IT                   |  5  |  12 |
| 9           | 5  	      | Network              |  6  |  7  |
| 10          | 5  	      | Security             |  8  |  9  |
| 11          | 5  	      | Admin                |  10 |  11 |
| 4           | 2  	      | Financial            |  13 |  20 |
| 6           | 4  	      | Sales                |  14 |  15 |
| 7           | 4  	      | Marketing            |  16 |  17 |
| 8           | 4  	      | Payroll              |  18 |  19 |
+-------------+-----------+----------------------+-----+-----+
```

We want to move "IT" as a previous sibling of "Financial"

```php
$this->roles->moveAsNextSibling($sourceId = 5, $targetId = 4);
```
After the move operation.

Gives
```php
+-------------+-----------+----------------------+-----+-----+
| role_id     | parent_id | role_name            | lft | rgt |
+-------------+-----------+----------------------+-----+-----+
| 1			  | 0  	      | root		         |  1  |  22 |
| 2           | 1  	      | CEO		             |  2  |  21 |
| 3           | 2  	      | Operations           |  3  |  4  |
| 4           | 2  	      | Financial            |  5  |  12 |
| 6           | 4  	      | Sales                |  6  |  7  |
| 7           | 4  	      | Marketing            |  8  |  9  |
| 8           | 4  	      | Payroll              |  10 |  11 |
| 5           | 2  	      | IT                   |  13 |  20 |
| 9           | 5  	      | Network              |  14 |  15 |
| 10          | 5  	      | Security             |  16 |  17 |
| 11          | 5  	      | Admin                |  18 |  19 |
+-------------+-----------+----------------------+-----+-----+
```

#### $this->roles->deleteRolePermissions(int $roleId);

Delete role from permissions.

```php
$this->roles->deleteRolePermissions($roleId = 1);
```
Gives
```php
PDOStatement Object
(
    [queryString] => DELETE FROM `rbac_role_permissions` WHERE `roleId` = ?
)
```

#### $this->roles->deleteRoleFromUsers(int $roleId);

Delete role from users.

```php
$this->roles->deleteRoleFromUsers($roleId = 1);
```
Gives
```php
PDOStatement Object
(
    [queryString] => DELETE FROM `rbac_user_roles` WHERE `roleId` = ?
)
```

#### $this->roles->deleteOperationsByRoleId(int $roleId);

Delete operations by role id.

```php
$this->roles->deleteOperationsByRoleId($roleId = 1);
```
Gives
```php
PDOStatement Object
(
    [queryString] => DELETE FROM `rbac_op_permissions` WHERE `roleId` = ?
)
```

### Querying Operations.

------

#### $this->roles->getRoles($select = 'role_id,role_name');

Retrieving a Full Roles

```php
print_r($this->roles->getRoles($select = 'role_id,role_name'));
```
Gives
```php
Array
(
    [0] => Array
        (
            [role_id] => 1
            [role_name] => root
            [depth] => 0
        )
    [1] => Array
        (
            [role_id] => 2
            [role_name] => CEO
            [depth] => 1
        )
    [2] => Array
        (
            [role_id] => 3
            [role_name] => Operations
            [depth] => 2
        )
    [3] => Array
        (
            [role_id] => 4
            [role_name] => Financial
            [depth] => 2
        )
)
```

#### $this->roles->getRoot($select = 'role_id,role_name');

```php
print_r($this->roles->getRoot($select = 'role_id,role_name'));
```

Gives

```php
Array
(
    [0] => Array
        (
            [role_id] => 1
            [role_name] => root
        )
)
```

#### $this->roles->getSiblings($roleId, $select = 'role_id,role_name');

```php
print_r($this->roles->getSiblings(3, $select = 'role_id,role_name'));
```

Gives

```php
Array
(
    [0] => Array
        (
            [role_id] => 3
            [role_name] => Operations
        )
    [1] => Array
        (
            [role_id] => 4
            [role_name] => Financial
        )
    [2] => Array
        (
            [role_id] => 5
            [role_name] => IT
        )
)
```

#### $this->roles->getUsers($roleId, $select = null, $expiration = 7200);

```php
print_r($this->roles->getUsers(1));
```

Gives

```php
Array
(
    [0] => Array
        (
            [user_id] => 1
        )
    [1] => Array
        (
            [user_id] => 8
        )
    [2] => Array
        (
            [user_id] => 47
        )
)
```

#### $this->roles->getPermissions($roleId, $select = null, $expiration = 7200);

Get permissions from role id.

```php
print_r($this->roles->getPermissions($roleId = 1));
```
Gives

```php
Array
(
    [0] => Array
        (
            [permission_id] => 1
        )
    [1] => Array
        (
            [permission_id] => 2
        )
    [2] => Array
        (
            [permission_id] => 5
        )
)  
```

#### $this->roles->update($roleId, $data = array());

Updates your table row data using the primary key ( role_id ).

Before update operation our current table looks like below the example.

```php
+-------------+-----------+----------------------+-----+-----+
| role_id     | parent_id | role_name            | lft | rgt |
+-------------+-----------+----------------------+-----+-----+
| 1			  | 0  	      | root		         |  1  |  2  |
+-------------+-----------+----------------------+-----+-----+
```

We want to update "root" name.

```php
$this->roles->update($roleId = 1, $data = array('role_name' => 'Root'));
```

After the update operation.

```php
+-------------+-----------+----------------------+-----+-----+
| role_id     | parent_id | role_name            | lft | rgt |
+-------------+-----------+----------------------+-----+-----+
| 1			  | 0  	      | Root		         |  1  |  2  |
+-------------+-----------+----------------------+-----+-----+
```

#### $this->roles->delete($roleId);

Before delete operation our current table.

```php
+-------------+-----------+----------------------+-----+-----+
| role_id     | parent_id | role_name            | lft | rgt |
+-------------+-----------+----------------------+-----+-----+
| 1			  | 0  	      | root		         |  1  |  22 |
| 2           | 1  	      | CEO		             |  2  |  21 |
| 3           | 2  	      | Operations           |  3  |  4  |
| 4           | 2  	      | Financial            |  5  |  12 |
| 6           | 4  	      | Sales                |  6  |  7  |
| 7           | 4  	      | Marketing            |  8  |  9  |
| 8           | 4  	      | Payroll              |  10 |  11 |
| 5           | 2  	      | IT                   |  13 |  20 |
| 9           | 5  	      | Network              |  14 |  15 |
| 10          | 5  	      | Security             |  16 |  17 |
| 11          | 5  	      | Admin                |  18 |  19 |
+-------------+-----------+----------------------+-----+-----+
```

Deletes the given node (and any node) from the roles table.

We delete "IT" department. This operation also delete all nodes under the IT department.

```php
$this->roles->delete($roleId);
```

After the delete operation.

Gives
```php
+-------------+-----------+----------------------+-----+-----+
| role_id     | parent_id | role_name            | lft | rgt |
+-------------+-----------+----------------------+-----+-----+
| 1			  | 0  	      | root		         |  1  |  14 |
| 2           | 1  	      | CEO		             |  2  |  13 |
| 3           | 2  	      | Operations           |  3  |  4  |
| 4           | 2  	      | Financial            |  5  |  12 |
| 6           | 4  	      | Sales                |  6  |  7  |
| 7           | 4  	      | Marketing            |  8  |  9  |
| 8           | 4  	      | Payroll              |  10 |  11 |
+-------------+-----------+----------------------+-----+-----+
```

#### $this->permissions->getStatement();

Get PDO Statement Object

```php
print_r($this->treeDb->getStatement());
```
Gives
```php
PDOStatement Object
(
    [queryString] => INSERT INTO foo (`parent_id`,`name`,`lft`,`rgt`) VALUES (?,?,?,?);
)
```

### Function Reference

-----

#### $this->roles->addRoot($roleName, $extra = array());

Add root.

#### $this->roles->add($roleId, $roleName, $extra = array());

Add node.

#### $this->roles->append($roleId, $roleName, $extra = array());

Append node.

#### $this->roles->moveAsFirst($sourceId, $targetId);

Move as first node.

#### $this->roles->moveAsLast($sourceId, $targetId);

Move as last node.

#### $this->roles->moveAsPrevSibling($sourceId, $targetId);

Move as prev sibling

#### $this->roles->moveAsNextSibling($sourceId, $targetId);

Move as next sibling.

#### $this->roles->getRoles($select = 'role_id,role_name');

Get all roles.

#### $this->roles->getRoot($select = 'role_id,role_name');

Get root.

#### $this->roles->getSiblings($roleId, $select = 'role_id,role_name');

Get siblings.

#### $this->roles->update($roleId, $data = array());

Update node.

#### $this->roles->delete($roleId);

Delete node.

#### $this->roles->getStatement();

Returns to PDO Statement Object