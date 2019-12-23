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
 * Class Command
 */
class Command implements GenerateInterface
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
        
        Helper::createDirectory(
            $commandDirPath = $path.DIRECTORY_SEPARATOR.'Console'.DIRECTORY_SEPARATOR.'Command'
        );
        
        Helper::createDirectory(
            $etcDirPath = $path.DIRECTORY_SEPARATOR.'etc'
        );

        $this->createCommand($commandDirPath, $data);
        $data['command-class'] = str_replace('_', '\\', $moduleName).'\\Console\\Command\\'.ucfirst($data['name']);
        $this->addDiXmlData($etcDirPath, $data);
       
        return ['status' => 'success', 'message' => "Command Generated Successfully"];
    }

    /**
     * create Command class
     *
     * @param string $dir
     * @param array $data
     * @return void
     */
    public function createCommand($dir, $data)
    {
        $fileName = ucfirst($data['name']);
        $nameSpace = $data['module'];
        $nameArray = explode("_", $nameSpace);
        $commandFile = $this->helper->getTemplatesFiles('templates/command/command.php.dist');
        $commandFile = str_replace('%module_name%', $data['module'], $commandFile);
        $commandFile = str_replace('%name%', $fileName, $commandFile);
        $commandFile = str_replace('%command%', $data['command'], $commandFile);
        $commandFile = str_replace('%namespace%', $nameArray[0].'\\'.$nameArray[1], $commandFile);
        
        $this->helper->saveFile(
            $dir.DIRECTORY_SEPARATOR.$fileName.'.php',
            $commandFile
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
        $commandName = str_replace(':', '', $data['command']);
        $diXmlFile = $this->helper->getDiXmlFile($etcDirPath);
        $xmlObj = new Config($diXmlFile);
        $diXml = $xmlObj->getNode();
        $typeNode = $this->xmlGenerator->addXmlNode(
                                $diXml, 
                                'type', 
                                '', 
                                ['name'=>'Magento\Framework\Console\CommandList']
                            );
        $argsNode = $this->xmlGenerator->addXmlNode($typeNode, 'arguments');
        $argNode = $this->xmlGenerator->addXmlNode(
                                $argsNode, 
                                'argument', 
                                '', 
                                ['name'=>'commands', 'xsi:type'=>'array']
                            );
        $this->xmlGenerator->addXmlNode(
                                $argNode, 
                                'item', 
                                $data['command-class'], 
                                ['name'=>$commandName, 'xsi:type'=>'object']
                            );
        $xmlData = $this->xmlGenerator->formatXml($diXml->asXml());
        $this->helper->saveFile($diXmlFile, $xmlData);
    }
}
