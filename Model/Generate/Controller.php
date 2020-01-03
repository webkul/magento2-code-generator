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
use Magento\Framework\Simplexml\Config;
use Magento\Framework\Simplexml\Element;
use Webkul\CodeGenerator\Model\XmlGeneratorFactory;

/**
 * Class controller
 */
class Controller implements GenerateInterface
{
    protected $fileDriver;

    protected $helper;

    public function __construct(
        CodeHelper $helper,
        XmlGeneratorFactory $xmlGeneratorFactory,
        \Magento\Framework\Filesystem\Driver\File $fileDriver
    ) {
        $this->helper = $helper;
        $this->fileDriver = $fileDriver;
        $this->xmlGenerator = $xmlGeneratorFactory->create();
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
        CodeHelper::createDirectory(
            $etcDirPath = $data['module_path'].DIRECTORY_SEPARATOR.'etc'.DIRECTORY_SEPARATOR.$area
        );
        $this->createRoutesXmlFile($etcDirPath, $data);

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
        $fileName = $this->helper->getClassName($data['name']);
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
        $resource = $data['resource'];
        $pathParts = explode("Controller/", $data['path']);

        $nameArray = explode("_", $nameSpace);
        $nameSpace = implode("\\", $nameArray);
        $actionPath = explode("/", $pathParts[1]);
        
        $nameSpace = $nameSpace."\\Controller\\Adminhtml\\".implode("\\", $actionPath);
        
        $controllerFile = $this->helper->getTemplatesFiles('templates/controller/controller_admin.php.dist');
        $controllerFile = str_replace('%module_name%', $data['module'], $controllerFile);
        $controllerFile = str_replace('%class_name%', $fileName, $controllerFile);
        $controllerFile = str_replace('%namespace%', $nameSpace, $controllerFile);
        $controllerFile = str_replace('%resource_name%', $resource, $controllerFile);
        $this->helper->saveFile(
            $dir.DIRECTORY_SEPARATOR.$fileName.'.php',
            $controllerFile
        );
    }

    /**
     * Craete routes.xml
     *
     * @param string $etcDirPath
     * @param string $data
     * @return bool
     */
    private function createRoutesXmlFile($etcDirPath, $data)
    {
        $controllerName = $data['name'];
        $module = $data['module'];
        $area = $data['area'];
        
        $xmlFilePath = $this->getRoutesXmlFilePath($etcDirPath);
        if ($this->fileDriver->isExists($xmlFilePath)) {
            return true;
        }
        
        if (!isset($data['router']) || !$data['router']) {
            throw new \RuntimeException(
                __('Please provide router name')
            );
        }
        $routeName = $data['router'];
        
        $xmlData = $this->helper->getTemplatesFiles('templates/routes.xml.dist');
        $this->helper->saveFile($xmlFilePath, $xmlData);

        $xmlObj = new Config($xmlFilePath);
        $routesXml = $xmlObj->getNode();
        if (!$routesXml instanceof Element) {
            throw new \RuntimeException(
                __('Incorrect routes.xml schema found')
            );
        }
        $routesId = $area == 'adminhtml' ? 'admin' : 'standard';
        // $routeName = strtolower($this->helper->getClassName($controllerName));
        $routerNode = $this->xmlGenerator->addXmlNode(
            $routesXml,
            'router',
            '',
            ['id' => $routesId],
            'id'
        );
        $routeNode = $this->xmlGenerator->addXmlNode(
            $routerNode,
            'route',
            '',
            ['id' => $routeName, 'frontName' =>  $routeName],
            'id'
        );
        $this->xmlGenerator->addXmlNode(
            $routeNode,
            'module',
            '',
            ['name' =>  $module],
            'name'
        );
        $xmlData = $this->xmlGenerator->formatXml($routesXml->asXml());
        $this->helper->saveFile($xmlFilePath, $xmlData);

        return true;
    }

    /**
     * Get routes.xml file path
     *
     * @param string $etcDirPath
     * @return string
     */
    private function getRoutesXmlFilePath($etcDirPath)
    {
        return $etcDirPath.DIRECTORY_SEPARATOR.'routes.xml';
    }
}
