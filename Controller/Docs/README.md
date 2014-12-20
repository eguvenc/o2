
## Controllers

Controllers are the heart of your application, as they determine how HTTP requests should be handled.

### What is a Controller ?

------

**A Controller is simply a class file that is named in a way that can be associated with a URI.**

Consider this URI:

```php
example.com/index.php/blog/start
```

In the above example, framework would attempt to find a folder named <kbd>/welcome</kbd> in the <b>public</b> folder and it attempts to find a controller named <kbd>welcome.php</kbd> in the /controller folder and load it.

**When a controller's name matches the second segment of a URI, it will be loaded.**

### Let's try it: Hello World !

-------

Let's create a simple controller so you can see it in action. Create a directory called <kbd>blog</kbd> in the public folder

Then create <kbd>controller</kbd>  and <kbd>view</kbd> folders.

```php
-  app
-  public
    + welcome
    - welcome
       - controller
           welcome.php
       - view
           welcome.php

```

Using your text editor, create folder <kbd>welcome/controller</kbd> then create a file called <kbd>home.php</kbd> in the <kbd>welcome/controller</kbd> folder, and put the following code in it:

```php
<?php

Class Welcome extends Controller
{
    /**
     * Loader
     * 
     * @return void
     */
    public function load()
    {
        $this->c->load('url');
        $this->c->load('view');
    }

    /**
     * Index
     * 
     * @return void
     */
    public function index()
    {
        $this->view->load(
            'welcome',
            function () {
                $this->assign('name', 'Obullo');
                $this->assign('footer', $this->template('footer', false));
            }
        );
    }
}


/* End of file welcome.php */
/* Location: .public/welcome/controller/welcome.php */
```

Then save the file to your <kbd>public/welcome/controllers/</kbd> folder.

Now visit your site using a URL similar to this:

```php
demo-blog.com/index.php/welcome
```

### Functions <a name="functions"></a>

------

In the above example the function name is <kbd>index</kbd>. The "index" function is always loaded by default if the **second segment** of the URI is empty. Another way to show your "Hello World" message would be this:

```php
demo-blog.com/index.php/welcome/index/
```

**The third segment of the URI determines which function in the controller gets called.**

Let's try it. Add a new function to your controller:


### Passing URI Segments to your Functions

------

If your URI contains more then two segments they will be passed to your function as parameters.

For example, lets say you have a URI like this:

```php
shop.com/index.php/products/cars/classic/123
```

Your function will be passed URI segments number 3 and 4 ("classic" and "123"):

```php
<?php

Class Welcome extends Controller
{
    /**
     * Loader
     * 
     * @return void
     */
    public function load()
    {
        $this->c->load('view');
    }

    /**
     * Index
     * 
     * @return void
     */
    public function index($type, $id)
    {
        echo $type;           // Output  classic 
        echo $id;             // Output  123 
        echo $this->uri->segment(3);    // Output  123 
    }
}

/* End of file hello_world.php */
/* Location: .public/products/controller/cars.php */


```

**Important:** If you are using the URI Routing feature, the segments passed to your function will be the re-routed ones.

### Defining a Default Controller

------

Framework can be told to load a default controller when a URI is not present, as will be the case when only your site root URL is requested. To specify a default controller, open your config.php file and set this variable:

```php
<?php
$c['router']->domain($c->load('config')['url']['host']);  // Root domain

$c['router']->override('defaultController', 'welcome');  // This is the default controller, application call it as default
$c['router']->override('pageNotFoundController', 'errors/page_not_found');  // You can redirect 404 errors to specify controller

$c['router']->route('*', '/', 'welcome/$1/$2');   // Welcome route
$c['router']->route('*', 'product/([a-z]+)/([0-9]+)', 'products/cars/$1/$2');   // Welcome route

products/cars/classic/123

/* End of file routes.php */
/* Location: .routes.php */
```

Where <var>welcome</var> is the name of the <kbd>directory</kbd> and <var>welcome</var> controller class you want to use. If you now load your main index.php file without specifying any URI segments you'll see your Hello World message by default.

### Remapping Function Calls

-------

As noted above, the second segment of the URI typically determines which function in the controller gets called. Framework permits you to override this behavior through the use of the <kbd>_remap()</kbd> function:

```php
<?php

$app->func(
    '_remap',
    function () {

        // Some code here...
    }
);
```

**Important:** If your controller contains a function named <kbd>remap()</kbd> , it will **always** get called regardless of what your URI contains. It overrides the normal behavior in which the URI determines which function is called, allowing you to define your own function routing rules.
The overridden function call (typically the second segment of the URI) will be passed as a parameter the <kbd>_remap()</kbd> function:

```php
<?php

$app->func(
    '_remap',
    function () {
        if ($method == 'some_method') {
            $name = {'_'}.$method;
            $this->$name();
        } else {
            $this->_defaultMethod();
        }
    }
);
```

### Processing Output

------

Framework has an output class that takes care of sending your final rendered data to the web browser automatically. More information on this can be found in the Views and Output class pages. In some cases, however, you might want to post-process the finalized data in some way and send it to the browser yourself. Framework permits you to add a function named <kbd>_output()</kbd> to your controller that will receive the finalized output data.

**Important:** If your controller contains a function named <kbd>_output()</kbd>, it will always be called by the output class instead of echoing the finalized data directly. The first parameter of the function will contain the finalized output.

Here is an example:

```php
<?php

$app->func(
    '_output',
    function ($output) {
        echo $output;
    }
);
```

Please note that your <kbd>_output()</kbd> function will receive the data in its finalized state. Benchmark and memory usage data will be rendered. If you are using this feature the page execution timer and memory usage stats might not be perfectly accurate since they will not take into acccount any further processing you do. For an alternate way to control output <em>before</em> any of the final processing is done, please see the available methods in the Response Class.

### Private Functions

------

In some cases you may want certain functions hidden from public access. To make a function private, simply add <kbd>underscore</kbd> then it will not be served via a URL request. For example, if you were to have a function like this:

```php
<?php

$app->func(
    '_test',
    function ($output) {
        echo $output;
    }
);
```

Trying to access it via the URL, like this, will not work and framework will show "404 page not found" error:

```php
example.com/index.php/welcome/_test
```

### "One Public Method Per Controller" Rule

Just one public method allowed for each controllers. Framework has a principle "One Public Method Per Controller" we comply this rule otherwise application is entering a bottleneck.