
## Annotations

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
        <tr>
            <td><b>@event->subscribe("Event\Classname")</b></td>
            <td>Subscribes the event listener.</td>
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
'annotations' => array(
    'enabled' => true,
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