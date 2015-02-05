
## Password Class

Password class produces secure password hash using Bcrypt algorithm.

------

### Load Password Bcrypt

```php
<?php

$this->c['password/bcrypt as password'];
$this->password->method();
```

### Hashing Password

Creates a password hash.

```php
<?php

$value   = 'obulloFramework';
$options = array('cost' => 12); // Default cost 10.
echo $this->password->hash($value, $options)
// Gives
// $2y$10$g6KqDmd.qZPQMaBnzhOeW.tYq03iqBe/.f3flea2zlzwyHWKBJVnm
```
Returns the hashed password, or false on failure.

### Verifying Password

Verifying passwords is just as easy;

```php
<?php

$hash  = '$2y$10$g6KqDmd.qZPQMaBnzhOeW.tYq03iqBe/.f3flea2zlzwyHWKBJVnm';
$value = 'obulloFramework'

if ($this->password->verify($value, $hash)) { // bool(true)
    echo 'Password is valid!';
} else {
    echo 'Invalid password.';
}
```

returns true if the password and hash match, or false otherwise.

### Rehashing password

Checks if the given hash matches the given options

```php
<?php

$hash    = '$2y$10$g6KqDmd.qZPQMaBnzhOeW.tYq03iqBe/.f3flea2zlzwyHWKBJVnm';
$options = array('cost' => 12); // Default cost 10.
$value   = 'obulloFramework'

if ($this->password->verify($value, $hash)) {
    echo 'Password is valid!';
	if ($this->password->needsRehash($hash, $options)) {
		$hash = $this->password->hash($value, $options);
		// update hash in database
	}
} else {
    echo 'Invalid password.';
}
```

### Getting info

Getting information about the given hash.

```php
<?php

$hash = '$2y$10$g6KqDmd.qZPQMaBnzhOeW.tYq03iqBe/.f3flea2zlzwyHWKBJVnm';
var_dump($this->password->getInfo($hash));

// Gives
array(3) {
  ["algo"]=>
  int(1)
  ["algoName"]=>
  string(6) "bcrypt"
  ["options"]=>
  array(1) {
    ["cost"]=>
    int(10)
  }
}
```

### Why should I use Bcrypt ?

Bcrypt produces secure password hash using blowfish algorithm.

- Its slowness and multiple rounds ensures high security (an attacker must deploy massive hardware to be able to crack the passwords).
- Bcrypt is a one-way hashing algorithm. This means that you cannot "decode" the plain password.
- Bcrypt class hashes each password with a different salt.

<b>MD5</b> is a good method to obscure <b>non-sensitive data</b>, because it's quite fast.
However, this is a big disadvantage when it comes to password hashing. With [rainbow tables](http://en.wikipedia.org/wiki/Rainbow_table), MD5 hashes can be very easily “decoded”.

That's the point where Bcrypt comes into play. Using a work factor of *12*, Bcrypt hashes the password in about *0.3 seconds*. MD5, on the other hand, takes less than *a microsecond*. 

> Read more about [why you should use Bcrypt](http://phpmaster.com/why-you-should-use-bcrypt-to-hash-stored-passwords/).

<a name="scheme"></a>

### Scheme 

To alter the default scheme, change the variable `$_identifier` of the class to one of the following parameters (without $-signs):

- `$2a$` - Hash which is potentially generated with the buggy algorithm.
- `$2x$` - "compatibility" option the buggy Bcrypt implementation.
- `$2y$` - Hash generated with the new, corrected algorithm implementation *(crypt_blowfish 1.1 and newer)*.

---

**Note:** The default scheme is `$2y$`, which makes use of the new, corrected hash implementation.  
*Other schemes should only be used when comparing values produced by an old version.*

---

### Structure

```php
$2a$12$Some22CharacterSaltXXO6NC3ydPIrirIzk1NdnTz0L/aCaHnlBa
```

- `$2a$` tells PHP to use which [Blowfish scheme](#scheme) *(Bcrypt is based on Blowfish)*
- `12$`  is the number of iterations the hashing mechanism uses.
- `Some22CharacterSaltXXO` is a random salt *(by OpenSSL)*

#### Diagram

```php
$2a$12$Some22CharacterSaltXXO6NC3ydPIrirIzk1NdnTz0L/aCaHnlBa
\___________________________/\_____________________________/
  \                            \
   \                            \ Actual Hash (31 chars)
    \
     \  $2a$   12$   Some22CharacterSaltXXO
        \__/    \    \____________________/
          \      \              \
           \      \              \ Salt (22 chars)
            \      \
             \      \ Number of Rounds (work factor)
              \
               \ Hash Header
```

> Diagram based on [Andrew Moore's structure](http://stackoverflow.com/a/5343655).

---


### Function Reference

-----

#### $this->password->hash(string $value, array $options = array());

Creates a password hash.

#### $this->password->verify(string $value, string $hashedValue);

Verifies that a password matches a hash.

#### $this->password->needsRehash(string $hashedValue, array $options = array());

Checks if the given hash matches the given options.

#### $this->password->getInfo(string $hash);

Returns information about the given hash.
