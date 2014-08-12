<?php

/** @var Composer\Autoload\ClassLoader $autoloader */
$autoloader = require_once "vendor/autoload.php";

$defaultConfig = include_once "config/config.defaults.php";
$userConfig = include_once "config/config.php";
$config = array_merge($defaultConfig, $userConfig);

foreach ($config['autoload'] as $namespace => $path) $autoloader->addPsr4($namespace, $path);

$className = $argv[1];

$processor = new \Testgen\Processor\Processor($config);
$processor->processClass($className);