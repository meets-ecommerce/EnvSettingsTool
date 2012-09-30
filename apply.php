<?php

if (version_compare(PHP_VERSION, '5.3.0') <= 0) {
	throw new Exception('EnvSettingsTool needs at least PHP 5.3');
}

define('EST_ROOTDIR', dirname(__FILE__));

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
	$fileName .= str_replace('_', DIRECTORY_SEPARATOR, $className) . '.php';

	if (!file_exists($fileName)) {
		throw new Exception("File '$fileName' not found.");
	}
	require_once($fileName);
	if (!class_exists($className) && !interface_exists($className)) {
		throw new Exception("File '$fileName' does not contain class/interface '$className'");
	}
	return true;
});

try {

	if (empty($_SERVER['argv'][1]) || empty($_SERVER['argv'][2])) {
		throw new Exception('Please specify the environment and the path to the settings file.');
	}

	$env = $_SERVER['argv'][1];
	$settingsFile = $_SERVER['argv'][2];

	$processor = new Est_Processor($env, $settingsFile);
	$processor->apply();
	$processor->printResults();
} catch (Exception $e) {
	echo "\nERROR: {$e->getMessage()}\n\n";
	exit(1);
}