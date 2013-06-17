<?php

/**
* Simple autoloading
*
* @param string $className
* @return bool
* @throws Exception
* @author Fabrizio Branca
* @since 2012-09-19
*/
spl_autoload_register(function ($className) {

// don't do autoloading for external classes
if (strpos($className, 'Est_') !== 0) {
return false;
}

$fileName = dirname(__FILE__) . DIRECTORY_SEPARATOR;
$fileName .= str_replace('_', DIRECTORY_SEPARATOR, substr($className,4)) . '.php';

if (!file_exists($fileName)) {
throw new Exception("File '$fileName' not found.");
}
require_once($fileName);
if (!class_exists($className) && !interface_exists($className)) {
throw new Exception("File '$fileName' does not contain class/interface '$className'");
}
return true;
});