
## Request Class

Request class detects the server request method, secure connection, ip address, ajax requests and other similar things.

### Initializing the Class

------

```php
<?php
$this->c->load('request');

$this->request['variable'];
$this->request->method();
```

Once loaded, the Request object will be available using: <dfn>$this->request->method()</dfn>

#### $this->request['variable'];

if variable is exists in $_REQUEST global it returns to itself otherwise false.

#### $this->request->getHeader(string $key);

Fetches the http server header.

Using getHeader() method

```php
<?php
echo $this->request->getHeader('host'); // demo_blog
echo $this->request->getHeader('content-type'); // gzip, deflate
echo $this->request->getHeader('connection'); // keep-alive
```
An example http header output

```php
print_r(getallheaders());

// 
// EXAMPLE HEADER OUTPUT
// Host: demo_blog 
// User-Agent: Mozilla/5.0 (X11; Ubuntu; Linux x86_64; rv:26.0) Gecko/20100101 Firefox/26.0 Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/\*;q=0.8
// Accept-Language: en-US,en;q=0.5 
// Accept-Encoding: gzip, deflate 
// 
// Cookie: frm_session=uqdp8hvjsfhen759eucgp31h74; frm_session_userdata=a%3A4%3A%7Bs%3A10%3A%22session_id%22%3Bs%3A26%3A%22uqdp8hvjsfhen759eucgp31h74%22%3Bs%3A10%3A%22ip_address%22%3Bs%3A9%3A%22127.0.0.1%22%3Bs%3A10%3A%22user_agent%22%3Bs%3A50%3A%22Mozilla%2F5.0+%28X11%3B+Ubuntu%3B+Linux+x86_64%3B+rv%3A26.0%29+G%22%3Bs%3A13%3A%22last_activity%22%3Bi%3A1389947182%3B%7D75f0224d5214efb875c685a30eda7f06
// 
// Connection: keep-alive 
```

#### $this->request->server($key);

Fetches $_SERVER variable items.

```php
<?php
$this->request->server('HTTP_USER_AGENT');  

// gives Mozilla/5.0 (X11; Ubuntu; Linux x86_64; rv:26.0) Gecko/20100101 Firefox/26.0 
```

#### $this->request->getIpAddress();

Returns the IP address for the current user. If the IP address is not valid, the function will return an IP of: 0.0.0.0

```php
<?php
echo $this->get->ipAddress();  // 216.185.81.90
```

#### $this->request->isValidIp($ip);

Gets the IP address as input and returns true or false (boolean) depending on it is valid or not. 

***Note:*** The $this->request->getIpAddress() method also validates the IP automatically.

```php
<?php
if ( ! $this->request->isValidIp($ip)) {
	echo 'Not Valid';
} else {
	echo 'Valid';
}
```

#### $this->request->isValidIp();

#### $this->request->getIpAddress();

#### $this->request->server();

#### $this->request->getHeader();

#### $this->request->getMethod();

#### $this->request->isLayer();

Returns "true" if the secure connection ( Https ) available in server header.

#### $this->request->isAjax();

Returns "true" if xmlHttpRequest ( Ajax ) available in server header.

#### $this->request->isSecure();

Returns "true" if the secure connection ( Https ) available in server header.

#### $this->request->isPost();

If http request method equal to POST returns to true otherwise false.

#### $this->request->isGet();

If http request method equal to GET returns to true otherwise false.

#### $this->request->isPut();

If http request method equal to PUT returns to true otherwise false.

#### $this->request->isDelete();

If http request method equal to DELETE returns to true otherwise false.


### Some popular array access examples:

* $this->get['variable']  ( $_GET )
* $this->post['variable']  ( $_POST )
* $this->request['variable']  ( $_REQUEST )
* $this->config['variable']  ( Retrieves Config class items )
* $this->translator['variable']  ( Retrieves Translator file items )