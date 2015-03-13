
## FailedJob Class

Queues allow you to defer the processing of a time consuming task, such as sending an e-mail, until a later time, thus drastically speeding up the web requests to your application.

**Tip:** You can extend below these classes and build your own.

### Initializing the Class

------

When the job is fail this class automatically initialize by Queue Worker class.


### Configuration Of Failed Jobs

------

Open your application config file and set your Failed Jobs Storage. Default is <b>Obullo\Queue\Failed\Storage\Database</b> class.

```php
<?php
/*
|--------------------------------------------------------------------------
| Queue
|--------------------------------------------------------------------------
*/
'queue' => array(

),
```

### SQL File

FailedJob database sql file is located in <b>Obullo/Queue/Failed/Database.sql</b> path.

```php
CREATE TABLE IF NOT EXISTS `failures` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `job_id` int(11) NOT NULL,
  `job_name` varchar(40) NOT NULL,
  `job_body` text NOT NULL,
  `job_attempts` int(11) NOT NULL DEFAULT '0',
  `error_level` tinyint(3) NOT NULL,
  `error_message` varchar(255) NOT NULL,
  `error_file` varchar(255) NOT NULL,
  `error_line` tinyint(4) NOT NULL,
  `error_trace` text NOT NULL,
  `error_xdebug` text NOT NULL,
  `error_priority` tinyint(4) NOT NULL,
  `failure_repeat` int(11) NOT NULL DEFAULT '0',
  `failure_first_date` int(11) NOT NULL COMMENT 'unix timestamp',
  `failure_last_date` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Failed Jobs' AUTO_INCREMENT=1 ;
```

### Function Reference

------

#### $this->class->method();

Comments


## Emergency Handler Class

Emergency handler send last failed job details to server admin using email library. You can extend this class and build your own.