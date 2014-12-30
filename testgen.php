<?php

/** @var Composer\Autoload\ClassLoader $autoloader */
$autoloader = require_once "vendor/autoload.php";

$defaultConfig = include_once "config/config.defaults.php";
$userConfig = include_once "config/config.local.php";
$config = array_merge($defaultConfig, $userConfig);

foreach ($config['autoload'] as $namespace => $path) $autoloader->addPsr4($namespace, $path);

$argument = isset($argv[1]) ? $argv[1]: '';
$flag = isset($argv[2]) ? $argv[2]: 0;

if ($flag == '--dump') {
    $config['overrideAction'] = 'dump';
}
if ($flag == '--force') {
    $config['overrideAction'] = 'force';
}

$processor = new \Testgen\Processor\Processor($config);

if (is_file($argument)) {
    $processor->processClass($argument);
    return 0;
}
if (is_dir($argument)) {
    $processor->processDirectory($argument);
    return 0;
}
echo 'Nothing to process' . PHP_EOL;
