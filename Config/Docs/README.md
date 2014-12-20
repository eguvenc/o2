
## Config Class

------

The Config class provides a means to retrieve configuration preferences. These preferences can come from the default config file <kbd>app/config/env/local/config.php</kbd> or from your own custom config files.

### Initializing the Config Class ( Array Access )

------

```php
<?php
$this->c->load('config');
$this->config->method();    // method access
```

**Note:** Controller içerisinde config sınıfı otomatik olarak yüklü gelir, controller işlemlerinde $this->config->method() çağrılarak kısayoldan bu kütüphaneye erişilebilir.

### Creating a Env Config File

------

By default, Framework has one primary config file, located at <kbd>app/config/env/local/config.php</kbd>.  If you open the file using your text editor you'll see that config items are stored in an array called <var>$config</var>.

Simply create your own file and save it in <dfn>config</dfn> folder.

**Note:** If you do create your own config files use the same format as the primary one, storing your items in an array. 

### Loading Config Files

------

**Note:** Framework automatically loads the primary config file <kbd>app/config/env/local/config.php</kbd>, so you will only need to load a config file if you have created your own.

To load one of your custom config files you will use the following function within the <samp>controller</samp> that needs it:

```php
<?php
$this->config->load('filename');
```

### Creating a Shared Config File

Simply create your own file and save it in <kbd>app/config/shared</kbd> folder.

Then you can load it like below

```php
<?php
$this->config->load('filename');
```

### Loading Constant Files

Simply create your own constant file and save it in <kbd>app/config/shared/constants</kbd> folder.

Then you can load it like below

```php
<?php
$this->config->load('constants/filename');
```

### Accessing Variables

------

To retrieve an item from your config file, use the following function:

```php
<?php
echo $this->config['variable'];
echo $this->config['database']['db']['hostname'];  // gives  "localhost"
```

Where <var>itemname</var> is the <dfn>$config<dfn> array index you want to retrieve. For example, to fetch your language choice you'll do this:

### File Relationships 

If you want, you can give relationship to config file using <b>config('filename.php')</b> function. This will add your <b>file</b> contents to main configuration array.

```php
<?php
/*
|--------------------------------------------------------------------------
| Database
|--------------------------------------------------------------------------
*/
'database' => config('database.php'),

/* End of file config.php */
/* Location: .app/env/local/config.php */
```

### Environment Configuration ( environments.php )

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

When the application run <b>$app->detectEnvironment();</b> method detect your current environment using your configuration array then it assign environment to <b>ENV</b> constant.

```php
<?php
/*
|--------------------------------------------------------------------------
| Detect current environment
|--------------------------------------------------------------------------
*/
define('ENV', $c['app']->detectEnvironment());

// END Core.php File
/* End of file Core.php

/* Location: .Obullo/Obullo/Core.php */
```



### Environment folders

If you want to create new environment first create a new folder under the config directory then copy your config files to there.

```php
- app
- config
    - env
        - local
            config.php
            config.xml
            database.php
        - production
            config.php
            config.xml
            database.php
            .
            .
        - test
```

## Xml Config File

Xml config file keeps configuration data of your application with different environments. Also it helps to keep readable and writeable items. It is located in your <kbd>app/config</kbd> folder.

```xml

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
    <env file=".env.local.php">
        <service>
            <logger class="Log/Env/Local" cli="Log/Env/Cli" />
        </service>
        <provider></provider>
    </env>
</root>
<!--
END xml config File
End of file config.xml

Location: .app/config/env/local/config.xml
-->
```

Config xml file load <b>.env.local.php</b> variables from your project root.

```php
+ app
+ assets
+ o2
.
.
.env.local.php

```

### Contents of the .env.local.php

```php
<?php

return array(

    'MYSQL_USERNAME' => 'root',
    'MYSQL_PASSWORD' => '123456',

    'MONGO_USERNAME' => 'root',
    'MONGO_PASSWORD' => '123456',

    'REDIS_AUTH' => 'aZX0bjL',
    'MANDRILL_API_KEY' => 'BIK8O7xt1Kp7aZyyQ55uOQ',

    'MANDRILL_USERNAME' => 'obulloframework@gmail.com',
    'AMQP_USERNAME' => 'root',
    'AMQP_PASSWORD' => '123456',
);

/* End of file .env.local.php */
/* Location: .env.local.php */
```

If you use test environment you need create your <b>.env.test.php</b> file and update config.xml as <kbd>env file=".env.test.php"</kbd>

Above the example load config file from local environment. When config class initalized first time it loads your environnents variables from your <b>.env.$env.php</b>.

After that you can fetch variable values using any of these methods

* <b>$_ENV['key']</b>
* <b>$_SERVER['key']</b>
* <b>getenv('key')</b>

<b>env()</b> is a wrapper function that helps you to getting environment variables safely.

Below the database config example we fetch environment variables from <b>.env.local.php</b> file using <b>env();</b> function.

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
        'host' => 'localhost',
        'username' => env('DATABASE_USERNAME'),
        'password' => env('DATABASE_PASSWORD'),
        'database' => 'test',
        'port'     => '',
        'charset'  => 'utf8',
        'autoinit' => array('charset' => true, 'bufferedQuery' => true),
        'dsn'      => '',
        'pdo'      => array(
            'options'  => array()
        ),
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


### Initializing the Xml Data ( Object Access )

------

Xml variable returns to <b>SimpleXmlElement object</b>.

#### Reading Xml Variables

```php
<?php
echo $this->config->xml()->route->site->attributes()->label; // gives "Web Server"
```

#### Saving Xml Variables

```php
<?php
$this->config->xml()->route->site->attributes()->label = 'Test Server';
$this->config->xml()->route->site->attributes()->maintenance = 'down';
$this->config->save();
```

Now your xml config file updated as following example.

```xml
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


### Environment Functions

------

This functions helps to you getting environment file and variables safely.

#### env(string $key, $requiredValue = true);

Returns to env variables that is defined in .env.$environment.php file. If second parameter <b>true</b> people know any explicit <b>required variables</b> that your app will not work without. The function will not display an error message if <b>$requiredValue = false</b>.

Eğer ikici parametre string türünde varsayılan bir değer olarak girilirse fonksiyon bu sefer bu varsayılan değere dönecektir.

#### config(string $filename);

Returns to environment based file configuration array which are located in <b>app/config/erv/$environment/filename.php</b>