<?php

$root = __DIR__ . '/../';

return array(
    'autoloaders' => array(),
    'namespaceloader' => array(),
    'templates' => array(
        'class' => $root . 'tmpl/class.tmpl',
        'method' => array(
            'getset' => $root . 'tmpl/method.getset.tmpl',
            'getset.nullable' => $root . 'tmpl/method.getset.nullable.tmpl',
            'getset.boolean' => $root . 'tmpl/method.getset.boolean.tmpl',
            'addremove' => $root . 'tmpl/method.addremove.tmpl',
        )
    ),
    'override' => false, // override existing test files?
    'overrideAction' => false, // false | 'dump' | 'force' > force will ignore the override=false setting
    'exceptionOnExist' => true,
    'baseClasses' => array(
        '' => '\PHPUnit_Framework_TestCase',
    ),
    'writeDir' => false,
);