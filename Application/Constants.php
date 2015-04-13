<?php
/*
|---------------------------------------------------------------
| OBULLO APPLICATION CONSTANTS
|---------------------------------------------------------------
| ROOT              - The root path of your server
| APP               - The full server path to the "app" folder
| ASSETS            - The full server path to the "assets" folder
| DATA              - The full server path to the "data" folder
| CLASSES           - The full server path to the user "classes" folder
| TEMPLATES         - The full server path to the user "templates" folder
| RESOURCES         - The full server path to the user "resources" folder
| MODULES       	- The full "dynamic" server path to the "modules" folder
| TASK_FILE         - The file name for $php task operations.
| TASK              - The full "static" path of the native cli task folder.
| INDEX_PHP         - The path of your index.php file.
*/
define('APP',  ROOT .'app'. DS);
define('ASSETS',  ROOT .'assets'. DS);
define('RESOURCES',  ROOT .'resources'. DS);
define('DATA',  RESOURCES .'data'. DS);
define('TRANSLATIONS',  RESOURCES .'translations'. DS);
define('CLASSES',  APP .'classes'. DS);
define('TEMPLATES',  APP . 'templates'. DS);
define('MODULES', ROOT .'modules'. DS);
define('TASK_FILE', 'task');
define('TASK', PHP_PATH .' '. APP .'tasks'. DS .'cli'. DS);
define('CLI_PHP', 'cli.php');
define('INDEX_PHP', 'index.php');
/*
|--------------------------------------------------------------------------
| OBULLO
|--------------------------------------------------------------------------
*/
define('OBULLO_CLI', OBULLO .'Application'. DS .'Cli.php');
define('OBULLO_HTTP', OBULLO .'Application'. DS .'Http.php');
define('OBULLO_PROVIDERS', APP .'providers.php');
define('OBULLO_COMPONENTS', APP .'components.php');
define('OBULLO_EVENTS', APP .'events.php');
define('OBULLO_ROUTES', APP .'routes.php');
define('OBULLO_MIDDLEWARES', APP .'middlewares.php');
define('OBULLO_CONTROLLER', OBULLO .'Controller'. DS .'Controller.php');


// END Constants.php File
/* End of file Constants.php

/* Location: .Obullo/Application/Constants.php */