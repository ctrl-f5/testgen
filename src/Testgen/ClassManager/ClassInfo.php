<?php

namespace Testgen\ClassManager;

class ClassInfo
{
    /**
     * @var string
     */
    protected $fqClassName;

    /**
     * @var \ReflectionClass
     */
    protected $reflectionClass;

    public function __construct($class)
    {
        $this->fqClassName = $class;
        $this->reflectionClass = new \ReflectionClass($class);
    }

    public static function createFromFile($classFile)
    {
        if (!file_exists($classFile)) throw new \RuntimeException('File does not exist: ' . $classFile);

        $content = file_get_contents($classFile);

        $namespace = '';
        $className = '';
        foreach (explode(PHP_EOL, $content) as $line) {
            $line = trim($line);
            if (strpos($line, 'namespace ') === 0) {
                $namespace = str_replace(array('namespace ', ';'), array('', ''), $line);
                continue;
            }
            if (strpos($line, 'class ') === 0 || strpos($line, 'abstract class ') === 0) {
                $trim = strpos($line, 'class ') === 0 ? 6: 15;
                $line = trim($line, '{');
                $line = substr($line, $trim);
                $line = trim($line, ' ');
                $pos2 = strpos($line, ' ');
                if ($pos2 === false) {
                    $className = $line;
                } else {
                    $className = substr($line, 0, $pos2);
                }
                break;
            }
        }

        $fqcn = $namespace . '\\' . $className;

        if (!class_exists($fqcn)) throw new \RuntimeException("class not found: " . $fqcn);

        return new ClassInfo($fqcn);
    }

    public function getProperties()
    {
        $props = array();
        foreach ($this->reflectionClass->getProperties() as $prop) {
            $doc = $prop->getDocComment();
            $configLine = false;
            $relation = false;
            foreach (explode(PHP_EOL, $doc) as $line) {
                $line = trim($line);
                if (strpos($line, '\Column(') !== false) {
                    $configLine = $line;
                    $relation = false;
                    break;
                }
                if (strpos($line, '\ManyToOne(') !== false) {
                    $configLine = $line;
                    $relation = 'mto';
                    break;
                }
                if (strpos($line, '\OneToMany(') !== false) {
                    $configLine = $line;
                    $relation = 'otm';
                    break;
                }
            }
            if ($configLine) {
                $colConf = substr(
                    $configLine,
                    strpos($configLine, '(') + 1,
                    strlen($configLine) - (strpos($configLine, '(') + 1) - 1
                );

                // clean up the config
                $colConf = str_replace(' ', '', $colConf);
                $colConf = str_replace(
                    array(
                        ',cascade={"persist","remove"}',
                        ',cascade={"remove","persist"}',
                        ',cascade={"persist"}',
                        ',cascade={"remove"}',
                        'options={"default"="unknown"}',
                    ),
                    '',
                    $colConf
                );

                // parse ini
                $ini = str_replace(',', PHP_EOL, $colConf);
                $config = @parse_ini_string($ini);

                if ($config) {

                    if ($relation) $config['type'] = $relation;
                    if (!isset($config['nullable'])) $config['nullable'] = false;

                    $props[$prop->getName()] = $config;

                } else {
                    echo sprintf('unknown property config for %s: %s%s', $prop->getName(), $ini, PHP_EOL);
                }
            }
        }

        return $props;
    }

    public function getNamespace()
    {
        return $this->reflectionClass->getNamespaceName();
    }

    public function getClassName()
    {
        return $this->reflectionClass->getShortName();
    }

    public function getFQClassName()
    {
        return $this->reflectionClass->getName();
    }

    public function getTestNamespace()
    {
        return $this->convertToTestNamespace(
            $this->getNamespace()
        );
    }

    public function getTestClassName()
    {
        return $this->getClassName() . 'Test';
    }

    public function getClassVarName()
    {
        return lcfirst($this->getClassName());
    }

    public function isAbstract()
    {
        return $this->reflectionClass->isAbstract();
    }

    public function getTestClassFile($baseDir = false)
    {
        if ($baseDir) {
            $subPath = str_replace('\\', DIRECTORY_SEPARATOR, $this->getNamespace());
            $filePath = $baseDir . $subPath . DIRECTORY_SEPARATOR . $this->getClassName() . '.php';
        } else {
            $filePath = $this->reflectionClass->getFileName();
        }
        return str_replace(
            array('\\Entity\\',         '/Entity/',         $this->getClassName()),
            array('\\Tests\\Entity\\',  '/Tests/Entity/',   $this->getTestClassName()),
            $filePath
        );
    }

    protected function convertToTestNamespace($ns)
    {
        if (strpos($ns, '\\Entity') === false && strpos($ns, '\\Model') === false) {
            throw new \Exception('We can only handle Entity or Model classes');
        }

        return str_replace(
            array('\Entity', '\Model'),
            array('\Tests\Entity', '\Tests\Model'),
            $ns
        );
    }
}