
## Error Handling

Framework lets you build error reporting into your applications using the functions described below. In addition, it has an error logging class that permits error and debugging messages to be saved as text files.

**Note:** Whe you use "local" environment framework displays all PHP errors. Disabling debug will NOT prevent log files from being written if there are errors.

### Enable / Disable Errors

------

In your <dfn>app/config/$env/config.php</dfn> file you can enable application errors.

```php
/*
|--------------------------------------------------------------------------
| Debug
|--------------------------------------------------------------------------
*/
'debug' => true,            // If debug enabled framework converts all php errors to exceptions.
                            // Should be disabled in "live" mode.

```

**Note:** Working under the "local" environment errors should be "true" in production mode it should be "false".

**Note:** In production mode ( live ) all errors goes to your log handler if logging enabled.


The following functions let you generate errors:


#### $this->response->showError('message' [, int $status_code= 500 ] )

This function will display the error message supplied to it using the following error template:

You can <b>customize</b> this template which is located at <dfn>app/templates/errors/general.php</dfn>

The optional parameter <dfn>$status_code</dfn> determines what HTTP status code should be sent with the error.

```php
$this->response->showError('There is an error occured');
```

This function will display the 404 error message supplied to it using the following error template:

```php
$this->response->show404('page')
```

You can <b>customize</b> this template which is located at <dfn>app/templates/errors/</dfn><kbd>404.php</kbd>

The function expects the string passed to it to be the file path to the page that isn't found. Note that framework automatically shows 404 messages if controllers are not found.

#### $this->logger->level($message);

This function lets you write messages to your log files. You must supply one of three "levels" in the first parameter, indicating what type of message it is (debug, error, info), with the message itself in the second parameter. Example:

```php
if ( ! $variable) {
    $this->logger->error('Some variable did not contain a value.');
}

$this->logger->info('The purpose of some variable is to provide some value.');
```
**Note:** Look at Log package for more details about logging.

### Exceptions

------

We catch all exceptions with php <dfn>set_exception_handler()</dfn> function. You can control the exceptions from components.php.


```php
/*
|--------------------------------------------------------------------------
| Exception
|--------------------------------------------------------------------------
*/
$c['exception'] = function () {
    return new Obullo\Error\Exception;
};
```

**Note:** You can manually catch exceptions in try {} catch {} blocks.

```php
try
{
    throw new Exception('blabla');
    
} catch(Exception $e)
{
    echo $e->getMessage();  // output blabla 
}
```

### Debugging Logs to Your Console

When your application works you may want see all log files from console. To activate you need to run below the command.

```php
$cd /var/www/myproject
```

```php
$php task log
```
You can set filter for log level

```php
$php task log level info
```

```php
$php task log level debug
```

**Note:** Console debugging highly recommended if you want to see <b>sql queries, application errors, cookies, sessions</b> and so on.

### Debugging Logs to Html

------

Framework lets you build user friendly html debugging into your applications using the configurations described below.

```php
/*
|--------------------------------------------------------------------------
| Log
|--------------------------------------------------------------------------
| @see Syslog Protocol http://tools.ietf.org/html/rfc5424
| @link http://www.php.net/manual/en/function.syslog.php
*/
'log' =>   array(
    'enabled'   => true,       // On / Off logging.
    'debug'     => true,       // On / Off debug html output. When it is enabled all handlers will be disabled.
```

**Note:** Html debugging highly recommended if you want to see <b>Lvc Requests visually</b>.