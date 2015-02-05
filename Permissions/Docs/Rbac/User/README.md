
## User Class

------

The Rbac User object control the permission access, permission assignments and access operations.

### Initializing the Rbac Service

------

```php
<?php
$this->c['service/rbac'];
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

#### $this->rbac->user->setId(int $userId);

Set user id.

```php
<?php
$this->rbac->user->setId($userId = 1);
```

#### $this->rbac->user->setRoleIds(mixed $roleIds);

Set role ids.

```php
<?php
$this->rbac->user->setRoleIds(1);
```
And we propose other methods;

<kbd>$this->getRoles()</kbd> In order to use getRoles, you must set user id first using <b>setId()</b> method.

```php
<?php
$this->rbac->user->setId($userId = 1);
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

#### $this->rbac->user->getRoles(int $userId);

Get the roles.

```php
<?php
$this->rbac->user->setId(1);
print_r($this->rbac->user->getRoles());
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

## Yetkiler

Yetkiler 

### Kaynak ( Resource )

-----

Benzer isimde ki form elementlerin de birbirinden ayırıcı özellik olarak form adları kullanılmaktadır. Örnek verecek olursak, iki ayrı (ekleme ve düzenleme) formumuzda "user_username" input adını form adları yardımıyla birbirlerinden bağımsız çalıştırabilmekteyiz.

Aynı zamanda formlarında karışıklığa sebep vermemesi için birbirlerinden ayırıcı özellik olarak "kaynak kimlikleri" (resource id) kullanılmaktadır.

### Kaynak kimliği (Resource id) nedir?

Kaynak kimlikleri aslında nesnelerimizin çalışacağı sayfalardır. <kbd>http://www.example.com/user/create</kbd> bir kaynak kimliği (resoure id) 'dir.

### Sınıfın kullanımı

```php
<?php $this->rbac->resource->method();
```
Bir kaynak kimliği tanımlamak;

```php
<?php $this->rbac->resource->setId('user/create');
```
### Sayfa Yetkilerini Almak

#### $this->rbac->resource->page->getPermissions();
```php
<?php
$operations = array('view', 'insert'); // Eğer birden fazla operasyon kontrolü yapılacaksa
                                       // dizi olarak göndermemiz gerekmektedir.
$this->rbac->user->setId(1);
$this->rbac->user->setRoleIds(1);
$this->rbac->resource->setId('user/create');

var_dump($this->rbac->resource->page->getPermissions($operations));

// Çıktı

array (size=2)
  0 => 
    array (size=5)
      'rbac_permission_name'     => string 'User Create Page' (length=16)
      'rbac_permission_id'       => string '7' (length=1)
      'rbac_roles_rbac_role_id'  => string '1' (length=1)
      'rbac_permission_resource' => string 'user/create' (length=11)
      'rbac_operation_name'      => string 'view' (length=4)
  1 => 
    array (size=5)
      'rbac_permission_name'     => string 'User Create Page' (length=16)
      'rbac_permission_id'       => string '7' (length=1)
      'rbac_roles_rbac_role_id'  => string '1' (length=1)
      'rbac_permission_resource' => string 'user/create' (length=11)
      'rbac_operation_name'      => string 'insert' (length=6)
```

#### $this->rbac->resource->page['kaynak kimliği']->getPermissions();

Aynı kontroller içerisinde başka sayfa kontrolü yapılması gereken durumlarda, sürekli <kbd>resource->setId()</kbd> yazarak kod tekrarı olmaması ve pratiklik sağlamak adına, <kbd>page->getPermissions()</kbd> metodunun başka bir kullanım şeklidir. Bu kullanımı bir örnekle gösterelim.
```php
<?php
$operations = array('view', 'insert'); // Eğer birden fazla operasyon kontrolü yapılacaksa
                                       // dizi olarak göndermemiz gerekmektedir.
$this->rbac->user->setId(1);
$this->rbac->user->setRoleIds(1);
// $this->rbac->resource->setId('user/create'); Artık kullanmamız gerekmiyor.

var_dump($this->rbac->resource->page['user/create']->getPermissions($operations));
```
Bu kullanım bize yukarıda ki çıktının aynısını verecektir.

<blockquote>Bu örneklerden de anlayacağınız üzere, "User" sınıfı kesinlikle resource id'ye ihtiyaç duyar.</blockquote>

### Sayfaya Ait Nesnelerin Yetkilerini Almak

#### $this->rbac->resource->object['form adı']->getPermission();
```php
<?php
$operations = array('view', 'insert'); // Eğer birden fazla operasyon kontrolü yapılacaksa
                                       // dizi olarak göndermemiz gerekmektedir.
$this->rbac->user->setId(1);
$this->rbac->user->setRoleIds(1);
$this->rbac->resource->setId('user/create');

var_dump($this->rbac->resource->object['addNewUserForm']->getPermissions($operations));

// Çıktı

array (size=2)
  0 => 
    array (size=5)
      'rbac_permission_name'     => string 'addNewUserForm' (length=14)
      'rbac_permission_id'       => string '2' (length=1)
      'rbac_roles_rbac_role_id'  => string '1' (length=1)
      'rbac_permission_resource' => string 'user/create' (length=11)
      'rbac_operation_name'      => string 'view' (length=4)
  1 => 
    array (size=5)
      'rbac_permission_name'     => string 'addNewUserForm' (length=14)
      'rbac_permission_id'       => string '2' (length=1)
      'rbac_roles_rbac_role_id'  => string '1' (length=1)
      'rbac_permission_resource' => string 'user/create' (length=11)
      'rbac_operation_name'      => string 'insert' (length=6)
```

## Yetki kontrolü ve Operasyonlar ( Operations )

Kullanıcının sayfa veya nesnelere olan erişim kontrolü <b>Operation</b> sınıfı tarafından yapılmaktadır.

```php
<?php
$this->user->operasyonAdi->operasyonTipi->method();
```

#### Operasyonlar: <a name='#operasyonlar'></a>

* view
* delete
* update
* insert
* save (insert, update)

#### Operasyon Tipleri:

1. page
2. object

### Operasyon Tipi ( Page )


#### $this->rbac->user->operasyon->page['kaynak kimliği']->isAllowed();

Kişinin belirtilen "operasyona" yetkisinin olup olmadığını kontrol eder. Geriye doğru ya da yanlış cevabı döner.

```php
<?php
$this->rbac->user->setId(1);
$this->rbac->user->setRoleIds($this->rbac->user->getRoles());

if (! $this->user->view->page['user/create']->isAllowed()) { // false
    exit('Bu sayfaya giriş izniniz bulunmamaktadır.');
} else {
    // kodlar
}
```
<blockquote>Örnekte ki "view" yerine kullanabileceğiniz operasyonlar için "<a href="#operasyonlar">Operasyonlar</a>" kısmına göz atabilirsiniz.</blockquote>


### Operasyon Tipi ( Object )

Form nesnesi kontrolü;

#### $this->rbac->user->view->object['form adı']->isAllowed();
```php
<?php if ($this->user->view->object['addNewUser']->isAllowed()) : ?>
    <form action="admin/user/create" name="addNewUser" method="post" accept-charset="utf-8">
        <input type="text" name="user_username" />
        <input type="text" name="user_email" />
        <input type="password" name="user_password" />
        <input type="submit" name="Kullanıcı Ekle" />
    </form>
<?php endif; ?>
```
Geriye doğru ya da yanlış cevabı döner.

<blockquote>Örnekte ki "view" yerine kullanabileceğiniz operasyonlar için "<a href="#operasyonlar">Operasyonlar</a>" kısmına göz atabilirsiniz.</blockquote>

Form element nesnesi kontrolü;

#### $this->rbac->user->view->object['form adı']->isAllowed();
```php
$this->user->view->object['addNewUser']->isAllowed();
```
Geriye doğru ya da yanlış cevabı döner.

<blockquote>Örnekte ki "view" yerine kullanabileceğiniz operasyonlar için "<a href="#operasyonlar">Operasyonlar</a>" kısmına göz atabilirsiniz.</blockquote>



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

#### $this->rbac->user->roleCount(int $userId, int $roleId);

Gives number of roles of user.

#### $this->rbac->user->setRoles(array $roleIds);
 
Set role ids for user class.

#### $this->rbac->user->getRoles(int $userId);

Get all roles of given user id.

#### $this->rbac->user->setId(int $userId);

Sets id of user to comfortable permission check operations.

#### $this->rbac->user->setRoleId(int $roleId);

Sets role id of user to comfortable permission check operations.

#### $this->rbac->user->isAllowed(string $permName, array $permissions);

Checks permission name is allowed in your permission list.

#### $this->rbac->user->getStatement();

Returns to PDO Statement Object.






#################### Taşınacak Başlangıç.
Kaynak kimliklerini otomatik olarak tanımlayıp bunun ilgili bir filtre oluşturarak tüm sayfaların izin kontrollerini yapabilirsiniz.

```php
$c['router']->createFilter(
    'rbac.user',
    function () use ($c) {
        $c->load('rbac');

        $this->rbac->user->setId($this->user->identity->user_id);   // Yetkilendirme ( Authentication ) sınıfı yardımıyla
                                                                    // "user_id" 'yi Yetkiler sınıfına tanımlıyoruz.
        $this->rbac->user->setRoleIds($this->user->getRoles());     // Kullanıcı rolleri tanımlıyoruz.

        $url = $this->router->fetchTopDirectory() . '/' . $this->router->fetchDirectory() . '/' . $this->router->fetchClass();
        $this->rbac->resource->setId($url);                         // Kaynak kimliğini tanımlıyoruz.

        if (! $this->user->view->page['admin/marketing/index']->isAllowed()) {
            return $c->load('response')->show404();
        }
    }
);
```
#################### Taşınacak Son.