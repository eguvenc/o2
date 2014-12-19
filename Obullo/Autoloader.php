<?php

/**
 * Obullo
 * 
 * @category  Autoloader
 * @package   Obullo
 * @author    Obullo Framework <obulloframework@gmail.com>
 * @copyright 2009-2014 Obullo
 * @license   http://opensource.org/licenses/MIT
 * @link      http://www.php-fig.org/psr/psr-0/
 */

/**
 * PSR-0 Autoloader
 * 
 * @param string $realname classname 
 *
 * @see http://www.php-fig.org/psr/psr-0/
 * 
 * @return void
 */
function Obullo_autoloader($realname)
{
    if (class_exists($realname, false)) {  // Don't use autoloader
        return;
    }
    $className = ltrim($realname, '\\');
    $fileName  = '';
    $namespace = '';
    if ($lastNsPos = strrpos($className, '\\')) {
        $namespace = substr($className, 0, $lastNsPos);
        $className = substr($className, $lastNsPos + 1);
        $fileName  = str_replace('\\', DIRECTORY_SEPARATOR, $namespace) . DIRECTORY_SEPARATOR;
    }
    $fileName .= str_replace('_', DIRECTORY_SEPARATOR, $className);

    if (strpos($fileName, 'Obullo') === 0) {     // Check is it Obullo Package ?
        include_once OBULLO .substr($fileName, 7). '.php';
        return;
    }
    include_once CLASSES .$fileName. '.php'; // Otherwise load it from user directory
}
spl_autoload_register('Obullo_autoloader', true);


// END Autoloader.php File
/* End of file Autoloader.php

/* Location: .Obullo/Obullo/Autoloader.php */