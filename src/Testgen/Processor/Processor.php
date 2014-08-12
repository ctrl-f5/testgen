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

    public function processClass($class)
    {
        $info = new ClassInfo($class);

        $generator = new Generator($this->config);
        $generator->generateTestClass($info);
    }

    public function findTestClass($class)
    {

    }
} 