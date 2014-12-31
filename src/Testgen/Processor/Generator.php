<?php

namespace Testgen\Processor;

use Testgen\ClassManager\ClassInfo;

class Generator
{
    protected $config;

    public function __construct($config)
    {
        $this->config = $config;
    }

    public function generateTestClass(ClassInfo $info)
    {
        $vars = array(
            'baseClass' => $this->getBaseClass($info->getFQClassName()),
            'testClassNamespace' => $info->getTestNamespace(),
            'testClassName' => $info->getTestClassName(),
            'classNamespace' => $info->getNamespace(),
            'className' => $info->getClassName(),
            'FQCN' => $info->getFQClassName(),
            'classVarName' => $info->getClassVarName(),
        );

        $methods = array();
        $relations = array();
        foreach ($info->getProperties() as $propName => $prop) {
            if (isset($prop['type'])) {
                $methods[] = $this->generateTestMethod($propName, $prop, $vars);
                if ($prop['type'] == 'otm') {
                    if (strpos($prop['targetEntity'], '\\')) $relations[] = $prop['targetEntity'];
                    else $relations[] = $info->getNamespace() . '\\' . $prop['targetEntity'];
                }
            }
        }

        $uses = array();
        $relations = array_unique($relations);
        foreach ($relations as $r) $uses[] = 'use ' . $r . ';';

        $vars['useStatements'] = implode(PHP_EOL, $uses);
        $vars['testMethods'] = implode(PHP_EOL.PHP_EOL, $methods);

        $class = $this->processTemplate(
            $this->config['templates']['class'],
            $vars
        );

        $this->writeFile($info->getTestClassFile($this->config['writeDir']), $class);
    }

    public function generateTestMethod($prop, $propConfig, $vars)
    {
        $tmpl = $propConfig['nullable'] ?
            $this->config['templates']['method']['getset.nullable']:
            $this->config['templates']['method']['getset'];
        $methodName     = 'CanGetSet';
        $values         = array();

        if ($propConfig['type'] == 'otm') {
            $tmpl = $this->config['templates']['method']['addremove'];
            $methodName = 'CanAddRemove';

            $vars = array_merge($vars, array(
                'getter' => 'get' . ucfirst($prop),
                'adder' => 'add' . ucfirst($propConfig['targetEntity']),
                'remover' => 'remove' . ucfirst($propConfig['targetEntity']),
                'targetEntity' => $propConfig['targetEntity'],
            ));
        } else {
            switch ($propConfig['type']) {
                case 'integer':
                case 'int':
                    $values[] = 1;
                    break;
                case 'string':
                case 'str':
                case 'text':
                    $values[] = '"my string"';
                    break;
                case 'decimal':
                case 'float':
                    $values[] = '0.5';
                    break;
                case 'bool':
                case 'boolean':
                    $tmpl = $this->config['templates']['method']['getset.boolean'];
                    break;
                case 'datetime':
                case 'time':
                    $values[] = 'new \\DateTime()';
                    break;
                case 'array':
                    $values[] = 'array("test")';
                    break;
                case 'mto':
                    $values[] = 'new ' . $propConfig['targetEntity'] . '()';
                    break;
                default:
                    throw new \Exception('unknown property type: ' . $propConfig['type']);
            }

            // add method variables
            $vars = array_merge($vars, array(
                'setter' => 'set' . ucfirst($prop),
                'getter' => 'get' . ucfirst($prop),
            ));
        }

        // set values as template vars
        foreach ($values as $k => $v) $vars['val_'.$k] = $v;

        $vars['methodName'] = 'test' . $methodName . ucfirst($prop);

        return $this->processTemplate($tmpl, $vars);
    }

    public function processTemplate($template, $vars)
    {
        $template = file_get_contents($template);

        foreach ($vars as $var => $val) {
            $template = str_replace('{{' . $var . '}}', $val, $template);
        }

        return $template;
    }

    public function getBaseClass($fqcn)
    {
        foreach ($this->config['baseClasses'] as $ns => $class) {
            if (strpos($fqcn, $ns) === 0) return $class;
        }

        return $this->config['baseClasses']['\\'];
    }

    protected function writeFile($path, $content)
    {
        if (file_exists($path) && (!$this->config['override'] || !$this->config['overrideAction'] == 'force')) {
            if ($this->config['exceptionOnExist']) {
                echo PHP_EOL . $content . PHP_EOL . PHP_EOL;
                throw new \RuntimeException('File already exists: ' . $path . PHP_EOL . ' dumped generated content');
            }
            echo 'File exists, skipping: ' . $path . PHP_EOL;

            if ($this->config['overrideAction'] == 'dump') {
                echo 'Dumping content: ' . PHP_EOL . $content . PHP_EOL;
            }

            return;
        }

        echo 'writing testfile: (' . strlen($content) . 'bytes) ' . $path . PHP_EOL;

        $dir = dirname($path);
        if (!file_exists($dir)) {
            mkdir($dir, 0777, true);
        }
        file_put_contents($path, $content);
    }
}