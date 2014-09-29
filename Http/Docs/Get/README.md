
## Get Class

The Get Class serves two purposes:

<ol>
    <li>It pre-processes global input data for security.</li>
    <li>It provides some helper functions for fetching input data and pre-processing it.</li>
</ol>

### Initializing the Class ( Array Access )

------

```php
$c->load('get');
$this->get['variable'];
```
Once loaded, the Get object will be available using: <dfn>$this->get['variable']</dfn>

### Using GET Data

------

Get class comes with input helper functions that let you fetch $_GET items. The main advantage of using the provided functions rather than fetching an item directly ($_GET['something']) is that the functions will check to see if the item is set and return false (boolean) if not. 

This lets you conveniently use data without having to test whether an item exists first. In other words, normally you might do something like this:

```php
<?php
if ( ! isset($_GET['variable'])) {
    $variable = false;
} else {
    $variable = $_GET['variable'];
}
```

With "Get" class built in functions you can simply do this:

```php
<?php
if ($this->get['variable']) {
	echo $this->get['variable'];
}
```

### Getting All GET Data

Get sanitized all post data

```php
<?php
print_r($this->get[true]);  // gives all post data which keys are sanitized
```

Get all pure data

```php
<?php
print_r($this->get[false]);  // gives pure post data
```

Print and See results:

```php
<?php
var_dump($this->get['variable']);

echo $this->get['variable'];
```

### Some popular array access examples:

* $this->post['variable']  ( $_POST )
* $this->request['variable']  ( $_REQUEST )
* $this->config['variable']  ( Retrieves Config class items )
* $this->translator['variable']  ( Retrieves Translator file items )