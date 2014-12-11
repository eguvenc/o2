
## Rbac User Class

------

The Rbac User object control the permission access, permission assignments and access operations.

### Initializing the Rbac Service

------

```php
<?php
$c->load('service/rbac');
$this->rbac->user->method();
```

#### User Role

------

#### $this->rbac->user->assign(int $userId, int $roleId);

Assign a role to a user.

```php
<?php
print_r($this->rbac->user->assign($userId = 1, $roleId = 1));
```
Gives

```php
<?php
/*
PDOStatement Object
(
    [queryString] => INSERT INTO rbac_user_roles (`user_id`,`role_id`) VALUES (?,?)
)
*/
```

#### $this->rbac->user->deAssign(int $userId, int $roleId);

De-assign a role from a user.

```php
<?php
$this->rbac->user->deAssign($userId = 1, $roleId = 1);
```
Gives

```php
<?php
/*
PDOStatement Object
(
    [queryString] => DELETE FROM `rbac_user_roles` WHERE `user_id` = ? AND `role_id` = ?
)
*/
```

#### $this->rbac->user->setUserId(int $userId);

Set user id.

```php
<?php
$this->rbac->user->setUserId($userId = 1);
```

#### $this->rbac->user->setRoleIds(mixed $roleIds);

Set role ids.

```php
<?php
$this->rbac->user->setRoleIds(1);
```
And we propose other methods;

<kbd>$this->getRoles()</kbd> In order to use getRoles, you must set user id first using <b>setUserId()</b> method.

```php
<?php
$this->rbac->user->setUserId($userId = 1);
$this->rbac->user->setRoleIds($this->rbac->user->getRoles()); // getRoles() returns all roles for a specific user
```

#### $this->rbac->user->roleCount(int $userId);

Get the role count form user_id.

```php
<?php
echo $this->rbac->user->roleCount($userId = 1);
```
Gives
```php
<?php
// 1
```
Otherwise returns to false.

#### $this->rbac->user->setRoles(array $roleIds);

Set roles.

```php
<?php
$this->rbac->user->setUserId(1);
$this->rbac->user->setRoles($roleIds = array(2,5,7,8,11,12));
```

#### $this->rbac->user->getRoles(int $userId);

Get the roles.

```php
<?php
$this->rbac->user->setUserId(1);
echo $this->rbac->user->getRoles();
```

Gives

```php
<?php
/*
Array
(
    [0] => 2
    [1] => 5
    [2] => 7
    [3] => 8
    [4] => 11
    [5] => 12
)
*/
```
Otherwise returns to false.

#### $this->rbac->user->getPermissions(string $permName);

Get all permissions.

```php
<?php
$this->rbac->user->setUserId(1);
$this->rbac->user->getPermissions($permName = 'foo');
```
Gives

```php
<?php
/*
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
*/
```

#### $this->rbac->user->isPermitted(string $permName, array $permissions)


```php
<?php
$this->rbac->permissions->isPermitted($permName = 'foo', $permissions = array('foo', 'bar')); // true
```

#### $this->rbac->user->hasPagePermission(string $permResource, $expiration = 7200);

Returns true if has page permission allowed for given resource id otherwise false.

```php
<?php
$this->rbac->user->setUserId(1);
$this->rbac->user->setRoleId(1);

var_dump($this->rbac->user->hasPagePermission('admin/marketing')); // true
```

Returns to true or false.

#### $this->rbac->user->hasObjectPermission(string $permName, string $operationName, $expiration = 7200);

Returns array if has object permission allowed for given operation name otherwise false.

```php
<?php
$this->rbac->user->setUserId(1);
$this->rbac->user->setRoleId(1);
$this->rbac->user->hasPagePermission('admin/marketing');
// or
$this->rbac->user->setUserId(1);
$this->rbac->user->setRoleId(1);
$this->rbac->user->setResourceId('admin/marketing/index');

$permissions = $this->rbac->user->hasObjectPermission('foo', 'view');

var_dump($this->rbac->user->isPermitted('foo', $permissions)); // true
```

Returns to true or false.

#### $this->rbac->user->hasChildPermission(string $objectName, string $permName, string $operationName, $expiration = 7200);

Returns array if has object permission allowed for given operation name otherwise false.

```php
<?php
$this->rbac->user->setUserId(1);
$this->rbac->user->setRoleId(1);
$this->rbac->user->hasPagePermission('admin/marketing');
// or
$this->rbac->user->setUserId(1);
$this->rbac->user->setRoleId(1);
$this->rbac->user->setResourceId('admin/marketing/index');

$permissions = $this->rbac->user->hasChildPermission($objectName = 'foo', $permName = 'bar', 'view');

var_dump($this->rbac->user->isPermitted('bar', $permissions)); // true
```

Returns to true or false.

#### $this->rbac->user->getStatement();

Get PDO Statement Object

```php
<?php
print_r($this->rbac->user->getStatement());
```
Gives

```php
<?php
/*
<?php
PDOStatement Object
(
    [queryString] => INSERT INTO foo (`parent_id`,`name`,`lft`,`rgt`) VALUES (?,?,?,?);
)
*/
```

### Function Reference

------

#### $this->rbac->user->assign(int $userId, int $roleId);

Assign user to role.

#### $this->rbac->user->deAssign(int $userId, int $roleId);

Remove user from role.

#### $this->rbac->user->hasRole(int $userId, int $roleId);

Returns to true if user has role otherwise false.

#### $this->rbac->user->roleCount(int $userId, int $roleId);

Gives number of roles of user.

#### $this->rbac->user->setRoles(array $roleIds);
 
Set role ids for user class.

#### $this->rbac->user->getRoles(int $userId);

Get all roles of given user id.

#### $this->setUserId(int $userId);

Sets id of user to comfortable permission check operations.

#### $this->setRoleId(int $roleId);

Sets role id of user to comfortable permission check operations.

#### $this->rbac->user->getPermissions(string $permName);

Get all permissions of given permission name.

#### $this->rbac->user->hasPagePermission(string $permResource, int $expiration = 7200);

Has page permission.

#### $this->rbac->user->hasObjectPermission(string $permName, $elementName = array(), int $expiration = 7200);

Has object permission.

#### $this->rbac->user->getStatement();

Returns to PDO Statement Object.