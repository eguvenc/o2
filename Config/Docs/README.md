
## Config Class

------

The Config class provides a means to retrieve configuration preferences. These preferences can come from the default config file <kbd>app/config/env/local/config.php</kbd> or from your own custom config files.

### Initializing the Config Class ( Array Access )

------

```php
<?php
$c->load('config');
$this->config['variable'];
$this->config->method();
```

### Accessing Variables

------

```php
<?php
echo $this->config['database']['db']['hostname'];  // gives  "localhost"
```

### Creating a Config File

------

By default, Framework has one primary config file, located at <kbd>app/config/env/local/config.php</kbd>.  If you open the file using your text editor you'll see that config items are stored in an array called <var>$config</var>.

Simply create your own file and save it in <dfn>config</dfn> folder.

**Note:** If you do create your own config files use the same format as the primary one, storing your items in an array. 

### Loading a Config File

------

**Note:** Framework automatically loads the primary config file <kbd>app/config/env/local/config.php</kbd>, so you will only need to load a config file if you have created your own.

To load one of your custom config files you will use the following function within the <samp>controller</samp> that needs it:

```php
<?php
$this->config->load('filename');
```

### Getting Config Items

------

To retrieve an item from your config file, use the following function:

```php
<?php
$this->config['itemname'];
```

Where <var>itemname</var> is the <dfn>$config<dfn> array index you want to retrieve. For example, to fetch your language choice you'll do this:

### Relationships 

If you want, you can give relationship to main config file using <b>@include.filename.php</b> string.

When you use <b>@include.filename.php</b> in your main config file this will load your <b>file</b> from your <b>env/$env/filename.php</b>

```php
<?php
/*
|--------------------------------------------------------------------------
| Database
|--------------------------------------------------------------------------
*/
'database' => '@include.database.php',

/* End of file config.php */
/* Location: .app/env/local/config.php */
```

Above the example load config file from local environment.


```php
- app
- config
	- env
		- local
			config.php
			database.php
		- prod
		- test
```

Database config example

```php
<?php
/*
|--------------------------------------------------------------------------
| Database
|--------------------------------------------------------------------------
| Configuration file
|
*/
return array(
    'db' => array(
        'host' => 'demo_blog',
        'username' => 'root',
        'password' => '123456',
        'database' => 'demo_blog',
        'port'     => '',
        'charset'  => 'utf8',
        'dsn'      => '',
        'options'  => array()
    ),
    'yourSecondDatabaseName' => array(
        'host' => '',
        'username' => 'root',
        'password' => '123456',
        'database' => '',
        'port'     => '',
        'charset'  => 'utf8',
        'dsn'      => '',
        'options'  => array()
    ),
);

/* End of file database.php */
/* Location: .app/env/local/database.php */
```

Below the example will fetch your host item from your current environment.

```php
<?php
echo $this->config['database']['db']['host'];  // gives test.example.com 
```

**Note:** <b>ENV</b> constant defined in your <b>constants</b> php file.


### Setting a Config Item

------

If you would like to dynamically set a config item or change an existing one, you can do using:

```php
<?php
$this->config->array['item'] = 'value';
$this->config->array['item']['subitem'] = 'value';
```

Where <var>item</var> is the $config array index you want to change, and <var>value</var> is its value.

### Loading Files From Your Environment folder

```php
<?php
$this->config->load('filename', true);
```

## Xml Config File

Xml config file keeps configuration data of your application with different environments. Also it helps to keep readable and writeable items. It is located in your <kbd>app/config</kbd> folder.

```php
<?php
<?xml version="1.0"?>
<root>
    <route>
        <all maintenance="up" label="All Application" regex=".*.example.com"/>
        <site maintenance="up" label="Web Server" regex="^example.com$|^www.example.com$"/>
        <test maintenance="up" label="Sports" regex="sports\d+.example.com"/>
        <support maintenance="up" label="Support" regex="support.example.com"/>
    </route>
    <service>
        <all maintenance="down" label="All Services"/>
        <queue maintenance="up" label="Queue Service"/>
    </service>
    <container>
        <service>
            <logger class="Log/Env/LocalLogger" cli="Log/Env/CliLogger"/>
        </service>
        <provider></provider>
    </container>
</root>
<!--
END xml config File
End of file config.xml

Location: .app/config/env/local/config.xml
-->
```

### Initializing the Xml Data ( Object Access )

------

Xml variable returns to <b>SimpleXmlElement object</b>.

#### Reading Variables

```php
<?php
echo $this->config->xml()->route->site->attributes()->label; // gives "Web Server"
```

#### Saving Variables

```php
<?php
$this->config->xml()->route->site->attributes()->label = 'Test Server';
$this->config->xml()->route->site->attributes()->maintenance = 'down';
$this->config->save();
```

Now your xml config file updated as following example.

```php
<?xml version="1.0"?>
<root>
    <route>
        <all maintenance="up" label="All Application" regex=".*.example.com"/>
        <site maintenance="down" label="Test Server" regex="^example.com$|^www.example.com$"/>
        <test maintenance="up" label="Sports" regex="sports\d+.example.com"/>
        <support maintenance="up" label="Support" regex="support.example.com"/>
    </route>
    ....
<!--
END xml config File
End of file config.xml

Location: .app/config/env/local/config.xml
-->
```

### environments.php

You can configure environments in <kbd>app/config/env/environments.php</kbd> file.


```php
<?php
/*
|--------------------------------------------------------------------------
| Environments
|--------------------------------------------------------------------------
*/
return array(
    'local' => array (
        'server' => array(
            'hostname' => array(
                'localhost.john',
                'aly-desktop',
                'zero',
                'MS-7693-computer',
            ),
            'ip' => array(
                '127.0.0.1',
                '127.0.0.1',
                '127.0.0.1',
                '127.0.0.1',
            ),
        ),
    ),
    'test' => array (
        'server' => array(
            'hostname' => array(
                'localhost.testdomain',
            ),
            'ip' => array(),
        ),
    ),
    'prod' => array (
        'server' => array(
            'hostname' => array(
                'localhost.production',
            ),
            'ip' => array(),
        ),
    ),
);

// END environments.php File
/* End of file environments.php

/* Location: .app/config/env/environments.php */
```

When the application run $app->detectEnvironment(); method detect your current environment using your configuration array.


### Function Reference

------

#### $this->config->load(string $filename, boolean $env = false);

Load config file from <b>app/config/</b> folder if second parameter is <b>true</b> loading path will change as <b>app/config/$env</b>.

#### $this->config['variable']['item'];

Gets config variable from array config.

#### $this->config->array['variable']['item'] = 'value';

Sets config variable to array config.

#### $this->config->xml()->variable->item;

Gets config variable from <b>app/config/config.xml</b> file.

#### $this->config->xml()->variable->item = 'value';

Sets config variable to xml config.

#### $this->config->xml()->variable->attributes()->item = 'value';

Updates attributes value.

#### $this->config->save();

Save valid xml output to xml configuration file.