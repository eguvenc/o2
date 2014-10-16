
## O2 Authentication

Auth package provides an API for authentication and includes concrete authentication adapters for common use case scenarios. O2 Auth is concerned only with <b>authentication</b> and <b>not</b> with authorization. For more information about authorization please see <b>Permissions</b> package.

**Note:** Auth package cache storage only supports <b>Redis</b> driver at this time. Look at here for <a href="https://github.com/obullo/warmup/tree/master/Redis">redis installation</a>.

## Flow Chart

Below the flow chart shows authentication process of users:

## Adapters

Auth adapter is used to authenticate against a particular type of authentication service, such as AssociativeArray (RDBMS or NoSQL), or file-based storage. Different adapters are likely to have vastly different options and behaviors, but some basic things are common among authentication adapters. For example, accepting authentication credentials (including a purported identity), performing queries against the authentication service, and returning results are common to Auth adapters.


## User ( Service )

------

User service class simply manage <b>login</b>, <b>identity</b> and <b>activity</b> modules of the auth.


## Identity

## Login

## Activity


### Login Reference

------

```php
<?php
$c->load('service/user');
$this->user->class->method();
```

### $this->user->login->enableVerification();

If verification enabled, after successfull login memory storage creates a temporary identity. After a while temporary auth delete by storage if its not verified by the user.

### $this->user->login->disableVerification();

Disabled verification option.

### $this->user->login->attemp(array $credentials, $rememberMe = false);

Do login attempt and if login success gives authority and identity to the user.

### $this->user->login->authenticateVerifiedIdentity();

After verification, method authenticate temporary identity and removes old temporary data.

### $this->user->login->validate(array $credentials);

Validate a user's credentials without authentication.

### $this->user->login->validateCredentials(UserIdentity $user, array $credentials);

Validate a user against the given credentials.


### Identity Reference

------

### $this->user->identity->isAuthenticated();

Returns "true" if user is autheticated otherwise "false".

### $this->user->identity->isVerified();

Returns "true" if user is verified () after successfull login otherwise "false".

### $this->user->identity->isGuest();

Checks if the user is guest, if so, it returns to true, otherwise false.

### $this->user->identity->isTemporary();

Returns to "1" if user authenticated on temporary memory block otherwise  "0".

### $this->user->identity->logout();

Logs out user, sets __isAuthenticated key to "0". This method <kbd>does not destroy</kbd> the user <kbd>sessions</kbd>. It will just set authority data to "0".

**Note:** When you use logout method, user logins will work on memory storage if cached auth exists.

### $this->user->identity->destroy();

Destroys all identity stored in memory. 

**Note:** When you use destroy method, user identity will removed from storage then new user login will do query to database for one time.

### Identity Get Methods

------

### $this->user->identity->getIdentifier();

Get the unique identifier for the user.

### $this->user->identity->getPassword();

Returns to hashed password of the user.

### $this->user->identity->getType();

Get user type who has successfull memory token using by their session identifier. User types : UNVERIFIED, AUTHORIZED.

### $this->user->identity->getRememberMe();

Returns to "1" user if used remember me option.

### $this->user->identity->getPasswordNeedsReHash();

If user password needs rehash returns to "hashed password" string otherwise "false".

### $this->user->identity->getArray()

Returns to all user identity data ( attributes of user ).

### $this->user->identity->setArray(array $attributes)

Set new identity attributes.

### $this->user->identity->setRoles(int|string|array $roles);

Save user roles to your memory storage.

### $this->user->identity->getRoles();

Gets role(s) of the user.


### Identity Magic Methods

------

### $this->user->identity->variable

Gets value from identity array.

### $this->user->identity->variable = 'value'

Set value to identity array.

### unset($this->user->identity->variable)

Remove value from identity array.


### Activity Reference

------

### $this->user->activity->add();

Add user to online members.

### $this->user->activity->remove();

Remove user from online members.

### $this->user->activity->isOnline();

Returns true if user is online otherwie false.

### $this->user->activity->refreshTime();

Updates last activity time of the user.

### $this->user->activity->setAttribute($key, $value);

Adds custom item to online user data.

### $this->user->activity->getAttribute($key, $value);