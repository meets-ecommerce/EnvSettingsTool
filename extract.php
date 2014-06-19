#!/usr/bin/php
<?php

if (version_compare(PHP_VERSION, '5.3.0') <= 0) {
    throw new Exception('EnvSettingsTool needs at least PHP 5.3');
}

define('EST_ROOTDIR', dirname(__FILE__));


include(dirname(__FILE__).'/Est/Autoloading.php');

try {

    if (empty($_SERVER['argv'][1])) {
        throw new Exception('Please specify the environment');
    }

    if (getenv('NO_COLOR')) {
        Est_CliOutput::$active = false;
    }

    $handler = new $_SERVER['argv'][1](); /* @var $handler Est_Handler_Abstract */

    $handler->setParam1($_SERVER['argv'][2]);
    $handler->setParam2($_SERVER['argv'][3]);
    $handler->setConfigKey($_SERVER['argv'][4]);

    try {
        $handler->extractSettings();
    } catch (Exception $e) {
        echo PHP_EOL.PHP_EOL.PHP_EOL."ERROR: Stopping execution because an error has occured!".PHP_EOL;
        echo "\tDetail:".$e->getMessage().$e->getTraceAsString().PHP_EOL.PHP_EOL;
        exit(1);
    }

} catch (Exception $e) {
    echo "\nERROR: {$e->getMessage()}\n\n";
    exit(1);
}
