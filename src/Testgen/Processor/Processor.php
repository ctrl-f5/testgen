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
            if (substr($node, strlen($node) - 1) == '~') {
                echo 'skipping tmp file: ' . $node . PHP_EOL;
                continue;
            }
            $path = $directory . DIRECTORY_SEPARATOR . $node;
            echo 'Processing: ' . $node . PHP_EOL;
            if (is_file($path)) $this->processClass($path);
        }
    }
} 