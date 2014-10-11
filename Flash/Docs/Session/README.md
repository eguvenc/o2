
## Flash Class

------

Framework supports "flashData", or session data that will only be available for the next server request, and are then automatically cleared. These can be very useful, and are typically used for informational or status messages.

**Note:** Flash variables are prefaced with <b>"flash_"</b> so avoid this prefix in your own session names.

```php
<?php
$c->load('flash/session as flash');
$this->flash->method();
```

#### Adding Flash Notice

```php
<?php
$this->flash->success('Form saved successfully.');
$this->flash->error('Error.');
$this->flash->warning('Something went wrong.');
$this->flash->info('Email has been sent to your mail address.');

$this->flash->output();  // Gives string output with error templates.
```

Using in the application

```php
<?php
$e = $this->db->transaction(
    function () use ($data) {
        $this->db->where('username', $this->post['user_id']);
        $this->db->update('users', $data);
    }
);
$this->flash->success('User successfully updated');

if (is_object($e)) {
    $this->flash->error($e->getMessage());
}
```

#### Keep FlashData

If you find that you need to preserve a flashdata variable through an additional request, you can do so using the $this->flash->keep() function.

```php
<?php
$this->flash->keep('item');
```

```php
<?php
$this->flash->keep('notice:success');
```

#### Adding Flash Data

```php
<?php
$this->flash->set('item', 'value');
```
You can also pass an array to $this->flash->set(), in the same manner as $this->flash->set().

To read a flashdata variable:

```php
<?php
$this->flash->get('item', $prefix = '' , $suffix = '');

```
Getting data

If flash **data is empty** $this->flash->get() function will return an empty string otherwise it will retun the flash data value with $prefix and $suffix codes.

```php
<?php
echo $this->flash->get('item', '<p class="example">', '</p>');
```

### Function Reference

------

#### $this->flash->success(string $message);

Sets success type flash notice.

#### $this->flash->error(string $message);

Sets error type flash notice.

#### $this->flash->warning(string $message);

Sets warning type flash notice.

#### $this->flash->info(string $message);

Sets info type flash notice.

#### $this->flash->output();

Retrieves flash messages as "string" and identifies flashdata as 'old' for removal.

#### $this->flash->outputArray();

Retrieves flash messages as "array" and identifies flashdata as 'old' for removal.

#### $this->flash->set(string|array $data = '', $newval = '')

Sets new type flash notice. Flashdata, only available until the next request.

#### $this->flash->keep(string $key)

Keeps existing flashdata available to the next request.

#### $this->flash->get(string $key)

Identifies flashdata as 'old' for removal when flashdataSweep() method runs.