
## Cli Parser Class

------

Cli class helps you <b>parse</b> command line parameters and arguments.

Example commmand line parameters

```php
php task queue list
```

Example commmand line argument

```php
php task queue list --delete
```

Example commmand line parameters and arguments

```php
php task queue listen --channel=Logger --route=logger --delay=0 --memory=128
```

**Note:** Framework only accepts dashes <b>(--)</b> to parse arguments.

### Initializing the Class

------

```php
$c->load('cliParser');
$this->cliParser->method();
```

### Example Command

Below the command run <b>app/tasks/queue</b> controller.

```php
php task queue list --channel=Logger --route=logger --delay=0 --memory=128
```

```php
<?php
$app = new Controller;
$app->func(
    'index',
    function () use ($c) {
        $c->load('cliParser');
        $this->cliParser->parse(func_get_args());  // Parse parameters
        switch ($this->cliParser->segment(0)) {    // Grab "list" command
        case 'list':
            $this->_list();
            break;
        default:
            $this->_help();
            break;
        }
    }    
);
$app->func(
    '_list',
    function () use ($c) {
        $c->load('cliParser');
        echo $this->cliParser->argument('channel');      //  gives "Logger"
        echo $this->cliParser->argument('route', null);  //  gives "logger"
        echo $this->cliParser->argument('delay', null);  //  gives "0"
        echo $this->cliParser->argument('delay', null);  //  gives "128"  
    }
);
```

### Function Reference

------

#### $this->cliParser->parse(func_get_args())

Parse valid function parameters.

#### $this->cliParser->segment($number)

Gets valid command line segment.

#### $this->cliParser->segmentArray()

Returns to all segments.

#### $this->cliParser->argument($key)

Gets valid command line argument.

#### $this->cliParser->argumentArray()

Returns to all arguments.