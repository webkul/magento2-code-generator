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
 * Class controller
 */
class Controller implements GenerateInterface
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
        $modelName = $data['name'];
        $path = $data['path'];
        $area = $data['area'];
        
        CodeHelper::createDirectory(
            $controllerPath = $path
        );

       if ($area == 'frontend') {
            $this->createFrontController($controllerPath, $data);
       } else {
        $this->createAdminController($controllerPath, $data);
       }
       
        return ['status' => 'success', 'message' => "Controller Class generated successfully"];
    }

    /**
     * create front controller
     *
     * @param string $dir
     * @param array $data
     * @return void
     */
    public function createFrontController($dir, $data)
    {
        $fileName = ucfirst($data['name']);
        $nameSpace = $data['module'];
        $pathParts = explode("Controller/", $data['path']);

        $nameArray = explode("_", $nameSpace);
        $nameSpace = implode("\\", $nameArray);
        $actionPath = explode("/", $pathParts[1]);
        
        $nameSpace = $nameSpace."\\Controller\\".implode("\\", $actionPath);
       
        $controllerFile = $this->helper->getTemplatesFiles('templates/controller/controller_front.php.dist');
        $controllerFile = str_replace('%module_name%', $data['module'], $controllerFile);
        $controllerFile = str_replace('%class_name%', $fileName, $controllerFile);
        $controllerFile = str_replace('%namespace%', $nameSpace, $controllerFile);
        $this->helper->saveFile(
            $dir.DIRECTORY_SEPARATOR.$fileName.'.php',
            $controllerFile
        );
    }

    /**
     * create admin controller
     *
     * @param string $dir
     * @param array $data
     * @return void
     */
     public function createAdminController($dir, $data)
     {
        $fileName = ucfirst($data['name']);
        $nameSpace = $data['module'];
        $pathParts = explode("Controller/", $data['path']);

        $nameArray = explode("_", $nameSpace);
        $nameSpace = implode("\\", $nameArray);
        $actionPath = explode("/", $pathParts[1]);
        
        $nameSpace = $nameSpace."\\Controller\\Adminhtml\\".implode("\\", $actionPath);
       
        $controllerFile = $this->helper->getTemplatesFiles('templates/controller/controller_admin.php.dist');
        $controllerFile = str_replace('%module_name%', $data['module'], $controllerFile);
        $controllerFile = str_replace('%class_name%', $fileName, $controllerFile);
        $controllerFile = str_replace('%namespace%', $nameSpace, $controllerFile);
        $this->helper->saveFile(
            $dir.DIRECTORY_SEPARATOR.$fileName.'.php',
            $controllerFile
        );
     }
}
