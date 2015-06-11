
## Response Class

The Response class is a small class with one main function: To send the finalized web page to the requesting browser. It is also responsible for compress your web pages, if you use that feature.

<ul>
    <li><a href="#loading-class">Sınıfı Yüklemek</a></li>
    <li>
        <a href="#super-globals">Girdi Değerlerine Erişmek</a>
        <ul>
            <li><a href="#sget">$this->request->get()</a></li>
            <li><a href="#spost">$this->request->post()</a></li>
            <li><a href="#sall">$this->request->all()</a></li>
            <li><a href="#sserver">$this->request->server()</a></li>
            <li><a href="#smethod">$this->request->method()</a></li>
            <li><a href="#sip">$this->request->getIpAddress()</a></li>
            <li><a href="#sheaders-get">$this->request->headers->get()</a></li>
            <li><a href="#sheaders-all">$this->request->headers->all()</a></li>
        </ul>
    </li>
</ul>


```php
$this->response->method();
```

Uygulamada response sınıfı otomatik olarak yüklü gelir.

Under normal circumstances you won't even notice the <b>Response</b> class since it works transparently without your intervention. For example, when you use the <kbd>new keyword</kbd> to load a view file, it's automatically passed to the <b>Reponse</b> class, which will be called automatically by Obullo at the end of system execution.

It is possible, however, for you to manually intervene with the <b>Response</b> if you need to, using either of the two following functions:

#### $this->response->setOutput(string $data);

Permits you to manually set the final output string. Usage example:

```php
$this->response->setOutput($data);
```

**Important:** If you do set your output manually, it must be the last thing done in the function you call it from. For example, if you build a page in one of your controller functions, don't set the output until the end.


#### $this->response->setOutput();

Permits you to manually append to final output string. Usage example:

```php
$this->response->setOutput($data);
```

#### $this->response->getOutput();

Permits you to manually retrieve any output that has been sent for storage in the output class. Usage example:

```php
$string = $this->response->getOutput();
```

Note that data will only be retrievable from this function if it has been previously sent to the output class by one of the <kbd>vi</kbd> package functions like <kbd>view()</kbd>.

#### $this->response->compressOutput();

Compress output using <b>ob_gz_handler</b> if global compression disabled from your config.

```php
namespace Welcome;

class HelloWorld extends \Controller
{
    public function load()
    {
        $this->c['view'];
    }
    
    public function index()
    {
        $this->view->load('hello_world');
    }
}

/* End of file hello_world.php */
/* Location: .modules/Welcome/HelloWorld.php */
```

#### $this->response->headers->set(string $header, string $value = null, $replace = true);

Permits you to manually set server headers, which the output class will send for you when outputting the final rendered display. Example:

```php
$this->reponse->headers->set("HTTP/1.0 200 OK");
$this->reponse->headers->set("HTTP/1.1 200 OK");
$this->reponse->headers->set("Last-Modified", gmdate('D, d M Y H:i:s', $lastUpdate).' GMT');
$this->reponse->headers->set("Cache-Control", "no-store, no-cache, must-revalidate");
$this->reponse->headers->set("Cache-Control", "post-check=0, pre-check=0");
$this->reponse->headers->set("Pragma", "no-cache");
```

```php
$this->c['response']->headers->set("Content-type", "application/json");
```

#### $this->reponse->setHttpResponse(code, 'text');

Permits you to manually set a server status header. Example:

```php
$this->reponse->status('401');  // Sets the header as:  Unauthorized
```

[See here](http://www.w3.org/Protocols/rfc2616/rfc2616-sec10.html) for a full list of headers.

#### $this->response->json(array $data, $headers = true);

Returns json encoded string with json headers. Second paramater disable json headers.

```php
/**
 * $app create
 * 
 * @var Controller
 */
$app = new Controller;

$app->func(
    'index',
    function () {
        echo $this->response->json(array('test'));  // gives [ "test" ]
    }
);
```

### Error Functions and Headers

------

#### $this->response->show404();



#### $this->response->setOutput(string $output);

Print output to screen and sends http headers.

#### $this->response->getOutput(string $output);

#### $this->response->append(string $output);

Append output to response body.

#### $this->response->finalize();

#### $this->response->enableOutput();

#### $this->response->disableOutput();

#### $this->response->headers->set();
#### $this->response->headers->get();
#### $this->response->headers->remove();
#### $this->response->headers->all();

#### $this->response->sendHeaders();

#### $this->response->flush();

Writes output to screen.


#### $this->response->json(array $data, mixed $header = 'default');

Returns to json encoded string and sends header if second parameter not false. If you don't want to send headers set second paramater as false.

#### $this->response->status($code = 401, 'text');

Permits you to manually set a server status header.

[See here](http://www.w3.org/Protocols/rfc2616/rfc2616-sec10.html) for a full list of headers.


#### $this->response->getStatus();


#### $this->response->show404();

Generates <b>404 Page Not Found</b> errors using html template file.

#### $this->response->showError(string $message, $status_code = 500, $heading = 'An Error Was Encountered');

Manually shows an error to users using html template file.
