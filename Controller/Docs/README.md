
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
    - welcome
       - controller
           welcome.php
       - view
           welcome.php

```

Using your text editor, create folder <kbd>welcome/controller</kbd> then create a file called <kbd>welcome.php</kbd> in the <kbd>welcome/controller</kbd> folder, and put the following code in it:

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
    public function index()
    {
        $this->c['view']->load(
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
example.com/index.php/welcome
```

### Functions <a name="functions"></a>

------

Controller içerisinde en fazla iki adet public method kullanılabilir. Bunlardan birincisi <b>load</b> metodudur.

Birinci public method <b>load</b> __construct methodu gibi çalışır, __construct yerine load kullanmamızın nedeni temel controller içerisindeki __construct metodundaki yani parent::__construct() yazımından bağımsız bir loader kullanabilmektir. Load metodu mevcut ise içerisine bulunan tüm container nesneleri controller içerisine kendiliğinden kaydedilir.

İkinci public method örnekte görüldüğü gibi index metodudur. Bu metod içerisinde uygulama işlemleri gerçekleşir.

### One Public Method Per Controller

Uygulamada bakım kolaylıgı sağlamak amacıyla genel bir prensip olarak method her zaman <b>"index"</b> tir. Controller içerisine index dışına her hangi bir metod ile ulaşılamaz. Eğer bir private method tanımlamak istiyorsanız metod "_" underscore öneki ile başlamalıdır.


if the **second segment** of the URI is empty. Another way to show your "Hello World" message would be this:

```php
example.com/index.php/welcome/index/
```

**The third segment of the URI determines which function in the controller gets called.**

Let's try it. Add a new function to your controller:


### Passing URI Segments to your Functions

------

If your URI contains more then two segments they will be passed to your function as parameters.

For example, lets say you have a URI like this:

```php
example.com/index.php/products/cars/classic/123
```

Your function will be passed URI segments number 3 and 4 ("classic" and "123"):

```php
<?php

Class Cars extends Controller
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

/* End of file cars.php */
/* Location: .public/products/controller/cars.php */


```

**Important:** If you are using the URI Routing feature, the segments passed to your function will be the re-routed ones.

### Defining a Default Controller

------

Framework can be told to load a default controller when a URI is not present, as will be the case when only your site root URL is requested. To specify a default controller, open your config.php file and set this variable:

```php
<?php
$c['router']->domain($c['config']['url']['webhost']);  // Root domain

$c['router']->override('defaultController', 'welcome');  // This is the default controller, application call it as default
$c['router']->override('pageNotFoundController', 'errors/page_not_found');  // You can redirect 404 errors to specify controller

$c['router']->route('*', '/', 'welcome/$1/$2');   // Welcome route
$c['router']->route('*', 'product/([a-z]+)/([0-9]+)', 'products/cars/$1/$2');   // Welcome route

products/cars/classic/123

/* End of file routes.php */
/* Location: .routes.php */
```

Where <var>welcome</var> is the name of the <kbd>directory</kbd> and <var>welcome</var> controller class you want to use. If you now load your main index.php file without specifying any URI segments you'll see your Hello World message by default.


### Private Functions

------

In some cases you may want certain functions hidden from public access. To make a function private, simply add <kbd>underscore</kbd> then it will not be served via a URL request. For example, if you were to have a function like this:

```php
<?php
/**
 * Private method
 * 
 * @return void
 */
public function _test()
{
    echo "This is my controller private function";
}
```

Trying to access it via the URL, like this, will not work and framework will show "404 page not found" error:

```php
example.com/index.php/welcome/_test
```

### Annotations

------

An annotation is metadata (e.g. a comment, explanation, presentational markup) attached to text, image, or other data. Often annotations refer to a specific part of the original data. 

At this time we use annotations just for filters.

### Available Annotaion Filters

<table>
    <thead>
        <tr>
            <th>Method</th>    
            <th>Description</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td><b>@filter->before("name");</b></td>
            <td>Initialize filter before executing of controller.</td>
        </tr>
        <tr>
            <td><b>@filter->after("name");</b></td>
            <td>Initialize filter after executing of controller.</td>
        </tr>
        <tr>
            <td><b>@filter->load("name");</b></td>
            <td>Initialize filter executing of controller load method.</td>
        </tr>
        <tr>
            <td><b>@filter->method("post","get");</b></td>
            <td>Allow index method when http methods matched.</td>
        </tr>
         <tr>
            <td><b>@filter->before("name")->when("post","get")</b></td>
            <td>Initialize filter when http methods matched.</td>
        </tr>

    </tbody>
</table>

### Enabling Controller Annotations

Open main config.php file then update annotations as true.

```php
<?php
/*
|--------------------------------------------------------------------------
| Controller
|--------------------------------------------------------------------------
*/
'controller' => array(
    'annotation' => array(
        'reader' => true,
    )
)
```

Now you can use annotation filters on <b>index</b> method.


```php
<?php

/**
 * Index
 *
 * @filter->before("activity")->when("get", "post");
 * 
 * @return void
 */
public function index()
{
    // ..
}


/* End of file welcome.php */
/* Location: .public/welcome/controller/welcome.php */
```

<b>Examples</b>

```php
<?php
/**
 * Index
 *
 * @filter->before("csrf");
 * @filter->method("post","get");
 *
 * @return void
 */
```

```php
<?php
/**
 * Index
 *
 * @filter->before("csrf")->when("post");
 * 
 * @return void
 */
```

```php
<?php
/**
 * Index
 *
 * @filter->before("auth")->when("get", "post");
 * @filter->after("benchmark");
 *
 * @return void
 */
```

### Processing Response

------

Framework has an response class that takes care of sending your final rendered data to the web browser automatically. More information on this can be found in the Views and Response class pages. In some cases, however, you might want to post-process the finalized data in some way and send it to the browser yourself. Framework permits you to add a function named <kbd>response()</kbd> to your controller that will receive the finalized output data.

**Note:** If your controller contains a function named <kbd>response()</kbd>, it will always be called by the response class instead of echoing the finalized data directly. The first parameter of the function will contain the finalized output.

Here is an example:

```php
<?php
/**
 * Custom Response
 * 
 * @return void
 */
public function _response($output)
{
    echo $output;
}
```

Please note that your <kbd>response()</kbd> function will receive the data in its finalized state. For an alternate way to control output <em>before</em> any of the final processing is done, please see the available methods in the Response Class.


### Reserved Controller Methods

* load()
* extend()