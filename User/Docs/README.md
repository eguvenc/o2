
## User Agent Class

The User Agent Class provides functions that help identify information about the browser, mobile device, or robot visiting your site. In addition you can get referrer information as well as language and supported character-set information.

### Initializing an User Agent Class

------

```php
<?php
$this->c['user/agent as agent'];
$this->agent->method();
```
<blockquote>When the User Agent class is initialized it will attempt to determine whether the user agent browsing your site is a web browser, a mobile device, or a robot. It will also gather the platform information if it is available.</blockquote>

The following functions are available:

#### $this->agent->isBrowser();

Returns true or false (boolean) if the user agent is a known web browser.

```php
<?php
if ($this->agent->isBrowser()) {
    $browser = $this->agent->getBrowser();
    $browserVersion = $this->agent->getBrowserVersion();
}
```

<blockquote>The string "Safari" in this example is an array key in the list of browser definitions. You can find this list in .app/config/agents.php if you want to add new browsers or change the stings.</blockquote>

#### $this->agent->isRobot();

Returns true or false (boolean) if the user agent is a known robot.

```php
<?php
if ($this->agent->isRobot()) {
    echo 'This is a '. $this->agent->getRobotName() .' robot.';
}
```

<blockquote>The user agent library only contains the most common robot definitions. It is not a complete list of bots. There are hundreds of them so searching for each one would not be very efficient. If you find that some bots that commonly visit your site are missing from the list you can add them to your .app/config/agents.php file.</blockquote>

#### $this->agent->isMobile();

Returns true or false (boolean) if the user agent is a known mobile device.

```php
<?php
if ($this->agent->isMobile()) {
    $this->c['view']->load(
        'mobile/home',
        function () {
            $this->assign('name', 'Obullo');
            $this->assign('footer', $this->template('footer'));
        }
    );
}
```

#### $this->agent->isReferral();

Returns true or false (boolean) if the user agent was referred from another site.

```php
<?php
if ($this->agent->isReferral()) {
    $referrer = $this->agent->getReferrer();
}
```

#### $this->agent->getAcceptLang($lang = 'en');

Lets you determine if the user agent accepts a particular language. Example:

```php
<?php
if ($this->agent->getAcceptLang('en')) {
    echo 'Yes! Accept english!';
}
```
<blockquote>This function is not typically very reliable since some browsers do not provide language info, and even among those that do, it is not always accurate.</blockquote>


#### $this->agent->getAgent();

Returns a string containing the full user agent string. Typically it will be something like this:

* The PC:
    * Mozilla/5.0 (X11; Ubuntu; Linux x86_64; rv:34.0) Gecko/20100101 Firefox/34.0
    * Mozilla/5.0 (Macintosh; Intel Mac OS X 10_9_3) AppleWebKit/537.75.14 (KHTML, like Gecko) Version/7.0.3 Safari/537.75.14
    * Mozilla/5.0 (compatible; MSIE 10.0; Windows NT 6.1; WOW64; Trident/6.0)
* The Mobile Phone:
    * Mozilla/5.0 (Linux; Android 4.4.2; Nexus 4 Build/KOT49H) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/34.0.1847.114 Mobile Safari/537.36

#### $this->agent->getBrowser();

Returns a string containing the name of the web browser viewing your site.

* The PC:
    * Firefox
    * Safari
    * Internet Explorer
* The Mobile Phone:
    * Chrome

#### $this->agent->getBrowserVersion();

Returns a string containing the version number of the web browser viewing your site.

* The PC:
    * 34.0
    * 537.75.14
    * 10.0
* The Mobile Phone:
    * 34.0

#### $this->agent->getMobileDevice();

Returns a string containing the name of the mobile device viewing your site.

* Android

#### $this->agent->getRobotName();

Returns a string containing the name of the robot viewing your site.

#### $this->agent->getPlatform();

Returns a string containing the platform viewing your site (Linux, Windows, OS X, etc.).

* The PC:
    * Linux
    * Mac OS X
    * Windows 7
* The Mobile Phone:
    * Android

### Function Reference

------

#### $this->agent->isBrowser();

Returns true or false (boolean) if the user agent is a known web browser.

#### $this->agent->isRobot();

Returns true or false (boolean) if the user agent is a known robot.

#### $this->agent->isMobile();

Returns true or false (boolean) if the user agent is a known mobile device.

#### $this->agent->isReferral();

Returns true or false (boolean) if the user agent was referred from another site.

#### $this->agent->getAgent();

Get user agent

#### $this->agent->getPlatform();

Get platform

#### $this->agent->getBrowser();

Get browser name

#### $this->agent->getBrowserVersion();

Get the browser version

#### $this->agent->getRobotName();

Get The robot name

#### $this->agent->getMobileDevice();

Get the mobile device

#### $this->agent->getReferrer();

Get the referrer

#### $this->agent->getLanguages();

Get the accepted languages

#### $this->agent->getCharsets();

Get the accepted character sets

#### $this->agent->getAcceptLang($lang = 'en');

Test for a particular language

#### $this->agent->getAcceptCharset($charset = 'utf-8');

Test for a particular character set

#### $this->agent->getKey($keyName = null);

Get key