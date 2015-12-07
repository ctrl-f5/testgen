#! /usr/bin/env php
<?php

$srcDir = __DIR__ . DIRECTORY_SEPARATOR;

/** @var Composer\Autoload\ClassLoader $autoloader */
$autoloader = require_once $srcDir . "vendor/autoload.php";

$cwd = getcwd();
$localConfig = array();
foreach (scandir($cwd) as $node) {
    if ($node == 'testgen.conf.php') {
        $localConfig = realpath($cwd . DIRECTORY_SEPARATOR . $node);
        $localConfig = include_once $localConfig;
        break;
    }
}

$defaultConfig = include_once $srcDir . "config/config.defaults.php";
$userConfig = include_once $srcDir . "config/config.local.php";
$config = array_merge($defaultConfig, $userConfig, $localConfig);

foreach ($config['autoloaders'] as $loader) require_once $loader;
foreach ($config['namespaceloader'] as $namespace => $path) $autoloader->addPsr4($namespace, $path);

$argument = isset($argv[1]) ? $argv[1]: '.';
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
