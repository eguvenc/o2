
## Rbac User Class

------

The Rbac User object control the permission access, permission assignments and access operations.

### Initializing the Class

------

First you need to define <kbd>Permission/Rbac/User</kbd> class as services. Update your service.php

```php
<?php
/*
|--------------------------------------------------------------------------
| User
|--------------------------------------------------------------------------
*/
$c['user'] = function () use ($c) {
    return new Obullo\Permissions\Rbac\User(
        array(
            'db.tablename'              => 'rbac_user_roles',
            'db.user_id'                => 'user_id',
            'db.role_id'                => 'role_id',
            'db.rbac_op_tablename'      => 'rbac_operations',
            'db.rbac_op_perm_tablename' => 'rbac_op_permissions',
            'db.op_id'                  => 'op_id',
            'db.op_text'                => 'operation',
            'db.roles_tablename'        => 'rbac_roles',
            'db.roles_text'             => 'role_name',
            'db.perm_type'              => 'perm_type',
            'db.role_perm_tablename'    => 'rbac_role_permissions',
            'db.perm_id'                => 'perm_id',
            'db.perm_text'              => 'perm_name',
            'db.perm_resource'          => 'perm_resource',
            'db.perm_tablename'         => 'rbac_permissions',
            'db.assignment_date'        => 'assignment_date',
            'db.allow'                  => 'allow',
            'db.deny'                   => 'deny',
        )
    );
};
```

```php
<?php
$c->load('user');
$this->user->method();
```

#### User Role
------

#### $this->user->assign(int $userId, int $roleId);

Assign a role to a user.

```php
<?php
print_r($this->user->assign($userId = 1, $roleId = 1));
```
Gives
```php
<?php
PDOStatement Object
(
    [queryString] => INSERT INTO rbac_user_roles (`user_id`,`role_id`) VALUES (?,?)
)

```

#### $this->user->deAssign(int $userId, int $roleId);

De-assign a role from a user.

```php
<?php
$this->user->deAssign($userId = 1, $roleId = 1);
```
Gives
```php
<?php
PDOStatement Object
(
    [queryString] => DELETE FROM `rbac_user_roles` WHERE `user_id` = ? AND `role_id` = ?
)
```

#### $this->setUserId(int $userId);

Set user id.

```php
<?php
$this->user->setUserId($userId = 1);
```

#### $this->setRoleIds(mixed $roleIds);

Set role ids.

```php
<?php
$this->user->setRoleIds(1);
```
And we propose other methods;

<kbd>$this->getRoles()</kbd> In order to use getRoles, you must set user id first using <b>setUserId()</b> method.

```php
<?php
$this->user->setUserId($userId = 1);
$this->user->setRoleIds($this->getRoles()); // getRoles() returns all roles for a specific user
```

#### $this->user->roleCount(int $userId);

Get the role count form user_id.

```php
<?php
echo $this->user->roleCount($userId = 1);
```
Gives
```php
<?php
1
```
Otherwise returns to false.

#### $this->user->setRoles(array $roleIds);

Set roles.

```php
<?php
$this->user->setUserId(1);
$this->user->setRoles($roleIds = array(2,5,7,8,11,12));
```

#### $this->user->getRoles(int $userId);

Get the roles.

```php
<?php
$this->user->setUserId(1);
echo $this->user->getRoles();
```
Gives
```php
<?php
Array
(
    [0] => 2
    [1] => 5
    [2] => 7
    [3] => 8
    [4] => 11
    [5] => 12
)
```
Otherwise returns to false.

#### $this->user->getPermissions(string $permName);

Get all permissions.

```php
<?php
$this->user->setUserId(1);
$this->user->getPermissions($permName = 'foo');
```
Gives
```php
<?php
Array
(
    [0] => Array
        (
            [perm_name] => foo
            [role_type] => page
            [perm_id] => 1
            [role_id] => 1
            [perm_resource] => admin/advertising
        )
    [1] => Array
        (
            [perm_name] => bar
            [role_type] => object
            [perm_id] => 2
            [role_id] => 2
            [perm_resource] => admin/advertising
        )
)
```

#### $this->user->isPermitted(string $permName, array $permissions)


```php
<?php
$this->perms->isPermitted($permName = 'foo', $permissions = array('foo', 'bar')); // true
```

#### $this->user->hasPagePermission(string $permResource, $expiration = 7200);

Returns true if has page permission allowed for given resource id otherwise false.

```php
<?php
$this->user->setUserId(1);
$this->user->setRoleId(1);
var_dump($this->user->hasPagePermission('admin/marketing')); // true
```

Returns to true or false.

#### $this->user->hasObjectPermission(string $permName, string $operationName, $expiration = 7200);

Returns array if has object permission allowed for given operation name otherwise false.

```php
<?php
$this->user->setUserId(1);
$this->user->setRoleId(1);
$this->user->hasPagePermission('admin/marketing');
// or
$this->user->setUserId(1);
$this->user->setRoleId(1);
$this->user->setResourceId('admin/marketing/index');

$permissions = $this->user->hasObjectPermission('foo', 'view');
var_dump($this->user->isPermitted('foo', $permissions)); // true
```

Returns to true or false.

#### $this->user->hasChildPermission(string $objectName, string $permName, string $operationName, $expiration = 7200);

Returns array if has object permission allowed for given operation name otherwise false.

```php
<?php
$this->user->setUserId(1);
$this->user->setRoleId(1);
$this->user->hasPagePermission('admin/marketing');
// or
$this->user->setUserId(1);
$this->user->setRoleId(1);
$this->user->setResourceId('admin/marketing/index');

$permissions = $this->user->hasChildPermission($objectName = 'foo', $permName = 'bar', 'view');
var_dump($this->user->isPermitted('bar', $permissions)); // true
```

Returns to true or false.

#### $this->permissions->getStatement();

Get PDO Statement Object

```php
<?php
print_r($this->user->getStatement());
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

------

#### $this->user->assign(int $userId, int $roleId);

Assign user to role.

#### $this->user->deAssign(int $userId, int $roleId);

Remove user from role.

#### $this->user->hasRole(int $userId, int $roleId);

Returns to true if user has role otherwise false.

#### $this->user->roleCount(int $userId, int $roleId);

Gives number of roles of user.

#### $this->user->setRoles(array $roleIds);
 
Set role ids for user class.

#### $this->user->getRoles(int $userId);

Get all roles of given user id.

#### $this->setUserId(int $userId);

Sets id of user to comfortable permission check operations.

#### $this->setRoleId(int $roleId);

Sets role id of user to comfortable permission check operations.

#### $this->user->getPermissions(string $permName);

Get all permissions of given permission name.

#### $this->user->hasPagePermission(string $permResource, int $expiration = 7200);

Has page permission.

#### $this->user->hasObjectPermission(string $permName, $elementName = array(), int $expiration = 7200);

Has object permission.

#### $this->user->getStatement();

Returns to PDO Statement Object.