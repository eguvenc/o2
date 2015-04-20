

## What is Layers ?

------

Layers is a programming technique extensively used in Obullo and derived from ( HMVC ) Hierarchical Controllers ( Java ) which allows to you "Multitier Architecture" to make your application more scalable.

### What is Hmvc  ?

------

#### Hierarchical-Model-View-Controller ( HMVC )

"Hmvc pattern" decomposes the client tier into a hierarchy of parent-child <b>MVC</b> layers. The repetitive application of this pattern allows for a structured client-tier architecture.
<a href="http://www.javaworld.com/article/2076128/design-patterns/hmvc--the-layered-pattern-for-developing-strong-client-tiers.html" target="_blank">Click to See a Reference About Hmvc.</a>

## Layers

### Diagrams

Layers has two interface raw and json layers.

#### Raw Layers

"Layered Vc" decomposes the client tier into a hierarchy of parent-child <b>Vc</b> layers. The repetitive application of this pattern allows for a structured client-tier architecture.

<b>All layers</b> accesible from your visitors. Response format is <b>raw</b> for <b>View</b> layers.

```php
View Layers

      Controller
        ------  
        | c  | -------
        ------        \
             \         \
              -------   \
              |  v  |    \
              -------     \  View Controller ( Raw )
                          ------
                          | c  |
                          ------ 
                                 \
                                -------   
                                |  v  |
                                ------- 

```

#### Json Layers

Json layers same as view layers main difference is response format is <b>json</b> and they can be used for seperating form validations or caching some heavy operations.

```php
Json Layers

      Controller
        ------  
        | c  | -------
        ------        \
             \         \
              -------   \
              |  v  |    \
              -------     \  Controller ( Database )
                          ------
                          | c  |
                          ------ 
                                 \
                                -------   
                                |  v  |
                                ------- 

```

Below the example simply demonstrate <b>Layers</b> technique.

```php
------------------------------------------------------------------------------------
|                                                                                   |
|               echo $this->layer->get('views/header');                             |
|                                                                                   |
|-----------------------------------------------------------------------------------|
|                                                                                   |
|             $r = $this->layer->post('jsons/membership/create_user');              |
|                                                                                   |
|             if ($r['success']) {                                                  |
|                  // user created successfully.                                    |
|             } else {                                                              |
|                  print_r($r['errors']);                                           |
|             }                                                                     |
|                                                                                   |
-------------------------------------------------------------------------------------
|                                                                                   |
|                echo $this->layer->get('views/footer');                            |
|                                                                                   |
-------------------------------------------------------------------------------------
```

After that success operation we can redirect user to another page or echo error message into same page.

If validation fail we print the error messages to screen.

## Layer Class

Layer class creates your layers then manage layer traffic and cache mechanism using with an unique id. Layer has cache service dependecy that is located in <b>app/classes/service/cache.php</b>

#### A Layer request creates the random connection string ( Layer ID ) as the following steps.

*  The request method gets the uri and serialized string of your data parameters.
*  Then it builds a Layer ID with <b>unsigned Crc32 hash</b>.
*  Finally Layer ID added to end of your uri.
*  "Cache Service" use Layer ID as a <b>cache key</b> in <b>caching</b> mechanism.

### Initializing the Class

------

Layer class defined as core service in your application root <b>components.php</b>. Don't forget services always use same instance.

```php
<?php
$c->load('layer');
$this->layer->method();
```
Once loaded, the Layer object will be available using: <dfn>$this->layer->method()</dfn>


### Cache Usage

```php
<?php
$this->layer->get('views/header', array('user_id' => 5), $expiration = 7200);
```
Above the example will do cache for user_id = 5 parameter. ( If you use cache option you need to configure your cache driver. ( Redis, Memcache, Apc .. ) ).

## View Layers

View Layers returns to <b>raw</b> output. Framework keeps view type layers in views folder.

#### Folder Structure

```php
+ app
+ o2
- public
      - welcome
          - controller
              welcome.php
          + view
      - views
          - controller
              header.php
          - view
              header.php
```

<b>Public</b> folder are <b>visible</b> from your visitors. It contains controller folder and each layers is accessible via <b>http</b> requests.

An Example View Controller ( Header Controller )

```php
<?php

/**
 * $app header
 *
 * @var Header Controller
 */
$app = new Controller(
    function ($c) {
        $c->load('url');
        $c->load('view');
    }
);

$app->func(
    'index',
    function () {
        $navbar = array(
            'home'    => 'Home',
            'about'   => 'About', 
            'contact' => 'Contact',
            'membership/login'   => 'Login',
            'membership/signup'  => 'Signup',
        );
        foreach ($navbar as $key => $value) {
            $li.= '<li>'.$this->url->anchor($key, $value, " $active ").'</li>';
        }
        echo $this->view->load(
            'header',
            function () use ($li) {
                $this->assign('li', $li);
            },
            false
        );
    }
);

/* End of file header.php */
/* Location: .public/views/controller/header.php */
```

Above the example header controller manage your navigation bar 


## Nested Layers

You can call "layers in layers" with theirs views we call this way as nested.

```php
<?php
/**
 * $app hello_world
 * 
 * @var Controller
 */
$app = new Controller(
    function ($c) {
        $c->load('layer');
    }
);

$app->func(
    'index', 
    function () use ($c) {
        echo $this->layer->get('welcome/welcome_dummy/1/2/3');
    }
);

/* End of file hello_world.php */
/* Location: .public/tutorials/controller/hello_world.php */
```

Above the example we call the welcome layer from hello world controller.

```php
<?php
/**
 * $app welcome_dummy
 * 
 * @var Controller
 */
$app = new Controller(
    function ($c) {
        $c->load('view');
        $c->load('layer');
    }
);

$app->func(
    'index', 
    function ($arg1 = '', $arg2 = '', $arg3 = '') {
        
        echo $this->layer->get('views/header');
        echo $this->layer->get('views/footer');

        echo $this->view->nested($this)->load('dummy', false);
    }
);

/* End of file welcome_dummy.php */
/* Location: .public/tutorials/controller/welcome_dummy.php */
```

Above the example we call nested layers and theirs views.

<b>The most important thing</b> when we work with nested views we need to <b>pass reference of controller ($this)</b> to nested function. Otherwise we couldn't load unlimited nested views.

 
## Json Layers

Json Layers returns to <b>json encoded</b> output. Response is differ it use <b>json_decode()</b> and validate response format of output. This way facilitate to heavy operations like do heavy form validations, creating the new user and so on.

#### Folder Structure

```php
+ app
+ o2
- public
      + welcome
      + views
      - jsons
          - membership
              - controller
                  create_user.php
```

Scaling Application with Json Layers ( Signup Controller )

```php
<?php

/**
 * $app json
 *
 * @var Signup Controller
 */
$app = new Controller(
    function ($c) {
        $c->load('request');
        $c->load('validator');

        if ( ! $this->request->isLayer()) { // Bad guys can't see this page using http request.
            $this->response->show404();
        }
    }
);

$app->func(
    'index',
    function () use ($c) {

        // Start Heavy Operations

        $this->validator->setRules('username', 'Username', 'required|trim');
        $this->validator->setRules('email', 'Email', 'required|email');
        $this->validator->setRules('');
        $this->validator->setRules('');  // ...

        $errors = array();
        if ($this->validator->isValid()) {

            $c->load('service/provider/database as db');  // load database
            
            try {
                $this->db->transaction();
                $this->db->insert(
                  'users', 
                  array(
                    'username' => $this->validator->getValue('username'),
                    'email'    => $this->validator->getValue('email'),
                  )
                );
                $this->db->commit();
                $r = array(
                  'success' => 1     // status "1" or "0"
                  'message' => '',  // optional success message
                );

            } catch (Exception $e) {
                $this->db->rollBack();
                $r = array(
                  'success' => 0,         // status "1" or "0"
                  'message' => 'FORM_MESSAGE:TRANSACTION_ERROR',  // optional transactional message
                  'errors'  => $this->validator->getErrors(),    // optional operation errors
                  'results' => array(),    // optional query results
                  'e' => $e->getMessage(), // optional exception message
                );
            }
            echo json_encode($r); // json encoded string
            return;
        }

        $r = array(
          'success' => 0     // status "1" or "0"
          'message' => 'FORM_MESSAGE:VALIDATION_ERROR',  // optional validation error message
          'errors'  => $this->validator->getErrors(),    // optional validation field errors
          'results' => array(),    // optional query results
          'e' => $e->getMessage(), // optional exception message
        );
        echo json_encode($r); // json encoded string
    }
);


/* End of file header.php */
/* Location: .public/jsons/membership/controller/create_user.php */
```

### Json Layer Response Format

A Json response format must be an <b>array</b> and contain at least one of the following keys.

```php
<?php
$r = array(
    'success' => integer     // optional status
    'message' => string,     // optional error message
    'errors'  => array(),    // optional operation errors
    'results' => array(),    // optional query results
    'e' => $e->getMessage(), // optional exception message
)
echo json_encode($r); // required json encoded string
```


### Layer Class Function Reference

------

#### $this->layer->post(string $uri, $data = array | int, expiration = 0);  

Creates $_POST request request to "public" folder.

#### $this->layer->get(string $uri, $data = array | int, expiration = 0);

Creates $_GET request to "public" folder.

#### $this->layer->method(string 'jsons/$uri', $data = array | int, expiration = 0);

Creates $_GET or $_POST request to <b>"public/jsons"</b> folder.

#### $this->layer->id();

Returns to current layer Id using your json encoded hash of your uri and method parameters.


## Layer/Flush Class

Layer flush class allows to remove cached layers from your cache storage.

### Initializing the Flush Class

------

```php
<?php
$c->load('layer/flush');
$this->layerFlush->method();
```
Once loaded, the Layer object will be available using: <dfn>$this->layerFlush->method()</dfn>


### Layer/Flush Class Function Reference

------

#### $this->layerFlush->uri(string $uri, $data = array);

Deletes cache from memory using uri and parameters.

#### $this->layerFlush->id(integer $layerId);

Deletes cache from memory by layer id.