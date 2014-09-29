
## Rate Limiter Class

This class requires an object class of "IP", "Username" or "MobilePhone".

------
### Service configuration

```
/**
 * Ip Listener Service
 *
 * @category  Service
 * @package   Ip
 * @author    Obullo Framework <obulloframework@gmail.com>
 * @copyright 2009-2014 Obullo
 * @license   http://www.gnu.org/licenses/gpl-3.0.html GPL Licence
 * @link      http://obullo.com/docs/container
 */
Class Ip implements ServiceInterface
{
	/**
	 * Ip listener
	 *
	 * @param object $c container
	 * 
	 * @return void
	 */
	public function register($c)
	{
	    $c['listenerIp'] = function ($params = array('config' => array())) use ($c) {
	        $ipListener = new ObulloIp($c, $params['ip'], $params['route'], $params['config']);
	        return new Limiter($c, $ipListener, $params);
	    };
	}
}
```

### Initializing the Class

```
$c->load('service/listener/ip as listenerIp');
$this->listenerIp->method();
```

### Limits

-----

#### How can I set limits

1.You can send config data.

```
$c->load(
	'service/listener/ip as listenerIp',
	$params = array(
        'ip' => $c->load('request')->getIpAddress(),
        'route' => 'Login',
        'config' => $config
	)
);
```

2.You can use this following functions.

```
$c->load(
	'service/listener/ip as listenerIp',
	$params = array(
        'ip' => $c->load('request')->getIpAddress(),
        'route' => 'Login',
        'config' => $config
	)
);
```
You can set new configs.

```php
$this->listenerIp->setIntervalLimit(
    $newConfig['interval_limit']['amount'],
    $newConfig['interval_limit']['limit']
);
$this->listenerIp->setHourlyLimit(
    $newConfig['hourly_limit']['amount'],
    $newConfig['hourly_limit']['limit']
);
$this->listenerIp->setDailyLimit(
    $newConfig['daily_limit']['amount'],
    $newConfig['daily_limit']['limit']
);
$this->listenerIp->setBanStatus($newConfig['ban']['status']);
$this->listenerIp->setBanExpiration($newConfig['ban']['expiration']);
```

#### How do I user limit control?

```
$ipAddress = $c->load('request')->getIpAddress();
$c->load(
    'service/listener/ip as listenerIp',
    array(
        'ip' => $ipAddress,
        'route' => 'Login',
        'config' => $config,
    )
);
if ( ! $this->listenerIp->isAllowed()) {
	$c->load('logger');
    $this->logger->notice(
        'Invalid login attempt user ip is banned from limiter service.',
        array(
            'category' => 'failedLogin',
            'data' => array(
                'username' => $username,
                'ip_address' => $ipAddress,
            )
        )
    );
    $this->logger->push(LOGGER_MONGO);
    return $this->listenerIp->getError();
}
if (condition) {
	$this->listenerIp->increaseLimit(); // This is optional.
} else {
	$this->listenerIp->reduceLimit();	// This is important.
}
```

#### What is isAllowed() function?

This function is a function of all the controls is made.

** What are these controls? **

1. The first check, whether the user is control of the ban.
2. Limiti dolan kullanıcı, tekrar istek yapabilmek için süresinin dolmasını beklemek zorundadır. Bu yüzden son istek zamanıyla tanımlanan süre karşılaştırılır. Süresi dolmuş ise limitleri sıfırlanır.
	- **Note**: Only the expired limits are reset, all limits not reset.
3. User is controlled limits.
	* Daily limit
	* Hourly limit
	* Interval limit

#### The user successful request to increase the limit.

For each successful request applies to all defined limits +1 limits also increasing.

This is optional.

```
if ( ! $this->listenerIp->isAllowed()) {
    return $this->listenerIp->getError();
} else {
	$this->listenerIp->increaseLimit();
}
```

#### The user successful request to reduce the limit.

For each successful request applies to all defined limits +1 limits also reducing.

```
if ( ! $this->listenerIp->isAllowed()) {
    return $this->listenerIp->getError();
} else {
	$this->listenerIp->reduceLimit();
}
```

This is not optional. This reduce function is required for limits control.

### User Bans

-----

If you want to ban the user, ban must be turned on. Ban feature is off by default.

```
$this->listenerIp->setBanStatus($enable = true);
$this->listenerIp->setBanExpiration($expiration = 300); // sec
```

#### Remove ban manually.

```
$this->listenerIp->removeBan();
```

### Function Reference

-----

#### $this->listenerIp->increaseLimit();

Increase limit

#### $this->listenerIp->reduceLimit();

Reduce limit

#### $this->listenerIp->isAllowed();

Is allowed

#### $this->listenerIp->setBanStatus($enable = true);

Enable ban. $enable variable default true but in function $this->isBanActive variable default false.

#### $this->listenerIp->setBanExpiration();

Set ban expiration time.

#### $this->listenerIp->isBanned();

Is banned.

#### $this->listenerIp->removeBan();

Remove ban.