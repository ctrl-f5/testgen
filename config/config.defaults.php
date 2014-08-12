<?php

$root = __DIR__ . '/../';

return array(
    'autoload' => array(),
    'templates' => array(
        'class' => $root . 'tmpl/class.tmpl',
        'method' => array(
            'getset' => $root . 'tmpl/method.getset.tmpl',
            'getset.nullable' => $root . 'tmpl/method.getset.nullable.tmpl',
            'addremove' => $root . 'tmpl/method.addremove.tmpl',
        )
    ),
    'override' => false, // override existing test files?
    'exceptionOnExist' => true,
    'baseClasses' => array(
        '' => '\PHPUnit_Framework_TestCase',
    ),
);