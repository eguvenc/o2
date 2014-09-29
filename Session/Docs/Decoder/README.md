
## Decoder Class

Decodes and encodes php raw session data.

### Initializing the Class

------

```php
<?php
$c->load('session/decoder as decoder');
$this->decoder->method();
```

To decode your metada data:

```php
<?php
$output = $this->decoder->decodeMeta('test|s:6:"asdasd";_o2_meta|s:153:"{"sid":"6l1conq3khk6grq6l6n7ouv361","ip":"127.0.0.1","ua":"Mozilla/5.0 (X11; Ubuntu; Linux x86_64; rv:26.0) Gecko/20100101 Firefox/26.0","la":1404817064}";');

print_r($output); // gives Array ( [example] => Hello World ! [user_id] => 1512 ) 

$output = $this->decoder->encodeMeta($output); // gives {"sid":"6l1conq3khk6grq6l6n7ouv361","ip":"127.0.0.1","ua":"Mozilla/5.0 (X11; Ubuntu; Linux x86_64; rv:26.0) Gecko/20100101 Firefox/26.0","la":1404817064}
```

To save your custom meta to session:

```php
<?php
$data = array ('sid' => '6l1conq3khk6grq6l6n7ouv361', 'ip' => '127.0.0.1' 'ua' => 'Mozilla/5.0 (X11; Ubuntu; Linux x86_64; rv:26.0) Gecko/20100101 Firefox/26.0' 'la' => 1404817064 ) 
$output = $this->decoder->saveMeta(array $data);
```

To decode your raw $_SESSION:

```php
<?php
$output = $this->decoder->decode('s:48:"example|s:13:"Hello World !";user_id|s:4:"1512";";');

print_r($output); // gives Array ( [example] => Hello World ! [user_id] => 1512 ) 
```

To encode your array as raw data:

```php
<?php

echo $this->decoder->encode(array('example' => 'Hello World !', 'user_id' => 1512)); 

// gives example|s:13:"Hello World !";user_id|i:1512; 
```

### Function Reference

------

#### $this->decoder->decodeMeta(string $rawSession)

Decodes php session meta data to array.

#### $this->decoder->encodeMeta(array $data)

Encodes php session meta data array to string.

#### $this->decoder->saveMeta(array $data)

Writes meta data to $_SESSION.

#### $this->decoder->decode(string $rawSession)

Decodes php session data to array.

#### $this->decoder->encode(array $data)

Encodes array data to php session.