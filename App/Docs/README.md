
## Application Class

------

The app class contains functions that assist in working with global application functions.

### Initializing the Class

-------

```php
<?php
$c['app']->method();
```

### config.php

All of the <b>readable</b> configuration files are stored in the <kbd>app/config/env</kbd> directory. And all of config nodes joined in main config called <b>config.php</b>.

Config class simply manage your configuration files.

```php
<?php
$this->config['locale']['timezone'];  // gives "gmt"
$this->config['locale']['charset'];   // gives "UTF-8"
```

### config.xml

Globals config saved in <b>data/globals/config.xml</b> file and it keeps application global settings. <b>Data/globals</b> folder contains <b>readable</b> and <b>writable</b> config files.

```php
<?php
<?xml version="1.0"?>
<root>
<host>
  <all>
    <name>All Site</name>
    <domain><regex>.*.example.com</regex></domain>
    <maintenance>up</maintenance>
  </all>
  <site>
    <name>Web Site</name>
    <domain><regex>^(www\.)?example\.com$</regex></domain>
    <maintenance>down</maintenance>
  </site>
  <sports>
    <name>Sports</name>
    <domain><regex>sports\d+\.example\.com$</regex></domain>
    <maintenance>up</maintenance>
  </sports>
  <support>
    <name>Support</name>
    <domain><regex>support\.example\.com$</regex></domain>
    <maintenance>up</maintenance>
  </support>
</host>
<service>
  <all>
    <name>All Services</name>
    <maintenance>up</maintenance>
  </all>
  <queue>
    <name>Queue Service</name>
    <maintenance>down</maintenance>
  </queue>
</service>
</root>
<!--
END xml config File
End of file config.xml

Location: .app/config/env/local/config.xml
-->
```

#### Updating config.xml

```php
<?php
$this->config->xml->host->site->name = 'Test Site';
$this->config->xml->host->site->maintenance = 'down';
$this->config->save($this->config->xml->asXML());
```

Following xml file show changes after save operation

```php
<?php
<?xml version="1.0"?>
<root>
<host>
    <all>
        <name>All Site</name>
        <domain><regex>.*.example.com</regex></domain>
        <maintenance>up</maintenance>
    </all>
    <site>
        <name>Test Site</name>
        <domain><regex>^(www\.)?example\.com$</regex></domain>
        <maintenance>down</maintenance>
    </site>
    .....
```

#### Reading from config.xml

```php
<?php
echo $this->config->xml->variable->item;
```

### Give write access to your /env/$env/config.xml file

When you start to configure your application first you must give write acccess to <b>config.xml</b> files. Otherwise application could not write on global settings.

```php
- app
	- config
        - env
            - local
                config.php
                config.xml

```

```
chmod -R 777 /var/www/yourproject/app/config/env/local/config.xml
chmod -R 777 /var/www/yourproject/app/config/env/test/config.xml
chmod -R 777 /var/www/yourproject/app/config/env/prod/config.xml
```

#### Maintenance Mode

To <b>enable</b> maintenance mode you can execute the <b>down</b> task command:

```php
$php task host $name down
```


```php
$php task service $name down
```

To <b>disable</b> maintenance mode you can execute the <b>up</b> command:


```php
$php task host $name up
```

```php
$php task service $name up
```

### environments.php

Software projects are typically deployed in various environments. You likely develop your application locally in a development environment. You then have your live server where your site resides, known as the production environment. Many applications will have a staging environment, essentially a duplicate of your production environment, where apps are deployed and tested before being pushed to production. Larger applications may have other environments in between.

Each of these environments may require different configuration.

#### Default Environment and Configuration

The default environment in Obullo is local; in other words, without making any of the aforementioned changes your app will be considered in ‘local’. Any files within the root of the app/config  folder will be considered the configuration for the production environment; that includes app/config/cache.php , app/config/config.php , app/config/database.php  and others.

#### Setting Environments

We can set different environments and subsequently use different configuration. Open up the <kbd>app/config/env/environments.php</kbd> file in your application. Here you will see the following block of code:


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
                'localhost.localdomain',
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

When the application run $app->detectEnvironment(); method using your configuration array compare the hostnames, if your hostname match with your hostnames array it returns to matched environment name.


### Loading Config Files From Current Environment Folder

```php
<?php
$this->config->load('filename');
```
Config class load your files from <kbd>app/config</kbd> folder but if the file exists in your current environment folder config class will load it from <kbd>app/config/env/$env</kbd> folder.

### Function Reference

------

#### $app->detectEnvironment();

Detect and returns current environment using your <b>app/config/env/environments.php</b> hostname array.