
## O2 Rol Tabanlı Erişim Kontrolü ( Role-based access control - RBAC )

RBAC Nedir ?

RBAC Kullanıcılar, Roller ve İzinler kavramlarını ayırır. Aslında erişim kontrolü, bir kaynağa erişimin ve o kaynağın kullanımının kişilere yetkiler vererek ve kısıtlamalar getirerek sınırlanmasıdır. Oluşturmuş olduğumuz <b>Roller</b> kullanıcıyla ilişkilendirilerek, kullanıcının hangi izinlere sahip olması gerektiğine rollere tanımladığımız <b>Yetkiler</b> sayesinde karar verebiliriz.

O2 yetkiler; 

* Sayfalara erişim,
* Linkler,
* Formlar,
* Form Elementleri,

gibi mevcut özellikleri ile istediğimiz tüm sayfa, nesne ve elementlere hızlı ve güvenli bir yetkilendirme için kullanabileceğiniz bir servis sağlar.

## Sınıfı yüklemek

Yetkiler paketi sınıflarına erişim rbac servisi üzerinden sağlanır, bu servis önceden <b>.app/classes/Service</b> dizininde <b>Rbac.php</b> olarak konfigure edilmiştir Uygulamanınızın sürdürülebilirliği açısından bu servis üzerinde database provider haricinde değişiklilik yapmamanız önerilir. <b>Rbac</b> sınıfı yetkiler servisine ait olan <b>User</b>, <b>Roles</b> ve <b>Permissions</b> gibi sınıfları bu servis üzerinden kontrol eder, böylece paket içerisinde kullanılan tüm public sınıf metodlarına tek bir sınıf üzerinden erişim sağlanmış olur.

Rbac servisi bir kez çağrıldığı zaman bu servis içerisinden ilgili kütüphane metotları çalıştırılabilir.

```php
$this->c->load('service/rbac');
$this->rbac->class->method();
```

Aşağıda verilen örnek prototipler size yetkiler sınıfı metodlarına <b>rbac</b> servisi üzerinden nasıl erişim sağlandığı hakkında bir fikir verebilir.

<b>User</b> için bir örnek

```php
$this->rbac->user->method();
```

User sınıfının kullanımı ve metot örnekleri için [Permissions/Docs/Rbac/User/README.md]'ye bakabilirsiniz.

<b>Roles</b> için bir örnek

```php
$this->rbac->roles->method();
```
Roles sınıfının kullanımı ve metot örnekleri için [Permissions/Docs/Rbac/Roles/README.md]'ye bakabilirsiniz.

<b>Permissions</b> için bir örnek

```php
$this->rbac->permissions->method();
```
Permissions sınıfının kullanımı ve metot örnekleri için [Permissions/Docs/Rbac/Permissions/README.md]'ye bakabilirsiniz.

<b>Resource</b> için bir örnek

```php
$this->rbac->resource->method();
```
Resource sınıfının kullanımı ve metot örnekleri için [Permissions/Docs/Rbac/Resource/README.md]'ye bakabilirsiniz.