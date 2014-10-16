
## O2 Authentication

Auth package provides an API for authentication and includes concrete authentication adapters for common use case scenarios. O2 Auth is concerned only with <b>authentication</b> and <b>not</b> with authorization. For more information about authorization please see <b>Permissions</b> package.

**Note:** Auth package cache storage only supports <b>Redis</b> driver at this time. Look at here for <a href="https://github.com/obullo/warmup/tree/master/Redis">redis installation</a>.

## Flow Chart

Below the flow chart shows authentication process of users:

## Adapters

Auth adapter is used to authenticate against a particular type of authentication service, such as AssociativeArray (RDBMS or NoSQL), or file-based storage. Different adapters are likely to have vastly different options and behaviors, but some basic things are common among authentication adapters. For example, accepting authentication credentials (including a purported identity), performing queries against the authentication service, and returning results are common to Auth adapters.

## Redis Storage

Auth class uses redis storage like database. The following picture shown an example authentication data stored in redis.

![PhpRedisAdmin](/Auth/Docs/Auth/images/redis.png?raw=true "PhpRedisAdmin")

### Package predefined keys:

Auth package build its own variables which keys are start by 2 underscore "__". You should not change these variables by manually.

<table>
    <thead>
        <tr>
            <th>Key</th>    
            <th>Description</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td>__activity</td>
            <td>Contains online user activity data: session id (sid), lastActivity time and any other data you want to add.</td>
        </tr>
        <tr>
            <td>__isAuthenticated</td>
            <td>If user has authority this key contains "1" otherwise "0".</td>
        </tr>
        <tr>
            <td>__isTemporary</td>
            <td>If verification method <kbd>$this->user->login->enableVerification()</kbd> used before login the temporary authentication value will be "1" otherwise "0". If user verified by mobile phone or any kind of verification then you need authenticate user by using <kbd>$this->user->login->authenticateVerifiedIdentity()</kbd> method.</td>
        </tr>
        <tr>
            <td>__lastTokenRefresh</td>
            <td>The config <b>security token</b> updates cookie and memory token value every <b>1</b> minutes by default. If memory token and cookie does not match we logout the user. This is a strong security measure for hijacking session id or token. ( Refreshing time is configurable item from your auth.php config file )</td>
        </tr>
        <tr>
            <td>__rememberMe</td>
            <td>If user checked rememberMe input before login it contains to "1" otherwise "0".</td>
        </tr>
        <tr>
            <td>__token</td>
            <td>Random token value.</td>
        </tr>
        <tr>
            <td>__type</td>
            <td>Contains authentication types of user: <b>Guest, Unverified, Authorized, Unauthorized</b>.</td>
        </tr>

    </tbody>
</table>

Example output of the identity

```php
<?php
print_r($this->user->identity->getArray()); // Gives
/*
Array
(
    [__activity] => Array
        (
            [sid] => 0ri8fsfoksutisaifioq60mu16
            [lastActivity] => 1413454236
        )

    [__isAuthenticated] => 1
    [__isTemporary] => 0
    [__lastTokenRefresh] => 1413454236
    [__rememberMe] => 0
    [__token] => 6ODDUT3FtmmXEZ70.86f40e86
    [__type] => Authorized
    [id] => 1
    [password] => $2y$10$0ICQkMUZBEAUMuyRYDlXe.PaOT4LGlbj6lUWXg6w3GCOMbZLzM7bm
    [remember_token] => bqhiKfIWETlSRo7wB2UByb1Oyo2fpb86
    [username] => user@example.com
)
*/
```

## Configuration

```php
<?php
/*
|--------------------------------------------------------------------------
| Auth
|--------------------------------------------------------------------------
| Configuration file
|
*/
return array(
    'adapter' => 'AssociativeArray',
    'memory' => array(          // Keeps user identitiy data in your cache driver.
        'storage' => 'Redis',   // Storage driver uses cache package
        'block' => array(
            'permanent' => array(
                'lifetime' => 3600  // 1 hour is default storage life time. if remember choosed we use "rememberMeSeconds" value as lifetime otherwise default.
            ),
            'temporary'  => array(
                'lifetime' => 300  // 5 minutes is default temporary login lifetime.
            )
        )
    ),
    'security' => array(
        'cookie' => array(
            'name' => '__ot',  // Cookie name, change it if you want
            'refresh' => 60,   // Every 1 minutes do the cookie validation
            'path' => '/',
            'secure' => false,
            'httpOnly' => false,
            'prefix' => '',
            'expire' => 6 * 30 * 24 * 3600,  // Default " 6 Months ". Should be same with rememberMeSeconds value.
        ),
        'passwordNeedsRehash' => array(
            'cost' => 10
        ),
    ),
    'login' => array(
        'rememberMe'  => array(
            'cookie' => array(
                'name' => '__rm',
                'path' => '/',
                'secure' => false,
                'httpOnly' => false,
                'prefix' => '',
                'expire' => 6 * 30 * 24 * 3600,  // Default " 6 Months ". Should be same with rememberMeSeconds value.
            )
        ),
        'session' => array(
            'regenerateSessionId' => true,               // Regenerate session id upon new logins.
            'deleteOldSessionAfterRegenerate' => false,  // Destroy old session data after regenerate the new session id upon new logins
        )
    ),
    'activity' => array(
        'singleSignOff' => false,  // Single sign-off is the property whereby a single action of signing out terminates access to multiple agents.
    )
);

/* End of file auth.php */
/* Location: .app/config/shared/auth.php */
```

## Description Of Config Items

<table>
    <thead>
        <tr>
            <th>Key</th>    
            <th>Description</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td>adapter</td>
            <td></td>
        </tr>
    </tbody>
</table>


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

### $this->user->identity->exists();

Checks identity block available in memory. If yes returns to <b>true</b> otherwise <b>false</b>.

### $this->user->identity->isAuthenticated();

if user is autheticated returns to <b>true</b> otherwise <b>false</b>.

### $this->user->identity->isVerified();

if user is verified () after successfull login returns to <b>true</b> otherwise <b>false</b>.

### $this->user->identity->isGuest();

Checks if the user is guest, if so, it returns to <b>true</b> otherwise <b>false</b>.

### $this->user->identity->isTemporary();

Returns to <b>1</b> if user authenticated on temporary memory block otherwise <b>0</b>.

### $this->user->identity->logout();

Logs out user, sets __isAuthenticated key to <b>0</b>. This method <kbd>does not destroy</kbd> the user <kbd>sessions</kbd>. It will just set authority of user to <b>0</b>.

**Note:** When you use logout method, user logins will work on memory storage if cached auth exists.

### $this->user->identity->destroy();

Destroys all identity stored in memory. 

### $this->user->identity->forgetMe();

Removes the rememberMe cookie.

### $this->user->identity->refreshRememberToken(GenericIdentity $genericUser);

Regenerates rememberMe token in <b>database</b>.

**Note:** When you use destroy method, user identity will removed from storage then new user login will do query to database for one time.

### Identity Get Methods

------

### $this->user->identity->getIdentifier();

Get the unique identifier for the user.

### $this->user->identity->getPassword();

Returns to hashed password of the user.

### $this->user->identity->getType();

Get user type who has successfull memory token using by their session identifier. User types : <b>UNVERIFIED, AUTHORIZED</b>.

### $this->user->identity->getRememberMe();

Get rememberMe option if user choosed returns to <b>1</b> otherwise <b>0</b>.

### $this->user->identity->getPasswordNeedsReHash();

Checks the password needs rehash if yes returns to <b>array</b> that contains new hashed password otherwise <b>false</b>.

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

### $this->user->activity->set($key, $val);

Add activity data to user.

### $this->user->activity->get($key);

Get activity data item of user.

### $this->user->activity->remove();

Remove activity key from container.

### $this->user->activity->isSignedIn();

Returns <b>true</b> if user online.

### $this->user->activity->isSignedOut();

Returns <b>false</b> if user not online.

### $this->user->activity->update();

Updates all activity data of the user which we set them before using $this->user->activity->set(); method.