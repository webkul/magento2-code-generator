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

/**
 * Class UnitTestCase
 */
class UnitTestCase implements GenerateInterface
{

    protected $helper;

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
        
        CodeHelper::createDirectory(
            $unitTestRootDir = $path.DIRECTORY_SEPARATOR.'Test'
        );
       
        //$this->generateUnitTest($unitTestRootDir, $data);
       
        return ['status' => 'success', 'message' => "currently under development"];
    }

    /**
     * generate unit test cases
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
            if ( in_array(strtolower($dirName), ['controller', 'helper', 'block', 'model']) )
            {
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
       // print_r($methods); echo "\n";
        
        // print_r($files);
    }

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
