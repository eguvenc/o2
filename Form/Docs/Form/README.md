
## Form Class

The Form Class operate your validator class outputs, custom messages, form notices and errors.

```php
<?php
$this->c->load('form')
$this->form->method();
```

### Initializing the Class

-------

First you need to define <kbd>Form</kbd> class as services. Update your service.php

```php
<?php
/*
|--------------------------------------------------------------------------
| Form
|--------------------------------------------------------------------------
*/
$c['form'] = function () use ($c) {
    return new Obullo\Form\Form($c, $c->load('config')->load('form'));
};
```
Form configuration

```php
<?php

/*
| -------------------------------------------------------------------
| Form
| -------------------------------------------------------------------
| This file contains your arrays of form messages configuration. It is used by the
| Form Class to help set form notice templates. The array keys are used to identify notices 
| that is defined in your constants file.
|
*/
return  array(
    NOTICE_MESSAGE => '<div class="{class}">{icon}{message}</div>',
    NOTICE_ERROR   => array('class' => 'alert alert-danger', 'icon' => '<span class="glyphicon glyphicon-remove-sign"></span>'),
    NOTICE_SUCCESS => array('class' => 'alert alert-success', 'icon' => '<span class="glyphicon glyphicon-ok-sign"></span> '),
    NOTICE_WARNING => array('class' => 'alert alert-warning', 'icon' => '<span class="glyphicon glyphicon-exclamation-sign"></span>'),
    NOTICE_INFO    => array('class' => 'alert alert-info', 'icon' => '<span class="glyphicon glyphicon-info-sign"></span> '),
);

/* End of file form.php */
/* Location: .app/config/form.php */
```

#### $this->form->setMessage($message = '', $status = 0);

Sets form head message. This function uses bootstrap css error class names as default.

```php
<?php
$this->form->setMessage('Example of a success message.', NOTICE_SUCCESS);
$this->form->setMessage('Example of an error message.', NOTICE_ERROR);
```

* The second parameter <b>NOTICE_SUCCESS</b> is defined in your 'constants' file, the default value is 1
* The second parameter <b>NOTICE_ERROR</b> is defined in your 'constants' file, the default value is 0

#### $this->form->setKey($key, $val);

Set key for json_encode(). Sets <b>success, message, errors</b> keys and any custom.

```php
<?php
$this->form->setKey('success', NOTICE_ERROR);				 // sets success key
$this->form->setKey('redirect', 'http://localhost/welcome'); // sets custom key
```

#### $this->form->setErrors(mixed $error);

Sending validator object errors to form object.

```php
<?php
$this->form->setErrors($this->validator);
```
or

```php
<?php
$this->form->setErrors($this->validator->getErrors());
```

Set an error array to form object.

```php
<?php
$errors = array(
	'field'  => 'error message',
	'field2' => 'error message2'
);
$this->form->setErrors($errors));
```

#### $this->form->message($notice = '', $status = 0);

Gets notification message for valid post.

```php
<?php
$this->form->setMessage('Example of a success message.', NOTICE_SUCCESS);
echo $this->form->getMessage();  // Gives: Example of a native POST success message.
```

Gets notification from session flash data with error templates.

```php
<?php
$e = $this->db->transaction(function () {
	$this->db->insert('users', array('username' => 'test', 'date' => time()));
});

if ($e === true) {
    $this->flash->success('User successfully updated.');
} else {
    $this->flash->error($e->getMessage());
}

echo $this->flash->output();  // Get string output with template
```

**Note :** $this->form->message() function returns to <b>boostrap</b> css template as default this feature is configurable from your <b>components.php</b>


#### $this->form->outputArray();

Get all outputs of the form as array.


### Function Reference

-----

#### $this->form->setMessage(string $message = '', integer $status = 0);

Sets form head message. This function uses bootstrap css error class names as default.

#### $this->form->setKey(string $key, mixed $val);

Set key for json_encode(). Sets <b>success, message, errors</b> keys and any custom.

#### $this->form->setErrors(mixed $errors);

Set validator errors to form object.

#### $this->form->success(integer $status = 1);

Sets form message status default is "1". Status "0" means we have an error.

#### $this->form->message(string $message, integer $status = 0);

Gets notification message for valid post.

#### $this->form->outputArray();

Get all outputs of the form as array.