#!/usr/bin/php
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

	if (empty($_SERVER['argv'][1])) {
		throw new Exception('Please specify the environment');
	}

	if (getenv('NO_COLOR')) {
		Est_CliOutput::$active = false;
	}

	$env = $_SERVER['argv'][1];
	$settingsFile = empty($_SERVER['argv'][2]) ? '../settings/settings.csv' : $_SERVER['argv'][2];

	if (empty($_SERVER['argv'][3])) {
		throw new Exception('No handler specified!');
	}
	$handler = $_SERVER['argv'][3];

	$param1 = isset($_SERVER['argv'][4]) ? $_SERVER['argv'][4] : '';
	$param2 = isset($_SERVER['argv'][5]) ? $_SERVER['argv'][5] : '';
	$param3 = isset($_SERVER['argv'][6]) ? $_SERVER['argv'][6] : '';

	$processor = new Est_Processor($env, $settingsFile);
	$handler = $processor->getHandler($handler, $param1, $param2, $param3);

	echo $handler->getValue();

} catch (Exception $e) {
	echo "\nERROR: {$e->getMessage()}\n\n";
	exit(1);
}