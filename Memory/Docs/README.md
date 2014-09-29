
## Memory Class

------

The Memory class control <b>locale machine</b> memory blocks with shared memory functions. Shmop no requries any extension it comes with your default php installation.

<a hre="http://www.php.net/manual/en/book.shmop.php">http://www.php.net/manual/en/book.shmop.php</a>

### Initializing the Class

------

When you load memory class it will be available in your controller

```php
<?php
$c->load('memory');
$this->memory->method();
```

### Testing Results

```php
if ($data = $this->memory->get('test')) {
	print_r($data);
}
```

### Storing raw data

```php
$this->memory->set('test', 'Hello World');
```

### Storing array data

```php
$this->memory->set('test', array('dummy' => 'hello world'));
```

### Expiration

```php
$this->memory->set('test', array('dummy' => 'hello world'), 7200);
```

### Deleting data

```php
$delete = $this->memory->delete('test');

var_dump($delete);  // gives true / false
```

### Function Reference

------

#### $this->memory->exists(string $key)

Checks whether the <b>key</b> has been used before and checks <b>expiration</b> if it is expired, it will be deleted.

#### $this->memory->set(string $key, mixed $value, int $expiration)

Sets data to memory block with expiration time.

#### $this->memory->get(string $key)

Read unserialized cache data from memory block.

#### $this->memory->delete(string $key)

Deletes key from memory block.

#### $this->memory->read(string $key)

Read raw data from memory block.

#### $this->memory->write(string $key, mixed $data)

Sets raw data to memory block.

#### $this->memory->getPermission()

Returns permission ( octal ) used in memory blocks.

#### $this->memory->setPermission(0755)

The permissions that you wish to assign to your memory segment ( octal ).