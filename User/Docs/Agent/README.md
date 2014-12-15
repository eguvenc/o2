
## User Agent Class

The User Agent Class provides functions that help identify information about the browser, mobile device, or robot visiting your site. In addition you can get referrer information as well as language and supported character-set information.
Initializing the Class

### Initializing an User Agent Class

------

```php
$c->load('user/agent');
$this->userAgent->method();
```
<blockquote>When the User Agent class is initialized it will attempt to determine whether the user agent browsing your site is a web browser, a mobile device, or a robot. It will also gather the platform information if it is available.</blockquote>

The following functions are available:

#### $this->userAgent->isBrowser();

Returns true or false (boolean) if the user agent is a known web browser.

```php
if ($this->userAgent->isBrowser()) {
    $browser = $this->userAgent->getBrowser();
    $browserVersion = $this->userAgent->getBrowserVersion();
}
```

<blockquote>The string "Safari" in this example is an array key in the list of browser definitions. You can find this list in .app/config/shared/agents.php if you want to add new browsers or change the stings.</blockquote>

#### $this->userAgent->isRobot();

Returns true or false (boolean) if the user agent is a known robot.

```php
if ($this->userAgent->isRobot()) {
    echo 'This is a '. $this->userAgent->getRobotName() .' robot.';
}
```

<blockquote>The user agent library only contains the most common robot definitions. It is not a complete list of bots. There are hundreds of them so searching for each one would not be very efficient. If you find that some bots that commonly visit your site are missing from the list you can add them to your .app/config/shared/agents.php file.</blockquote>

#### $this->userAgent->isMobile();

Returns true or false (boolean) if the user agent is a known mobile device.

```php
if ($this->userAgent->isMobile()) {
    $this->view->load(
        'mobile/home',
        function () {
            $this->assign('name', 'Obullo');
            $this->assign('footer', $this->template('footer'));
        }
    );
}
```

#### $this->userAgent->isReferral();

Returns true or false (boolean) if the user agent was referred from another site.

```php
if ($this->userAgent->isReferral()) {
    $referrer = $this->userAgent->getReferrer();
}
```

#### $this->userAgent->getAcceptLang($lang = 'en');

Lets you determine if the user agent accepts a particular language. Example:

```php
if ($this->userAgent->getAcceptLang('en')) {
    echo 'Yes! Accept english!';
}
```
<blockquote>This function is not typically very reliable since some browsers do not provide language info, and even among those that do, it is not always accurate.</blockquote>


#### $this->userAgent->getAgent();

Returns a string containing the full user agent string. Typically it will be something like this:

* The PC:
    * Mozilla/5.0 (X11; Ubuntu; Linux x86_64; rv:34.0) Gecko/20100101 Firefox/34.0
    * Mozilla/5.0 (Macintosh; Intel Mac OS X 10_9_3) AppleWebKit/537.75.14 (KHTML, like Gecko) Version/7.0.3 Safari/537.75.14
    * Mozilla/5.0 (compatible; MSIE 10.0; Windows NT 6.1; WOW64; Trident/6.0)
* The Mobile Phone:
    * Mozilla/5.0 (Linux; Android 4.4.2; Nexus 4 Build/KOT49H) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/34.0.1847.114 Mobile Safari/537.36

#### $this->userAgent->getBrowser();

Returns a string containing the name of the web browser viewing your site.

* The PC:
    * Firefox
    * Safari
    * Internet Explorer
* The Mobile Phone:
    * Chrome

#### $this->userAgent->getBrowserVersion();

Returns a string containing the version number of the web browser viewing your site.

* The PC:
    * 34.0
    * 537.75.14
    * 10.0
* The Mobile Phone:
    * 34.0

#### $this->userAgent->getMobileDevice();

Returns a string containing the name of the mobile device viewing your site.

* Android

#### $this->userAgent->getRobotName();

Returns a string containing the name of the robot viewing your site.

#### $this->userAgent->getPlatform();

Returns a string containing the platform viewing your site (Linux, Windows, OS X, etc.).

* The PC:
    * Linux
    * Mac OS X
    * Windows 7
* The Mobile Phone:
    * Android

### Function Reference

------

#### $this->userAgent->isBrowser();

Returns true or false (boolean) if the user agent is a known web browser.

#### $this->userAgent->isRobot();

Returns true or false (boolean) if the user agent is a known robot.

#### $this->userAgent->isMobile();

Returns true or false (boolean) if the user agent is a known mobile device.

#### $this->userAgent->isReferral();

Returns true or false (boolean) if the user agent was referred from another site.

#### $this->userAgent->getAgent();

Get user agent

#### $this->userAgent->getPlatform();

Get platform

#### $this->userAgent->getBrowser();

Get browser name

#### $this->userAgent->getBrowserVersion();

Get the browser version

#### $this->userAgent->getRobotName();

Get The robot name

#### $this->userAgent->getMobileDevice();

Get the mobile device

#### $this->userAgent->getReferrer();

Get the referrer

#### $this->userAgent->getLanguages();

Get the accepted languages

#### $this->userAgent->getCharsets();

Get the accepted character sets

#### $this->userAgent->getAcceptLang($lang = 'en');

Test for a particular language

#### $this->userAgent->getAcceptCharset($charset = 'utf-8');

Test for a particular character set

#### $this->userAgent->getKey($keyName = null);

Get key