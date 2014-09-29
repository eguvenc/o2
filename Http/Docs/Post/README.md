
## Post Class

The Post Class serves two purposes:

<ol>
    <li>It pre-processes global input data for security.</li>
    <li>It provides some helper functions for fetching input data and pre-processing it.</li>
</ol>

### Initializing the Class ( Array Access )

------

```php
<?php
$c->load('post');
$this->post['variable'];
```
Once loaded, the Post object will be available using: <dfn>$this->post['variable']</dfn>

### Using POST Data

------

Post class comes with input helper functions that let you fetch $_POST items. The main advantage of using the provided functions rather than fetching an item directly ($_POST['something']) is that the functions will check to see if the item is set and return false (boolean) if not. 

This lets you conveniently use data without having to test whether an item exists first. In other words, normally you might do something like this:

```php
<?php
if ( ! isset($_POST['variable'])) {
    $variable = false;
} else {
    $variable = $_POST['variable'];
}
```

With "Post" class built in functions you can simply do this:

```php
<?php
if ($this->post['variable']) {
	echo $this->post['variable'];
}
```

### Getting All POST Data

Get sanitized all post data

```php
<?php
print_r($this->post[true]);  // gives all post data which keys are sanitized
```

Get all pure data

```php
<?php
print_r($this->post[false]);  // gives pure post data
```

Print and See results:

```php
<?php
var_dump($this->post['variable']);

echo $this->post['variable'];
```

### Some popular array access examples:

* $this->post['variable']  ( $_POST )
* $this->request['variable']  ( $_REQUEST )
* $this->config['variable']  ( Retrieves Config class items )
* $this->translator['variable']  ( Retrieves Translator file items )