
## O2 Authentication

Auth package provides an API for authentication and includes concrete authentication adapters for common use case scenarios. O2 Auth is concerned only with <b>authentication</b> and <b>not</b> with authorization. For more information about authorization please see <b>Permissions</b> package.

**Note:** Auth package cache storage only supports <b>Redis</b> driver at this time. Look at here for <a href="https://github.com/obullo/warmup/tree/master/Redis">redis installation</a>.

## Flow Chart

Below the flow chart shows authentication process of users:

* [Click to see flowchart](/Auth/Docs/images/flowchart.png?raw=true)

## Adapters

Auth adapter is used to authenticate against a particular type of authentication service, such as Database (RDBMS or NoSQL), or file-based. Different adapters are likely to have vastly different options and behaviors, but some basic things are common among authentication adapters. For example, accepting authentication credentials (including a purported identity), performing queries against the authentication service, and returning results are common to Auth adapters.

## Redis Storage

Auth class uses redis storage like database. The following picture shown an example authentication data stored in redis.

![PhpRedisAdmin](/Auth/Docs/images/redis.png?raw=true "PhpRedisAdmin")

### Predefined keys:

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
            <td>Contains online user activity data: lastActivity time and any other data you want to add.</td>
        </tr>
        <tr>
            <td>__isAuthenticated</td>
            <td>If user has authority this key contains <b>1</b> otherwise <b>0</b>.</td>
        </tr>
        <tr>
            <td>__isTemporary</td>
            <td>If verification method <kbd>$this->user->login->enableVerification()</kbd> used before login the temporary authentication value will be <b>1</b> otherwise <b>0</b>. If user verified by mobile phone or any kind of verification then you need authenticate user by using <kbd>$this->user->login->authenticateVerifiedIdentity()</kbd> method.</td>
        </tr>
        <tr>
            <td>__isVerified</td>
            <td>If user verified returns to <b>1</b> otherwise <b>0</b>.</td>
        </tr>
        <tr>
            <td>__lastTokenRefresh</td>
            <td>The config <b>security token</b> updates cookie and memory token value every <b>1</b> minutes by default. If memory token and cookie does not match then we logout the user. This is a strong security measure for hijacking session id or token. ( Refreshing time is configurable item from your auth.php config file )</td>
        </tr>
        <tr>
            <td>__rememberMe</td>
            <td>If user checked rememberMe input before login it contains to <b>1</b> otherwise <b>0</b>.</td>
        </tr>
        <tr>
            <td>__token</td>
            <td>Random token for security cookie.</td>
        </tr>
        <tr>
            <td>__type</td>
            <td>Contains authentication types of user: <b>Guest, Unverified, Authorized, Unauthorized</b>.</td>
        </tr>
        <tr>
            <td>__time</td>
            <td>Identity creation date in unix microtime format.</td>
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
            [last] => 1413454236
        )

    [__isAuthenticated] => 1
    [__isTemporary] => 0
    [__lastTokenRefresh] => 1413454236
    [__rememberMe] => 0
    [__token] => 6ODDUT3FtmmXEZ70.86f40e86
    [__type] => Authorized
    [__time] => 1414244130.719945
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
    'adapter' => 'Database',
    'memory' => array(          // Keeps user identitiy data in your cache driver.
        'key' => 'Auth',        // Auth key should be replace with your projectameAuth
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
            'name' => '__token',  // Cookie name, change it if you want
            'refresh' => 60,   // Every 1 minutes do the cookie validation
            'userAgentMatch' => false,  // Whether to match user agent when reading token
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
                'expire' => 6 * 30 * 24 * 3600,  // Default " 6 Months ".
            )
        ),
        'session' => array(
            'regenerateSessionId' => true,               // Regenerate session id upon new logins.
            'deleteOldSessionAfterRegenerate' => false,  // Destroy old session data after regenerate the new session id upon new logins
        )
    ),
    'activity' => array(
        'singleSignOff' => false,  // Single sign-off is the property whereby a single action of signing out terminates access to multiple sessions.
    )
);

/* End of file auth.php */
/* Location: .app/config/auth.php */
```

### Description Of Config Items

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
            <td>Adapter is used to authenticate against a particular type of authentication service, such as Database (RDBMS or NoSQL), or file-based.</td>
        </tr>
        <tr>
            <td>memory[key]</td>
            <td>This is Redis key, using same key may cause collison with your other projects. It should be replace with your "projectameAuth" ( e.g. frontendAuth, backendAuth ).</td>
        </tr>
        <tr>
            <td>memory[storage]</td>
            <td>Auth class uses a memory container to speed up your application default driver is Redis.</td>
        </tr>
        <tr>
            <td>memory[block][permanent][lifetime]</td>
            <td>Before the login action if verification is disabled by the <kbd>$this->user->login->disableVerification()</kbd> method user identity data is stored into <b>permanent</b> memory block otherwise it will be stored in temporary block. Permanent block expires after <b>3600</b> seconds by default. To speed up your login this feature prevents more than one login queries within the specified time period.</td>
        </tr>

        <tr>
            <td>memory[block][temporary][lifetime]</td>
            <td>Before the login action if verification is enabled by the <kbd>$this->user->login->enableVerification()</kbd> method user identity data is stored into <b>temporary</b> memory block otherwise it will be stored in permanent block. Temporary block expires after <b>300</b> seconds by default. 

            The temporary data is designed for the user <b>verification</b> protocols ( verification by <b>phone call</b>, verification by <b>SMS</b> etc.).
            <br />
            If the verification code you have generated is confirmed by the user within the specific time, the user information in the temporary data is updated and the user becomes authorized with the method <kbd>$this->user->login->authenticateVerifiedIdentity()</kbd>. Otherwise, while you do not use this method the temporary identity information will be <b>lost</b>.
            </td>
        </tr>

        <tr>
            <td>security[cookie]</td>
            <td>This precaution is taken for preventing user information and session id from being stolen. Randomly generated security token with the information special to browser information of the user is saved to memory container and these tokens are renewed within a certain periods (1 minute by default). When the token is renewed, the verification function runs and if the token in the memory is not equal to the token in the browser of the user, the session of the user is expired. The expiration time of a security token is recommended to be the same with the time of the rememberMe cookie (6 months by default).</td>
        </tr>

        <tr>
            <td>security[passwordNeedsRehash][cost]</td>
            <td>It is the length of the password hash and it should not exceed the 10, otherwise it may cause performance problems in your application.<b>Note:</b> If user password needs to be rehashed for the security purposes, run this method  <kbd>$this->user->identity->getPasswordNeedsReHash()</kbd> . If renewed, the method returns new hash password in the array format and the user password field in your database must be updated with the returned value. </td>
        </tr>

        <tr>
            <td>login[rememberMe]</td>
            <td>If the user wants their information to be kept in the browser permanently, a cookie with the name <b>__rm</b> is created and saved to a browser(The default expiration time of the cookie is 6 months).When the user comes different times, if this cookie exists in the user's browser and the user id is not defined in the session, this value is saved to the key <b>$_SESSION['__isAuthenticated\Identifier']</b>. The user information is recalled with the method <b>Auth\Recaller->recallUser($rememberToken)</b> and the users starts to be active in the site. This value is updated in the both database and cookie on every login and logout.</td>
        </tr>

        <tr>
            <td>login[session][regenerateSessionId]</td>
            <td>This is a security preacution for session id not to be stolen, if this option is active session id is updated on every login and the user information on the session is not removed.</td>
        </tr>

        <tr>
            <td>login[session][deleteOldSessionAfterRegenerate]</td>
            <td>If this option is active, during a login operation after session is regenerated, all the created information in the user's session is removed.</td>
        </tr>

        <tr>
            <td>activity[singleSignOff]</td>
            <td>Single sign-off is the property whereby a single action of signing out terminates access to multiple sessions. If this option is active, all sessions of the user expired and only the last session remains active.</td>
        </tr>
    </tbody>
</table>


## Tutorial

Controller Example

* <a href="https://gist.github.com/eguvenc/7cff67ebec6ebe3ca3c5" target="_blank">Click to see example code</a>

Vie Example

* <a href="https://gist.github.com/eguvenc/6ce279d1fb0e2378e611" target="_blank">Click to see example code</a>



## User ( Service )

------

User service class simply manage <b>login</b>, <b>identity</b> and <b>activity</b> modules of the auth.

## Login

------

The class Login manages the operations like login, authentication and verification. 

```php
<?php
$this->user->login->disableVerification();
$this->user->login->attempt(
    array(
        Auth\Credentials::IDENTIFIER => $this->post['email'], 
        Auth\Credentials::PASSWORD => $this->post['password']
    ),
    $this->post['rememberMe']
);
```

## Auth Results

AuthResult class ile sonuç doğrulama filtresinden geçer oluşan hata kodları ve mesajlar array içerisine kaydedilir.

```php
<?php
$result = $this->user->login->attempt(
    array(
        Auth\Credentials::IDENTIFIER => $this->post['email'], 
        Auth\Credentials::PASSWORD => $this->post['password']
    ),
    $this->post['rememberMe']
);

if ($result->isValid()) {

    // Go ..

} else {

    print_r($result->getArray());

    /* Array ( 
        [code] => -6 
        [messages] => Array ( 
            [0] => You are already logged in. 
        ) 
        [identifier] => user@example.com 
    ) 
    */
}
```

#### AuthResult Codes

<table>
    <thead>
        <tr>
            <th>Code</th>    
            <th>Constant</th>    
            <th>Description</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td>0</td>
            <td>AuthResult::FAILURE</td>
            <td>General Failure.</td>
        </tr>
        <tr>
            <td>-1</td>
            <td>AuthResult::FAILURE_IDENTITY_AMBIGUOUS</td>
            <td>Failure due to identity being ambiguous.( Found more than 1 identity record ).</td>
        </tr>
        <tr>
            <td>-2</td>
            <td>AuthResult::FAILURE_CREDENTIAL_INVALID</td>
            <td>Failure due to invalid credential being supplied.</td>
        </tr>
        <tr>
            <td>-3</td>
            <td>AuthResult::FAILURE_UNCATEGORIZED</td>
            <td>Failure due to uncategorized reasons.</td>
        </tr>
        <tr>
            <td>-4</td>
            <td>AuthResult::FAILURE_IDENTIFIER_CONSTANT_ERROR</td>
            <td>Failure due to idenitifer constant not matched with results array.</td>
        </tr>
        <tr>
            <td>-5</td>
            <td>AuthResult::FAILURE_ALREADY_LOGGEDIN</td>
            <td>Failure due to user already autheticated.</td>
        </tr>
        <tr>
            <td>-6</td>
            <td>AuthResult::FAILURE_UNHASHED_PASSWORD</td>
            <td>Failure due to user password not hashed.</td>
        </tr>
        <tr>
            <td>-7</td>
            <td>AuthResult::FAILURE_TEMPORARY_AUTH_HAS_BEEN_CREATED</td>
            <td>Failure due to temporary user auth has been created and not verified.</td>
        </tr>
        <tr>
            <td>-8</td>
            <td>AuthResult::FAILURE_UNVERIFIED</td>
            <td>Failure due to user account not verified.</td>
        </tr>
        <tr>
            <td>1</td>
            <td>AuthResult::SUCCESS</td>
            <td>Authentication success.</td>
        </tr>

    </tbody>
</table>


## Customizing Auth

Uygulamanın esnek olarak çalışması için auth modeli kimlik classları <b>app/classes/Auth</b> klasörü altında gruplanmıştır. Bu klasör o2 auth paketi ile senkron çalışır ve aşağıdaki dizindedir.

```php
- app
    - classes
        - Auth
            Identities
                - AuthorizedUser
                - GenericUser
        - Provider
            UserProvider.php
        Credentials.php
```

#### Class Descriptions

<table>
    <thead>
        <tr>
            <th>Class</th>    
            <th>Description</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td>Auth\Credentials</td>
            <td>Contains user database field <b>id</b> and <b>passwod</b> field constants.</td>
        </tr>
        <tr>
            <td>Auth\Identities\GenericUser</td>
            <td>Guest user identity.</td>
        </tr>
        <tr>
            <td>Auth\Identities\AuthorizedUser</td>
            <td>Authorized user identity.</td>
        </tr>
        <tr>
            <td>Auth\Provider\UserProvider</td>
            <td>Contains user database query sql methods.</td>
        </tr>
    </tbody>
</table>


## Identity

------

The class Identity manages the identity information and does the operations below:

* Reads data from the identity and saves the data to identity  
* Checks if the user has identity
* Checks if the identity is authorized
* Checks if the identity is permanent or not
* Makes the identity passive ( logout )
* Expires the identity ( destroy )
* Remembers the identity ( remeberMe ), removes the identity from the cookie ( forgetMe )


## Activity

------

The classs Activity acts as a container to manage the meta data of the logged in users. The instant information like the last action of the user is on which page is sent to the identity data from this container. In order for information to be written on the memory, the update() method needs to be run once at the bottom. When the user logs in <b>sid</b> (session id) value is sent to the inside of the activity data by default.

#### Adding activity data and update.

```php
<?php
$this->user->activity->set('date', time());
$this->user->activity->update();

// __activity a:3:{s:3:"sid";s:26:"f0usdabogp203n5df4srf9qrg1";s:4:"date";i:1413539421;}
```


### Extending to UserProvider

O2 Auth paketi kullanıcıya ait database fonksiyonlarını servis içerisinden Obullo\Auth\AuthUserProvider sınfından çağırmaktadır. Bu sınıfa genişlemek için önce Service/User sınıfından provider ı Auth\UserProvider olarak değiştirmeniz gerekmektedir.


```php

namespace Service;

use Obullo\Auth\UserService,
    Service\ServiceInterface;

Class User implements ServiceInterface
{
    /**
     * Registry
     *
     * @param object $c container
     * 
     * @return void
     */
    public function register($c)
    {            
        $c['user'] = function () use ($c) {
            return new UserService($c, $c->load('return service/provider/db'));
        };;
    }
}
```

Bunun için önce <b>app/classes/Auth/Provider</b> klasörünü yaratın. Daha sonra Database user provider aşağıdaki gibi yaratarak AbstractUserProvider sınıfına genişlemeniz gerekmektedir. Bunu yaparken UserProviderInterface içerisindeki  kurallara bir göz atın.

Aşağıdaki örnek olarak verimiştir aşağıdaki sınıfı ihtiyaçlarınıza göre değiştirebilirsiniz. Bunun içib Obullo\Auth\AbstractUserProvider sınıfına bakın ve override etmek istediğiniz method yada değişkenleri UserProvider sınıfı içersine dail edin.


```php

<?php

namespace Auth\Provider;

use Obullo\Auth\UserProviderInterface,
    Obullo\Auth\AuthUserProvider,
    Auth\Identities\GenericUser,
    Auth\Identities\AuthorizedUser,
    Auth\Credentials;

/**
 * O2 Auth - User Database Provider
 *
 * @category  Auth
 * @package   Provider
 * @author    Obullo Framework <obulloframework@gmail.com>
 * @copyright 2009-2014 Obullo
 * @license   http://opensource.org/licenses/MIT MIT license
 * @link      http://obullo.com/package/auth
 */
Class UserProvider extends AuthUserProvider implements UserProviderInterface
{
    /**
     * Constructor
     * 
     * @param object $c  container
     * @param object $db database
     */
    public function __construct($c, $db)
    {
        parent::__construct($c, $db);

        $this->tablename = 'users';                     // Db users tablename
        $this->rememberTokenColumn = 'remember_token';  // RememberMe token column name

        $this->userSQL = 'SELECT * FROM %s WHERE BINARY %s = ?';      // Login attempt SQL
        $this->recalledUserSQL = 'SELECT * FROM %s WHERE %s = ?';     // Recalled user for remember me SQL
        $this->rememberTokenUpdateSQL = 'UPDATE %s SET %s = ? WHERE BINARY %s = ?';  // RememberMe token update SQL
    }
    
    /**
     * Execute sql query
     *
     * @param object $user GenericUser object to get user's identifier
     * 
     * @return mixed boolean|object
     */
    public function execQuery(GenericUser $user)
    {
        // return parent::execQuery($user);
        
        $this->db->prepare($this->userSQL, array($this->tablename, Credentials::IDENTIFIER));
        $this->db->bindValue(1, $user->getIdentifier(), PARAM_STR);
        $this->db->execute();

        return $this->db->row();  // returns to false if fail
    }

}

// END UserProvider.php File
/* End of file UserProvider.php

/* Location: .app/classes/Auth/Provider/UserProvider.php */
```


### Events

By default we have two active user event in event/user class. Auth class use these methods when you use service/user class.

If you want you can release afterLogin and afterLogout events using fire method.

```php
<?php

namespace Event;

use Obullo\Auth\AuthResult,
    Obullo\Auth\User\UserIdentity;

/**
 * User event handler
 * 
 * @category  Event
 * @package   User
 * @author    Obullo Framework <obulloframework@gmail.com>
 * @copyright 2009-2014 Obullo
 * @license   http://opensource.org/licenses/MIT MIT license
 * @link      http://obullo.com/docs/event
 */
Class User
{
    /**
     * Container
     * 
     * @var object
     */
    protected $c;

    /**
     * Constructor
     *
     * @param object $c container
     */
    public function __construct($c)
    {
        $this->c = $c;
    }

    /**
     * Handle user login attempts
     *
     * @param object $authResult AuthResult object
     * 
     * @return void
     */
    public function onLoginAttempt(AuthResult $authResult)
    {
        if ( ! $authResult->isValid()) {

            // Store attemtps

        }
        return $authResult;
    }

    /**
     * Invalid auth token event listener
     * 
     * @param object $identity UserIdentity
     * @param string $cookie   user token that we read from cookie
     * 
     * @return void
     */
    public function onInvalidToken(UserIdentity $identity, $cookie)
    {
        $this->c->load('flash/session')->error('Invalid auth token : '.$cookie.' identity '.$identity->getIdentifier().' destroyed');
        $this->c->load('url')->redirect('/login');
    }

    /**
     * Handler user login events
     * 
     * @return void
     */
    public function onAfterLogin()
    {
        // ..
    }

    /**
     * Handle user logout events.
     *
     * @return void
     */
    public function onAfterLogout()
    {
        // ..
    }

    /**
     * Register the listeners for the subscriber.
     * 
     * @param object $event event class
     * 
     * @return void
     */
    public function subscribe($event)
    {
        $event->listen('login.attempt', 'Event\User.onLoginAttempt');
        $event->listen('after.login', 'Event\User.onAfterLogin');
        $event->listen('auth.token', 'Event\User.onInvalidToken');
        $event->listen('after.logout', 'Event\User.onAfterLogout');
    }

}

// END User class

/* End of file User.php */
/* Location: .Event/User.php */
```



### Login Reference

------

```php
<?php
$this->c->load('service/user');
$this->user->login->method();
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

### $this->user->login->validateCredentials(AuthorizedUser $user, array $credentials);

Validate a user against the given credentials.

$this->user->login->getAdapter();

Returns user service adapter object.

$this->user->login->getStorage();

Returns to user service storage object.

### Identity Reference

------

```php
<?php
$this->c->load('service/user');
$this->user->identity->method();
```

### $this->user->identity->exists();

Checks identity block available in memory. If yes returns to <b>true</b> otherwise <b>false</b>.

### $this->user->identity->check();

if user authenticated returns to <b>true</b> otherwise <b>false</b>.

### $this->user->identity->isVerified();

if user is verified () after successfull login returns to <b>true</b> otherwise <b>false</b>.

### $this->user->identity->guest();

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

### $this->user->identity->refreshRememberToken(GenericUser $genericUser);

Regenerates rememberMe token in <b>database</b>.

**Note:** When you use destroy method, user identity will removed from storage then new user login will do query to database for one time.


### Identity "Set" Methods

------

### $this->user->identity->variable = 'value'

Set a value to identity array.

### unset($this->user->identity->variable)

Remove value from identity array.

### $this->user->identity->setRoles(int|string|array $roles);

Set user roles to identity data.

### $this->user->identity->setArray(array $attributes)

Reset identity attributes with new values.


### Identity "Get" Methods

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

### $this->user->identity->getTime();

Returns to creation time of identity. ( Php Unix microtime ).

### $this->user->identity->getArray()

Returns to all user identity data ( attributes of user ).

### $this->user->identity->getToken();

Returns to security token.

### $this->user->identity->getRoles();

Gets role(s) of the user.


**Note:** You can define your own methods into <kbd>app/classes/Auth/Identities/AuthorizedUser</kbd> class.


### Activity Reference

------


Activity data contains online user activity data: lastActivity time or and any analytics data you want to add.

```php
<?php
$this->c->load('service/user');
$this->user->activity->method();
```

### $this->user->activity->set($key, $val);

Add item to activity data array.

### $this->user->activity->get($key);

Fetches an item from activity data array.

### $this->user->activity->update();

Updates all activity data if $this->user->activity->set(); method used before on this method.

### $this->user->activity->remove();

Removes all activity data from auth container.