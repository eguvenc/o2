
## Config Class

------

The Config class provides a means to retrieve configuration preferences. These preferences can come from the default config file <kbd>app/config/env/local/config.php</kbd> or from your own custom config files.

### Initializing the Config Class ( Array Access )

------

```php
<?php
$this->c['config']->method();    // method access
```

**Note:** Controller içerisinde config sınıfı otomatik olarak yüklü gelir, controller işlemlerinde $this->config->method() çağrılarak kısayoldan bu kütüphaneye erişilebilir.

### Creating a Env Based Config

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


### environments.php

Framework ortam sabitini atamak için bilgisayarınıza ait hostname değerine ihtiyaç duymaktadır. <b>Local</b> ortamı için hostname ler her biri yazılımcı için <b>Production</b> ortamı için de bu değerler her bir sunucu için tanımlanmalıdır.

Using your environments array <b>$c->detectEnvironment();</b> method detect your current environment to assign <b>ENV</b> constant. Before this you need to define your hostnames in <kbd>app/config/environments.php</kbd> file.

```php
<?php

return array(
    'local' => array (
        'server' => array(
            'hostname' => array(
                'localhost.john',
                'aly-desktop',
                'zero',
                'MS-7693-computer',
            ),
            'ip' => array(),
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
    'production' => array (
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

/* Location: .app/config/environments.php */
```

Auto detecting of environment.

```php
<?php
/*
|--------------------------------------------------------------------------
| Detect current environment
|--------------------------------------------------------------------------
*/
define('ENV', $c->detectEnvironment());

// END Core.php File
/* End of file Core.php

/* Location: .Obullo/Obullo/Core.php */
```

After the definition application can use <b>ENV</b> constant.


### Creating Environment Folders

If you want to create new environment first create a new folder under the config directory then copy your config files to there.

```php
- app
- config
    - env
        - local
            config.php
            config.env
            database.php
        - production
            config.php
            config.env
            database.php
            .
            .
        - test
```

**Note:** If you use env() function in database.php you don't need to move to production folder.

## Config.env File

Config.env file keeps configuration data of your application with different environments. Also it helps to keep writable items. It is located in your <kbd>app/config</kbd> folder.

```php
<?php

return array(

    'environment' => array(
        'file' => 'env.local.php',
        'service' => array(
            'logger' => array(
                'cli' => 'Service/Log/Env/Cli',
                'http' => 'Service/Log/Env/Local',
            ),
        ),
    ),
    'web' => array(
        'app' => array(
            'all' => array(
                'maintenance' => 'up',
                'label' => 'All Application',
            ),
            'site' => array(
                'maintenance' => 'down',
                'label' => 'Web Server',
                'regex' => '^framework$',
            ),
        ),
    ),
    'service' => array(
        'app' => array(
            'all' => array(
                'maintenance' => 'up',
                'label' => 'All Services',
            ),
            'queue' => array(
                'maintenance' => 'up',
                'label' => 'Queue Service',
            ),
        ),
    ),
);

/* End of file config.env */
/* Location: .app/env/local/config.env */
```

Config.env file load <b>.env.local.php</b> variables from your project root.

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

    'AMQP_HOST' => '',
    'AMQP_USERNAME' => 'root',
    'AMQP_PASSWORD' => '123456',
);

/* End of file .env.local.php */
/* Location: .env.local.php */
```

If you use test environment you need create your <b>.env.test.php</b> file and update config.env environment['file'] value as <kbd>.env.test.php</kbd>

Above the example load config file from local environment. When config class initialized first time it loads your environnents variables from your <b>.env.$env.php</b>.

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
        'username' => env('DATABASE_USERNAME', 'root'),
        'password' => env('DATABASE_PASSWORD', '', false),
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

### Reading Config Variables

Below the example will fetch your host item from your current environment.

```php
<?php
echo $this->config['database']['db']['host'];  // gives test.example.com 
```

**Note:** <b>ENV</b> constant defined in your <b>constants</b> php file.


### Reading Config.env Variables

------

```php
<?php
echo $this->config->env['web']['app']['site']['label']; // gives "Web Server"
```

#### Saving Config.env Variables

```php
<?php
$this->config->env['web']['app']['site']['label'] = 'Test Server';
$this->config->env['web']['app']['site']['maintenance'] = 'down';
$this->config->write();
```

Now your config.env file updated as below.

```php
<?php

return array(

    'environment' => array(
        'file' => 'env.local.php',
        'service' => array(
            'logger' => array(
                'cli' => 'Service/Log/Env/Cli',
                'http' => 'Service/Log/Env/Local',
            ),
        ),
    ),
    'web' => array(
        'app' => array(
            'all' => array(
                'maintenance' => 'up',
                'label' => 'All Application',
            ),
            'site' => array(
                'maintenance' => 'down',
                'label' => 'Test Server',
                'regex' => '^framework$',
            ),
        ),
    ),

/* End of file config.env */
/* Location: .app/env/local/config.env */
```

### Function Reference

------

#### $this->config->load(string $filename, boolean $env = false);

Load config file from <b>app/config/</b> folder if second parameter is <b>true</b> loading path will change as <b>app/config/$env</b>.

#### $this->config['variable']['item'];

Gets config variable from array config.

#### $this->config->array['variable']['item'] = 'value';

Sets config variable to array config.

#### $this->config->env['group']['item'];

Gets config variable from <b>app/config/$env/config.env</b> file.

#### $this->config->env['group']['item'] = 'value';

Sets env variable to config.env.

#### $this->config->write();

Save current env array to config.env configuration file.


### Environment Functions

------

This functions helps to you getting environment file and variables safely.

#### env(string $key, string $default = '', bool $required = false)

Returns to env variables that is defined in .env.$environment.php file. If you provide second parameter it returns to default value even if variable is empty.

If third parameter <b>true</b> people know any explicit <b>required variables</b> that your app will not work without. The function will not display an error message if <b>$required = false</b>.

#### config(string $filename);

Returns to environment based file configuration array which are located in <b>app/config/erv/$environment/filename.php</b>