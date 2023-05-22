<?php
/**
 * Webkul Software.
 *
 * @package   Webkul_CodeGenerator
 * @author    Ashutosh Srivastava
 */

namespace Webkul\CodeGenerator\Model\Generate;

use Webkul\CodeGenerator\Model\Helper as CodeHelper;
use Webkul\CodeGenerator\Api\GenerateInterface;
use Laminas\Code\Generator\ClassGenerator;
use Laminas\Code\Generator\DocBlockGenerator;
use Laminas\Code\Generator\DocBlock\Tag;
use Laminas\Code\Generator\MethodGenerator;
use Laminas\Code\Generator\PropertyGenerator;
use Laminas\Code\Generator\ParameterGenerator;

/**
 * Generate UnitTestCase
 */
class UnitTestCase implements GenerateInterface
{
    /**
     * @var CodeHelper
     */
    protected $helper;

    /**
     * @var object
     */
    protected $docblock;

    /**
     * __construct function
     *
     * @param CodeHelper $helper
     */
    public function __construct(
        CodeHelper $helper
    ) {
        $this->helper = $helper;
    }

    /**
     * @inheritDoc
     */
    public function execute($data)
    {
        $path = $data['path'];
        $this->docblock = $this->helper->getHeadDocBlock($data['module']);
        $this->helper->createDirectory(
            $unitTestRootDir = $path.DIRECTORY_SEPARATOR.'Test'.DIRECTORY_SEPARATOR.'Unit'
        );
       
        $this->generateUnitTest($unitTestRootDir, $data);
       
        return ['status' => 'success', 'message' => "unit test cases successfully generated"];
    }

    /**
     * Generate unit test cases
     *
     * @param string $dir
     * @param array $data
     * @return void
     */
    public function generateUnitTest($dir, $data)
    {
        $modulePath = $data['path'];
        $dirs = array_filter(
            glob($modulePath."/*"),
            'is_dir'
        );

        $files = [];
        foreach ($dirs as $dir) {
            $dirName = basename($dir);
            if (in_array(strtolower($dirName), ['controller', 'helper', 'block', 'model'])) {
                $this->getDirContents($dir, $files);
            }
        }
        $classes = [];
        $methods = [];
        foreach ($files as $file) {
            $pathParts = explode("code/", $file);
            $filePath = rtrim($pathParts[1], ".php");
            $fullClassName = implode("\\", explode("/", $filePath));
            $c = new \ReflectionClass($fullClassName);
            if (!$c->isAbstract()) {
                foreach ($c->getMethods(\ReflectionMethod::IS_PUBLIC) as $method) {
                    if ($method->class == $fullClassName) {
                        $methods[$fullClassName][] = $method->name;
                    }
                }
            }
        }

        $this->generateTestCases($methods, $modulePath);
    }

    /**
     * Generate unit test cases
     *
     * @param array $methods
     * @param string $path
     * @return void
     */
    public function generateTestCases($methods, $path)
    {
        foreach ($methods as $c => $method) {
            $pathParts = explode("\\", $c);
            $vendor = $pathParts[0];
            $module = $pathParts[1];
            unset($pathParts[0]);
            unset($pathParts[1]);

            $fileName = end($pathParts).'Test';
            unset($pathParts[count($pathParts)+1]);
           
            $unitTestPath = implode(DIRECTORY_SEPARATOR, $pathParts);
            $this->helper->createDirectory(
                $path.DIRECTORY_SEPARATOR.'Test'.DIRECTORY_SEPARATOR.'Unit'.DIRECTORY_SEPARATOR.$unitTestPath
            );
            $nameSpace = $vendor."\\".$module."\\"."Test\\Unit\\".implode("\\", $pathParts);
            $classCode = $this->generateTestClass($nameSpace, $fileName);
            $this->helper->saveFile(
                $path.DIRECTORY_SEPARATOR.'Test'.DIRECTORY_SEPARATOR.'Unit'.DIRECTORY_SEPARATOR.
                    $unitTestPath.DIRECTORY_SEPARATOR.$fileName.'.php',
                $classCode->generate()
            );
        }
    }

    /**
     * Genearte class
     *
     * @param string $nameSpace
     * @param string $className
     * @return void
     */
    public function generateTestClass($nameSpace, $className)
    {
        $unitTestClass      = new ClassGenerator();
        $generatorsMethods = [
            MethodGenerator::fromArray([
                'name'       => 'setUp',
                'parameters' => [],
                'visibility' => PropertyGenerator::FLAG_PROTECTED,
                'body'       => '$this->objectManager = new \\Magento\\Framework\\TestFramework\\Unit\\Helper\\ObjectManager($this);',
                'docblock'   => DocBlockGenerator::fromArray([
                    'shortDescription' => 'setup mocks',
                    'longDescription'  => "",
                    'tags'             => [
                        new Tag\ReturnTag([
                            'datatype'  => 'void',
                        ]),
                    ],
                ]),
            ])
        ];

        $unitTestClass->setName($className)
        ->setNameSpaceName($nameSpace)
        ->setDocblock(DocBlockGenerator::fromArray([
            'shortDescription' => $className.' Class',
        ]))
        ->addProperties([
            ['objectManager', '', PropertyGenerator::FLAG_PROTECTED]
        ])
        ->setExtendedClass(\PHPUnit\Framework\TestCase::class)
        ->addMethods($generatorsMethods);
        $file = new \Laminas\Code\Generator\FileGenerator([
            'classes'  => [$unitTestClass],
            'docblock' => $this->docblock
        ]);
        return $file;
    }

    /**
     * Get Content
     *
     * @param string $dir
     * @param array $results
     * @return void
     */
    public function getDirContents($dir, &$results = [])
    {
        $files = scandir($dir);
    
        foreach ($files as $key => $value) {
            $path = realpath($dir.DIRECTORY_SEPARATOR.$value);
            if (!is_dir($path)) {
                $results[] = $path;
            } elseif ($value != "." && $value != "..") {
                $this->getDirContents($path, $results);
            }
        }
    }
}
