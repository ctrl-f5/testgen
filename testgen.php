<?php

/** @var Composer\Autoload\ClassLoader $autoloader */
$autoloader = require_once "vendor/autoload.php";

$defaultConfig = include_once "config/config.defaults.php";
$userConfig = include_once "config/config.local.php";
$config = array_merge($defaultConfig, $userConfig);

foreach ($config['autoload'] as $namespace => $path) $autoloader->addPsr4($namespace, $path);

$argument = $argv[1];

$processor = new \Testgen\Processor\Processor($config);
if (is_file($argument)) {
    return $processor->processClass($argument);
}
if (is_dir($argument)) {
    return $processor->processDirectory($argument);
}
echo 'Nothing to process' . PHP_EOL;
