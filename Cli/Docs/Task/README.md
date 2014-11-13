
## Command Line Interface ( Cli )

------

Php <b>task</b> file which is located in your project root that helps you to run your Cli tasks.

```php
+ app
+ assets
+ data
+ obullo
+ private
+ public
  components.php
  .
  .
  task
```

Open your shell Cli and type

```php
php task help
```

Each task command resolves a task <b>controller</b>.

```php
- app
- config
- tasks
  - controller
    help.php
```

### Following Logs

Open your shell Cli and type

```php
php task log
```

Above the command open your log Cli and follow application logs by reading app.log file.

<b>Log</b> segment is a controller that is located in your <b>app/tasks</b> folder.

```php
- app
- config
- tasks
  - controller
    log.php
```

### Clear log files

Open your shell Cli and type

```php
php task clear
```

Above the command deletes log files from <b>app/data/logs</b> folder.

<b>Clear</b> segment is a controller that is located in your <b>app/tasks</b> folder.

```php
- app
- config
- tasks
  - controller
    clear.php
    log.php
```

## Task class

Task class helps you use CLI operations ( running shell scripts etc..) also it helps to run some basic tasks using php schell_exec() function.

All task controllers located in your <b>app/tasks</b> folder. $this->cliTask->run() method resolve your controllers run task in the background.

**Note:** This class useful for simple operations forexample setting configuration files to memory if they not exists in the cache. To run heavy operations in the background use <b>Queue</b> package e.g. sending emails, logging and any async jobs.

### Initializing the Class

------

```php
$c->load('cliTask');
$this->cliTask->run('controller');
```

#### Running Tasks

The task uri works like framework uri it calls the <kbd>controller/index/arguments</kbd>

```php
echo $this->cliTask->run('help', true);
```

### Function Reference

------

#### $this->cliTask->run('controller/arg1/arg2 ...', $debug = false);

Using $this->cliTask->run() function run your tasks as a using php shell_exec(); command in the background.


#### Continious Tasks

Using below the command your task will be done in the background without wait the http server response.

```php
$this->cliTask->run('controller');
```

#### Follow Log Data ( Cli Debug )

```php
root@localhost:/var/www/project$ php task log
```

```php
Following log data ...

DEBUG - 2013-09-13 06:39:44 --> Application Controller Class Initialized 
DEBUG - 2013-09-13 06:39:44 --> Html Helper Initialized 
DEBUG - 2013-09-13 06:39:44 --> Url Helper Initialized 
DEBUG - 2013-09-13 06:39:44 --> Application Autorun Initialized 
DEBUG - 2013-09-13 06:39:44 --> View Class Initialized 
DEBUG - 2013-09-13 06:39:44 --> Final output sent to browser 
BENCH - 2013-09-13 06:39:44 --> Memory Usage: 700,752 bytes 
```

#### Clear

When you move your project to another server you need to clear log data. Go to your terminal and type your project path then run the clear.

```php
root@localhost:/var/www/project$ php task clear 
```

#### Update

This command upgrade your Obullo if new version available.

```php
root@localhost:/var/www/project$ php task update
```

### Troubleshooting

------

Go to your framework root folder.

```php
$cd /var/www/project/
```

Command line request goes to framework <b>task</b> file which is located in your root.


```php
$php task help
```

Above the command calls the <kbd>app/task/help.php</kbd> class from <b>tasks</b> folder.

```php
        ______  _            _  _
       |  __  || |__  _   _ | || | ____
       | |  | ||  _ || | | || || ||  _ |
       | |__| || |_||| |_| || || || |_||
       |______||____||_____||_||_||____|

        Welcome to Task Manager (c) 2014
Please run [$php task help] You are in [ app / tasks ] folder.

Usage:
php task [command] [arguments]

Available commands:
log        : Follows the application log file.
clear      : Clear application log data. It is currently located in data folder.
help       : See list all of available commands.
```

If you see this screen your command successfully run <b>otherwise</b> check your <b>php path</b> running by this command

```php
$which php // command output /usr/bin/php 
```

If your current php path is not <b>/usr/bin/php</b> open the <b>constants</b> file and define your php path. 

```php
define('PHP_PATH', 'your_php_path_that_you_learned_by_which_command'); 
```