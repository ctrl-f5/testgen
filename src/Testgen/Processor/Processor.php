<?php

namespace Testgen\Processor;

use Testgen\ClassManager\AnnotationReader;
use Testgen\ClassManager\ClassInfo;
use Testgen\ClassManager\FileHandler;

class Processor
{
    protected $config;

    public function __construct($config)
    {
        $this->config = $config;
    }

    public function processClass($classFile)
    {
        $info = ClassInfo::createFromFile($classFile);

        $generator = new Generator($this->config);
        $generator->generateTestClass($info);
    }

    public function processDirectory($directory)
    {
        $dirs = scandir($directory);

        foreach ($dirs as $node) {
            if ($node == '.' || $node == '..') continue;
            if (substr($node, strlen($node) - 1) == '~' || $node[0] == '.') {
                echo 'skipping tmp or hidden file: ' . $node . PHP_EOL;
                continue;
            }
            $path = realpath($directory . DIRECTORY_SEPARATOR . $node);
            echo 'Processing: ' . $path . PHP_EOL;
            if (is_file($path)) $this->processClass($path);
        }
    }
} 