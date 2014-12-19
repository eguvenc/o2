
## Router Class

------

The router class allows you to remap the URLs. 

### Initializing a Router Class

------

```php
<?php
$c->load('router');
$this->router->method();
```

Typically there is a one-to-one relationship between a URL string and its corresponding <kbd>directory/class/arguments</kbd>. The segments in a URI normally follow this pattern:

```php
example.com/directory/class/id/
```

In some instances, however, you may want to remap this relationship so that a different class/function can be called instead of the one corresponding to the URL.

```php
example.com/index.php/shop/product/1
```

For example, lets say you want your URLs to have this prototype:

```php
example.com/shop/product/1/
example.com/shop/product/2/
```

Normally the second segment of the URL is reserved for the class name (show), but in the example above it instead has a product. To overcome this, router allows you to remap the URI handler.

### Anatomy of Routing

------

Here is a routing example:

```php
      // Request filter     // Url request         // Real Process
  *|post|get|put|delete    example.com/product/4     shop/product/4
           _ _ _ _ _ _ _      _ _ _ _ _ _ _           _ _ _ _ _ _ _ _
                     |             |                        |
                     |             |                        |
$c['router']->route('get', 'product/([0-9])', 'shop/product/$1');                            
```

A URL with "product" as the first segment, and anything in the second will be remapped to the "shop" directory, "product" class and "arguments" passing in the match as a variable to the function.

**Note:**  Default method is always <b>index</b> you don't need to write index method.

The first parameter set request type of route.

### Route Request Types

<table>
  <thead>
    <tr>
    <th>Name</th><th>Description</th>
    </tr>
  </thead>
  <tbody>
    <tr>
    <td>*</td>
    <td>Any type of request</td>
    </tr>
    <tr>
    <td>post</td>
    <td>Setting route as $_POST request</td>
    </tr>
    <tr>
    <td>get</td>
    <td>Setting route as $_GET request</td>
    </tr>
    <tr>
    <td>put</td>
    <td>Setting route as $_PUT request</td>
    </tr>
    <tr>
    <td>delete</td>
    <td>Setting route as $_DELETE request</td>
    </tr>
    <tr>
    <td>post|get</td>
    <td>You can use pipe to assign multiple request to one route</td>
    </tr>
  </tbody>
</table>

**Note:** Splitting routes allows to us filter them.


### Configure Your Domain

Below the configuration sets your hostname for routing which they stored into this domain.

```php
<?php
$c['router']->domain('example.com');  //  Root domain
```

An example local configuration


```php
<?php
$c['router']->domain('localhost'); 
```

You should set your domain without <b>"www."</b>

```php
<?php
$c['router']->domain('myproject.com'); 
```

### Setting Your Routing Rules

------

Routing rules are defined in your <kbd>routes.php</kbd> file. In it you'll see route functions that permits you to specify your own routing criteria.

```php
<?php
$c['router']->route('*', 'welcome(.*)', 'tutorials/hello_world/$1');  // any matches with  example.com/welcome
```

Routes can either be specified using <kbd>/wildcards</kbd> or <kbd>Regular Expressions</kbd>.

```php
<?php
$c['router']->route('*', 'welcome/(.+)', 'tutorials/hello_world/$1'); // any matches with  example.com/welcome/
```

Above the example a URL containing the word "welcome/$arg/$arg .." in the first segment will be remapped to the "tutorials/hellow_world/$arguments".


### Examples

------

If you prefer you can use regular expressions to define your routing rules. Any valid regular expression is allowed, as are back-references.

**Note:** If you use back-references you must use the dollar syntax rather than the double backslash syntax.

A typical RegEx route might look something like this:

```php
<?php
$c['router']->route('*', '([0-9]+)/([a-z]+)', 'welcome/$1/$2');
```

In the above example, a URI similar to <kbd>example.com/1/test</kbd> call the <kbd>welcome</kbd> controller class index method with <kbd>1 - 2</kbd> arguments.


```php
<?php
$c['router']->route(
    '*', 'welcome/[0-9]+/[a-z]+', 'welcome/$1/$2', 
    function () use ($c) {
        $c->load('view')->load('dummy');  // load  public/welcome/view/dummy.php
    }
);
```

In the above example, a URI similar to <kbd>example.com/welcome/123/test</kbd> call the <kbd>welcome</kbd> controller class index method with <kbd>123 - test</kbd> arguments.

And also your closure function run in router level.


```php
<?php
$c['router']->route(
    '*', 'welcome/{id}/{name}', null,
    function ($directory, $id, $name) use ($c) {
        $c->load('response')->show404($directory.'-'.$id.'-'.$name);
    }
)->replace(array('id' => '([0-9]+)', 'name' => '([a-z]+)'));
```

In the above example, a URI similar to <kbd>example.com/welcome/123/test</kbd> call the <kbd>welcome</kbd> controller class and arguments belonging to our url scheme.

```php
<?php
$c['router']->route(
    'get', '{id}/{name}/{any}', 'tutorials/hello_world/$1/$2/$3',
    function ($id, $name, $any) use ($c) {
        echo $id.'-'.$name.'-'.$any;
    }
)->replace(array('id' => '([0-9]+)', 'name' => '([a-z]+)', 'any' => '(.+)'));
```

In the above example URI scheme <kbd>{id}/{name}/{any}</kbd> replaced with your regex then if correct uri matched a URI like <kbd>welcome/123/electronic/mp3_player/</kbd> rewrite your kroute as <kbd>tutorials/hello_world/123/electronic/mp3_player/</kbd> and sends arguments to your closure function.

```php
<?php
$c['router']->route(
    '*', 'shop/{id}/{name}', null,
    function ($directory, $id, $name) use ($c) {
        
        $db = $c->load('return db');
        $db->prepare('SELECT * FROM products WHERE id = ?');
        $db->bindValue(1, $id, PARAM_INT);
        $db->execute();

        if ($db->row() == false) {
            $c->load('response')->showError(sprintf('The product %s not found', $name));
        }
    }
)->replace(array('id' => '([0-9]+)', 'name' => '([a-z]+)'));
```

In the above example URI scheme <kbd>shop/{id}/{name}</kbd> replaced with your regex then if correct uri matched a URI like <kbd>example.com/shop/123/mp3_player</kbd> sends arguments to your closure function.


### Set Your Default Controller

------

Default Controller : "welcome"

```php
<?php
$c['router']->defaultPage('tutorials/hello_world');
```
This route indicates which controller class should be loaded if the URI contains no data, which will be the case when people load your root URL. In the above example, the "start" class would be loaded. You are encouraged to always have a default route otherwise a 404 page will appear by default.


### Set Your 404 Error Controller

------

```php
<?php
$c['router']->error404('errors/page_not_found');
```

**Important:** This method must come before any wildcard or regular expression routes.


### Sub-Domain Routing

Creating route group for shop.example.com domain.

```php
<?php
$c['router']->group(
    array('name' => 'shop', 'domain' => 'shop.example.com'), 
    function ($group) {
        $this->route('get', 'welcome/(.+)', 'tutorials/hello_world', null, $group);
        $this->route('*', 'product/{id}', 'product/list/$1', null, $group);
    }
);
```

Creating route group for account.example.com domain.

```php
<?php
$c['router']->group(
    array('name' => 'accounts', 'domain' => 'account.example.com'), 
    function ($group) {
        $this->route(
            'get', '{id}/{name}/{any}', 'user/account/$1/$2/$3',
            function ($id, $name, $any) {
                echo $id.'-'.$name.'-'.$any;
            }
        )->replace(array('id' => '([0-9]+)', 'name' => '([a-z]+)', 'any' => '(.+)'));
    }
);
```

### Route Filters

You can define your custom route filters from filters.php

#### The anatomy of a Filter

In order to understand how a filter works, let’s break one down by look at one of the most important, the authentication filter:

```php
<?php
/*
|--------------------------------------------------------------------------
| Auth
|--------------------------------------------------------------------------
| Auth filter
*/
$c['router']->createFilter(
    'auth',
    function () use ($c) {
      if ($this->user->identity->isGuest()) {
        $c->load('url')->redirect('/login');
      }
    }
);
```

#### Attaching a Filter to a Route

Once you have the filter set up, you need to attach it to a route in order for it to take effect.

To attach a filter, simply pass it as an argument in the array of the second argument of a Route method definition:

```php
<?php
$c['router']->attach('tutorials/hello_world', 'before.filters' => array('auth'));
```

#### Group Filters

Using a route pattern is perfect when you want to attach a filter to a very specific set of routes like above. However it’s often the case that your routes won’t fit into a nice pattern and so you would end up with multiple pattern definitions to cover all eventualities.

A better solution is to use Group Filters:

```php
<?php
$c['router']->group(
    array('name' => 'test', 'before.filters' => array('auth')) 
    function ($group) {
        $this->attach('tutorials/hello_form', $group);
        $this->attach('tutorials/hello_world', $group);
    }
);
```

### Attaching Filter A Route Group


```php
<?php
$c['router']->group(
    array('name' => 'shop', 'domain' => 'shop.example.com', 'before.filters' => array('auth')), 
    function ($group) {
        $this->route('get', 'welcome/.+', 'tutorials/hello_world', null, $group);
        $this->route('*', 'product/{id}', 'product/list/$1', null, $group);

        $this->attach('.*', $group); // attach to all urls 
    }
);
```

#### Filter Classes

In all of the examples above, we used Closures to hold the logic of the filter. Obullo also allows you to create a specific class for your custom defined filter.

Why would you want to do this? Well if you have many particular complex filters, it will probably make sense to abstract them away from the filters.php file to prevent that file from getting messy. This will make organising and maintaining your filters a lot easier.

Filter classes also use IoC Container. This means that they will automatically be able to use dependancy injection so you can very easily test that they are working correctly.

Open your filters.php file then put below the content.

```php
<?php
/*
|--------------------------------------------------------------------------
| Hello Filter
|--------------------------------------------------------------------------
| Example class filter
*/
$c['router']->createFilter('hello', 'HelloFilter');
```

An example of a filter class could be:

```php
<?php
class HelloFilter {
 
  public function __construct($c)
  {
      $this->post = $c->load('post');

      if ($this->post['apikey'] != '123456') {
        echo json_encode(array(
            'error' => 'Your api key is not valid'
        ));
        die;
      }
  }
}
```

Then attach your filter in routes.php

```php
<?php
$c['router']->attach('tutorials/hello_world.*', array('before.filters' => array('hello')));
```

Filters allow you to very easily abstract complex route access logic into concise and easy to use nuggets of code. This allows you to define the logic once, but then apply it to many different routes.

#### Example Filter

* filters.php

```php
<?php
$c['router']->createFilter(
    'rewriteLocale',
    function () use ($c) {
      $c->load('url')->redirect('en'. $this->uri->getRequestUri());
    }
);
```

* routes.php

```php
<?php
$c['router']->attach('tutorials/hello_world.*', array('before.filters' => array('rewriteLocale')));
```

#### Language Filters

Advanced Example

* filters.php

```php
<?php
/*
|--------------------------------------------------------------------------
| Redirect locale
|--------------------------------------------------------------------------
| Current: http://example.com/news/sports/
|
| If URL doesn't contain language abridgement 'en, tr, de, nl',
| it will be added to URL.
| 
| Then: http://example.com/en/news/sports
*/
$c['router']->createFilter(
    'redirectLocale',
    function () use ($c) {
        $locale = $c->load('cookie')->get('locale');
        $languages = $c['config']->load('translator')['languages'];
        if ( ! isset($languages[$locale])) {
            $locale = $this->translator->getLocale();
        }
        $c->load('url')->redirect($locale. '/' . $this->uri->getUriString());
    }
);
```

* routes.php

```php
<?php
$c['router']->group(
    array('name' => 'redirectLocale', 'domain' => '^www.example.com$|^example.com$', 'before.filters' => array('redirectLocale')),
    function ($group) {

        $this->defaultPage('welcome');
        $this->route('get', '(?:en|tr|de|nl)/(.*)', '$1', null, $group);  // Dispatch request for http://example.com/en/folder/class
        $this->route('get', '(?:en|tr|de|nl)', 'welcome/index',  null, $group);  // if request http://example.com/en  -> redirect it to default controller

        $this->attach('/', $group);         // Filter only works for below the urls
        $this->attach('welcome', $group);
        $this->attach('sports/.*', $group);
        $this->attach('support/.*', $group);
    }
);
```

#### Using Regex For Filters

<b>Scenario:</b> We have sub domains like this <kbd>sports19.example.com</kbd> or <kbd>sports4.example.com</kbd> so we need to do maintenance page filter for <kbd>sports\d+.example.com/tutorials/hello_word</kbd> page.

Example:

```php
<?php
$c['router']->group(
    array('domain' => 'sports.*\d.example.com', 'before.filters' => array('maintenance')),
    function ($group) {
        $this->defaultPage('welcome');
        $this->attach('tutorials/hello_world.*', $group);
    }
);
```

#### Creating Maintenance Filters

Maintenance filters display maintenance page using configured maintenance function.

Open your filters.php file then put below the content.

```php
<?php
$c['router']->createFilter('maintenance', 'Http\Filters\MaintenanceFilter');
```

Then we can assign our domain to filter using attach method.

Open your routes.php file then put below the content.

```php
<?php
$c['router']->group(
    array('name' => 'general', 'domain' => $c['config']->xml->host->all, 'before.filters' => array('maintenance')), 
    function ($group) {

        $this->defaultPage('welcome/index');
        $this->attach('tutorials/hello_world.*', $group); // attached to "sports" sub domain "/tutorials/hello_world/" url.
    }
);
```

Configure example for <b>All Website</b> and <b>all</b> urls.

```php
<?php
$c['router']->group(
    array('name' => 'general', 'domain' => $c['config']->xml->host->all, 'before.filters' => array('maintenance')), 
    function ($group) {

        $this->defaultPage('welcome/index');
        $this->attach('.*', $group); // all urls of your domain
    }
);
```

**Note:** <b>$c['config']->xml->host->all</b> fetches your config.xml "<app><all> .. </all<app/>" keys as <b>simpleXmlElement object</b>.


Then go to your console and type:

```php
php task route all down
```

Now your application show maintenance view for all pages.

Configure example for <b>reverse</b> urls.

```php
<?php
$c['router']->group(
    array('domain' => $c['config']->xml->host->sports, 'before.filters' => array('maintenance', 'auth')), 
    function ($group) {
        $this->attach('((?!tutorials/hello_world).)*$', $group);  // all urls which not contains "tutorials/hello_world"
    }
);
```

Then go to your console and type:

```php
php task route all down
```

Now go to your console and type:

```php
php task route all up
```

Now your application "all" is up for your visitors.


#### Creating Https Filter

Open your filters.php file thn put below the content.

```php
<?php
/*
|--------------------------------------------------------------------------
| Https Filter
|--------------------------------------------------------------------------
| Force to https connection
*/
$c['router']->createFilter(
    'https://',
    function () use ($c) {
        if ($c->load('request')->isSecure() == false) {
          $c->load('url')->redirect('https://'.$this->getDomain() . $c->load('uri')->getRequestUri());
        }
    }
);

/* End of file filters.php */
/* Location: .filters.php */
```

Then attach your filter using routes.php

```php
<?php
$c['router']->attach('tutorials/hello_world.*', array('before.filters' => array('https://'));

/* End of file routes.php */
/* Location: .routes.php */
```

### Route Reference

------

#### $c['router']->domain(string $domain);

Sets a your default domain.

#### $c['router']->defaultPage(string $pageController);

Sets your default controller.

#### $c['router']->error404(string $errorController);

Sets your error controller.

#### $c['router']->route(string $method, string $match, string $rewrite, object $closure = null, array $group = array)

Creates a route.

#### $c['router']->group(array $options, $closure);

Creates a route group.

#### $c['router']->replace(array $replace);

Replaces your route schema with arguments.


### Filter Reference

------

#### $c['router']->createFilter($route, array $options = array())

Creates route filter.

#### $c['router']->attach($route, array $options = array())

Attach your route to defined filters.


### Function Reference

------

#### $this->router->getDomain();

Gets the currently working domain.

#### $this->router->fetchTopDirectory();

Gets the currently working top directory name.

#### $this->router->fetchDirectory();

Gets the currently working directory name.

#### $this->router->fetchClass();

Gets the currently working directory name.

#### $this->router->getFilters();

Returns to registered filters.