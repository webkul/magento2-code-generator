<?php
/**
 * Webkul Software.
 *
 * @package   Webkul_CodeGenerator
 * @author    Sanjay Chouhan
 */

namespace Webkul\CodeGenerator\Model\Generate;

use Webkul\CodeGenerator\Model\Helper;
use Webkul\CodeGenerator\Api\GenerateInterface;
use Webkul\CodeGenerator\Model\XmlGeneratorFactory;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\Simplexml\Config;
use Magento\Framework\Simplexml\Element;

/**
 * Class Plugin
 */
class Plugin implements GenerateInterface
{
    protected $helper;
    
    protected $xmlGeneratorFactory;

    /**
     * Constructor
     *
     * @param XmlGeneratorFactory $xmlGeneratorFactory
     * @param Helper $helper
     */
    public function __construct(
        XmlGeneratorFactory $xmlGeneratorFactory,
        Helper $helper
    ) {
        $this->helper = $helper;
        $this->xmlGenerator = $xmlGeneratorFactory->create();
    }

    /**
     * @inheritDoc
     */
    public function execute($data)
    {
        $moduleName = $data['module'];
        $path = $data['path'];
        $data['plugin-name'] = strtolower($moduleName.'-'.$data['name'].'-'.'plugin');
        $data['plugin-class'] = str_replace('_', '\\', $moduleName).'\\'.'Plugin'.'\\'.$data['name'];
        
        Helper::createDirectory(
            $pluginDirPath = $path.DIRECTORY_SEPARATOR.'Plugin'
        );
        
        Helper::createDirectory(
            $etcDirPath = $path.DIRECTORY_SEPARATOR.'etc'
        );
        
        if ($data['area']!==null) {
            Helper::createDirectory(
                $etcDirPath = $path.DIRECTORY_SEPARATOR.'etc'.DIRECTORY_SEPARATOR.$data['area']
            );
        }

        $this->createPlugin($pluginDirPath, $data);
        $this->addDiXmlData($etcDirPath, $data);
       
        return ['status' => 'success', 'message' => "Plugin Class Generated Successfully"];
    }

    /**
     * create Plugin class
     *
     * @param string $dir
     * @param array $data
     * @return void
     */
    public function createPlugin($dir, $data)
    {
        $fileName = ucfirst($data['name']);
        $nameSpace = $data['module'];
        $nameArray = explode("_", $nameSpace);
        $pluginFile = $this->helper->getTemplatesFiles('templates/plugin/plugin.php.dist');
        $pluginFile = str_replace('%module_name%', $data['module'], $pluginFile);
        $pluginFile = str_replace('%plugin_name%', $fileName, $pluginFile);
        $pluginFile = str_replace('%namespace%', $nameArray[0].'\\'.$nameArray[1], $pluginFile);
        
        $this->helper->saveFile(
            $dir.DIRECTORY_SEPARATOR.$fileName.'.php',
            $pluginFile
        );
    }

    /**
     * add di xml data
     *
     * @param string $etcDirPath
     * @param array $data
     * @return void
     */
    public function addDiXmlData($etcDirPath, $data)
    {
        $pluginType = $data['plugin-type'];
        $pluginName = $data['plugin-name'];
        $pluginClass = $data['plugin-class'];
        $diXmlFile = $this->helper->getDiXmlFile($etcDirPath);
        $xmlObj = new Config($diXmlFile);
        $diXml = $xmlObj->getNode();
        $typeNode = $this->xmlGenerator->addXmlNode($diXml, 'type', '', ['name'=>$pluginType]);
        $this->xmlGenerator->addXmlNode($typeNode, 'plugin', '', ['name'=>$pluginName, 'type'=>$pluginClass, 'sortOrder'=>1]);
        $xmlData = $this->xmlGenerator->formatXml($diXml->asXml());
        $this->helper->saveFile($diXmlFile, $xmlData);
    }
}
